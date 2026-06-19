<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Traits\AdminApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AdminProductCategoryController extends Controller
{
    use AdminApiResponse;

    /**
     * Display a listing of product categories.
     */
    public function index()
    {
        return view('web.admin.product-category.product_categories');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = ProductCategory::query()->orderBy('name');

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
     * Store a new product category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'slug'      => 'nullable|string|max:255|unique:product_categories,slug',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        $category = ProductCategory::create([
            'name'      => $request->name,
            'slug'      => $slug,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->success('Product category created successfully.', [
            'category' => $category,
        ]);
    }

    /**
     * Update an existing product category.
     */
    public function update(Request $request, ProductCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'slug'      => 'nullable|string|max:255|unique:product_categories,slug,' . $category->id,
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        $category->update([
            'name'      => $request->name,
            'slug'      => $slug,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->success('Product category updated successfully.', [
            'category' => $category->fresh(),
        ]);
    }

    /**
     * Delete a product category.
     */
    public function destroy(ProductCategory $category)
    {
        $category->delete();

        return $this->success('Product category deleted successfully.');
    }
}
