<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Services\StripeProductSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PricingController extends Controller
{
    public function __construct(
        protected StripeProductSyncService $stripeSync,
    ) {}

    // List all plans
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PricingPlan::query()
                ->withCount('featureGroups')
                ->orderBy('sort_order')
                ->orderBy('id');

            return DataTables::eloquent($query)
                ->editColumn('sort_order', fn(PricingPlan $plan) => (int) $plan->sort_order)
                ->editColumn('plan_name', fn(PricingPlan $plan) => '<strong>' . e($plan->plan_name) . '</strong>')
                ->addColumn('price_text', fn(PricingPlan $plan) => '$' . (string) $plan->price . (string) $plan->price_suffix)
                ->addColumn('badge_text_display', function (PricingPlan $plan): string {
                    if (! $plan->badge_text) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="badge bg-info">' . e($plan->badge_text) . '</span>';
                })
                ->addColumn('featured_display', function (PricingPlan $plan): string {
                    return $plan->is_featured
                        ? '<span class="badge bg-primary">Yes</span>'
                        : '<span class="text-muted">No</span>';
                })
                ->addColumn('groups_count_display', fn(PricingPlan $plan) => (int) $plan->feature_groups_count . ' groups')
                ->addColumn('visible_display', function (PricingPlan $plan): string {
                    $btnClass = $plan->is_visible ? 'btn-success' : 'btn-secondary';
                    $btnLabel = $plan->is_visible ? 'Visible' : 'Hidden';

                    return '<button type="button" class="btn btn-sm ' . $btnClass . ' js-toggle-visibility" data-plan-id="' . $plan->id . '">' . $btnLabel . '</button>';
                })
                ->addColumn('actions', function (PricingPlan $plan): string {
                    $editUrl = route('admin.pricing.edit', $plan);

                    return '<a href="' . e($editUrl) . '" class="btn btn-sm btn-warning">Edit</a> '
                        . '<button type="button" class="btn btn-sm btn-danger js-delete-plan" data-plan-id="' . $plan->id . '">Delete</button>';
                })
                ->rawColumns(['plan_name', 'badge_text_display', 'featured_display', 'visible_display', 'actions'])
                ->toJson();
        }

        return view('web.admin.price_plan.index');
    }

    // Show create form
    public function create() {
        $plan = new PricingPlan();
        return view('web.admin.price_plan.form', compact('plan'));
    }

    // Save new plan with groups + items
    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);

        $plan = DB::transaction(function () use ($data, $request): PricingPlan {
            $plan = PricingPlan::create($data);
            $this->syncFeatureGroups($plan, (array) $request->input('feature_groups', []));
            return $plan;
        });

        // Auto-create Stripe Product + Price if user opted in
        $skipStripe = $request->boolean('skip_stripe_sync', false);
        if (!$skipStripe) {
            try {
                $this->stripeSync->sync($plan);
            } catch (\Exception $e) {
                report($e);
                // Plan was created but Stripe sync failed — admin can retry later
            }
        }

        $this->bustPricingCache();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Plan created successfully.' . (!$skipStripe && $plan->stripe_price_id ? ' Stripe product & price created.' : ''),
                'data' => [
                    'plan' => $plan,
                    'redirect' => route('admin.pricing.index'),
                ],
            ]);
        }

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Plan created!');
    }

    // Show edit form
    public function edit(PricingPlan $plan) {
        $plan->load('featureGroups.items');
        return view('web.admin.price_plan.form', compact('plan'));
    }

    // Update existing plan
    public function update(Request $request, PricingPlan $plan)
    {
        $data = $this->validatedPayload($request);

        DB::transaction(function () use ($request, $plan, $data): void {
            $plan->update($data);

            // Replace current feature groups/items with submitted payload.
            $plan->featureGroups()->each(fn($group) => $group->items()->delete());
            $plan->featureGroups()->delete();
            $this->syncFeatureGroups($plan, (array) $request->input('feature_groups', []));
        });

        // Sync Stripe product/price on update if user opted in
        $skipStripe = $request->boolean('skip_stripe_sync', false);
        if (!$skipStripe) {
            try {
                $this->stripeSync->sync($plan);
            } catch (\Exception $e) {
                report($e);
            }
        }

        $this->bustPricingCache();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully.' . (!$skipStripe && $plan->fresh()->stripe_price_id ? ' Stripe product & price synced.' : ''),
                'data' => [
                    'plan' => $plan->fresh('featureGroups.items'),
                    'redirect' => route('admin.pricing.index'),
                ],
            ]);
        }

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Plan updated!');
    }

    // Reusable group + item sync
    private function syncFeatureGroups(PricingPlan $plan, array $groups)
    {
        foreach ($groups as $gIndex => $group) {
            $title = trim((string) ($group['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $newGroup = $plan->featureGroups()->create([
                'title' => $title,
                'sort_order' => $gIndex,
            ]);

            foreach (($group['items'] ?? []) as $iIndex => $item) {
                $featureText = trim((string) ($item['text'] ?? ''));

                if ($featureText === '') {
                    continue;
                }

                $newGroup->items()->create([
                    'feature_text' => $featureText,
                    'sort_order' => $iIndex,
                ]);
            }
        }
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'plan_name' => ['required', 'string', 'max:150'],
            'badge_text' => ['nullable', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_suffix' => ['nullable', 'string', 'max:30'],
            'best_for' => ['nullable', 'string'],
            'outcome_text' => ['nullable', 'string'],
            'button_label' => ['nullable', 'string', 'max:150'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'stripe_product_id' => ['nullable', 'string', 'max:255'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'skip_stripe_sync' => ['nullable', 'boolean'],
            'feature_groups' => ['nullable', 'array'],
            'feature_groups.*.title' => ['nullable', 'string', 'max:255'],
            'feature_groups.*.items' => ['nullable', 'array'],
            'feature_groups.*.items.*.text' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_visible'] = $request->boolean('is_visible', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        return $validated;
    }

    // Toggle visibility
    public function toggle(Request $request, PricingPlan $plan)
    {
        $plan->update(['is_visible' => !$plan->is_visible]);

        $this->bustPricingCache();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Visibility updated successfully.',
                'data' => [
                    'is_visible' => (bool) $plan->is_visible,
                ],
            ]);
        }

        return back();
    }

    // Drag-drop reorder
    public function reorder(Request $request)
    {
        foreach ((array) $request->input('order', []) as $index => $id) {
            PricingPlan::where('id', $id)->update(['sort_order' => $index]);
        }

        $this->bustPricingCache();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, PricingPlan $plan)
    {
        $plan->featureGroups()->each(fn($g) => $g->items()->delete());
        $plan->featureGroups()->delete();
        $plan->delete();

        $this->bustPricingCache();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully.',
            ]);
        }

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Plan deleted!');
    }

    private function bustPricingCache(): void
    {
        Cache::forget('api:cms:pricing:index');
    }
}
