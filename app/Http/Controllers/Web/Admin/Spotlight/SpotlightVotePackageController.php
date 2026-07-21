<?php

namespace App\Http\Controllers\Web\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightVotePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class SpotlightVotePackageController extends Controller
{
    /**
     * Display a listing of vote packages.
     */
    public function index()
    {
        $packages = SpotlightVotePackage::ordered()
            ->withCount('purchases')
            ->paginate(20);

        $stats = [
            'total_active'   => SpotlightVotePackage::where('is_active', true)->count(),
            'total_inactive' => SpotlightVotePackage::where('is_active', false)->count(),
            'total_packages' => SpotlightVotePackage::count(),
        ];

        return view('web.admin.spotlight.vote-packages.index', compact('packages', 'stats'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        return view('web.admin.spotlight.vote-packages.create');
    }

    /**
     * Store a newly created vote package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'votes_count' => 'required|integer|min:1|max:1000',
            'price'       => 'required|numeric|min:0.01|max:99999.99',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0|max:999',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Check for duplicate slug
        $existing = SpotlightVotePackage::where('slug', $validated['slug'])->first();
        if ($existing) {
            $validated['slug'] = $validated['slug'] . '-' . time();
        }

        SpotlightVotePackage::create($validated);

        return redirect()->route('admin.spotlight.vote-packages.index')
            ->with('success', "Package '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(SpotlightVotePackage $package)
    {
        return view('web.admin.spotlight.vote-packages.edit', compact('package'));
    }

    /**
     * Update the specified vote package.
     */
    public function update(Request $request, SpotlightVotePackage $package)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'votes_count' => 'required|integer|min:1|max:1000',
            'price'       => 'required|numeric|min:0.01|max:99999.99',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0|max:999',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Update slug only if name changed and slug is unique
        if ($package->name !== $validated['name']) {
            $newSlug = Str::slug($validated['name']);
            $slugExists = SpotlightVotePackage::where('slug', $newSlug)
                ->where('id', '!=', $package->id)
                ->exists();
            $validated['slug'] = $slugExists ? $newSlug . '-' . time() : $newSlug;
        }

        $package->update($validated);

        return redirect()->route('admin.spotlight.vote-packages.index')
            ->with('success', "Package '{$validated['name']}' updated successfully.");
    }

    /**
     * Toggle the active status of a package.
     */
    public function toggleActive(SpotlightVotePackage $package)
    {
        $package->update(['is_active' => ! $package->is_active]);

        $status = $package->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.spotlight.vote-packages.index')
            ->with('success', "Package '{$package->name}' {$status} successfully.");
    }

    /**
     * Remove the specified package.
     */
    public function destroy(SpotlightVotePackage $package)
    {
        // Check if any purchases reference this package
        if ($package->purchases()->count() > 0) {
            return redirect()->route('admin.spotlight.vote-packages.index')
                ->with('error', "Cannot delete '{$package->name}' — it has existing purchases. Deactivate it instead.");
        }

        $name = $package->name;
        $package->delete();

        return redirect()->route('admin.spotlight.vote-packages.index')
            ->with('success', "Package '{$name}' deleted successfully.");
    }
}
