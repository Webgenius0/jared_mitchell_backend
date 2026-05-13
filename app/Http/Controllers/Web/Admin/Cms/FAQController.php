<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class FAQController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = FAQ::query();

            return DataTables::eloquent($query)
                ->editColumn('question', fn(FAQ $faq) => '<strong>' . e($faq->question) . '</strong>')
                ->editColumn('status', function (FAQ $faq) {
                    $class = $faq->status === 'active' ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' . $class . '">' . ucfirst($faq->status) . '</span>';
                })
                ->addColumn('actions', function (FAQ $faq) {
                    $editUrl = route('admin.cms.faq.edit', $faq);
                    return '<a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a> ' .
                           '<button type="button" class="btn btn-sm btn-danger js-delete-faq" data-faq-id="' . $faq->id . '">Delete</button>';
                })
                ->rawColumns(['question', 'status', 'actions'])
                ->toJson();
        }

        return view('web.admin.cms.faq.index');
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        $faq = new FAQ();
        return view('web.admin.cms.faq.form', compact('faq'));
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        FAQ::create($validated);

        $this->bustFAQCache();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'FAQ created successfully.',
                'redirect' => route('admin.cms.faq.index'),
            ]);
        }

        return redirect()->route('admin.cms.faq.index')->with('success', 'FAQ created successfully.');
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(FAQ $faq)
    {
        return view('web.admin.cms.faq.form', compact('faq'));
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, FAQ $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        $faq->update($validated);

        $this->bustFAQCache();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'FAQ updated successfully.',
                'redirect' => route('admin.cms.faq.index'),
            ]);
        }

        return redirect()->route('admin.cms.faq.index')->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(Request $request, FAQ $faq)
    {
        $faq->delete();

        $this->bustFAQCache();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'FAQ deleted successfully.',
            ]);
        }

        return redirect()->route('admin.cms.faq.index')->with('success', 'FAQ deleted successfully.');
    }

    /**
     * Bust the FAQ cache.
     */
    private function bustFAQCache(): void
    {
        Cache::forget('api:cms:faq:index');
    }
}
