<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Traits\AdminApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AdminBusinessCategoryController extends Controller
{
    use AdminApiResponse;

    /**
     * Display a listing of business categories.
     */
    public function index()
    {
        return view('web.admin.business-category.business_categories');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = BusinessCategory::query()->orderBy('sort_order')->orderBy('name');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('is_active', function ($row) {
                $status = $row->is_active ? 'Active' : 'Inactive';
                $class  = $row->is_active ? 'bg-success' : 'bg-danger';
                return '<span class="badge ' . $class . '">' . $status . '</span>';
            })
            ->addColumn('action', function ($row) {
                $editBtn   = '<button class="btn btn-sm btn-soft-info edit-btn" data-category=\'' . json_encode($row) . '\' title="Edit"><i class="ri-pencil-line"></i></button>';
                $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                return '<div class="d-flex gap-2 justify-content-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    /**
     * Store a new business category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:business_categories,slug',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        $category = BusinessCategory::create([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $request->input('sort_order', 0),
        ]);

        return $this->success('Business category created successfully.', [
            'category' => $category,
        ]);
    }

    /**
     * Update an existing business category.
     */
    public function update(Request $request, BusinessCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:business_categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        $category->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $request->input('sort_order', 0),
        ]);

        return $this->success('Business category updated successfully.', [
            'category' => $category->fresh(),
        ]);
    }

    /**
     * Delete a business category.
     */
    public function destroy(BusinessCategory $category)
    {
        $category->delete();

        return $this->success('Business category deleted successfully.');
    }
}
