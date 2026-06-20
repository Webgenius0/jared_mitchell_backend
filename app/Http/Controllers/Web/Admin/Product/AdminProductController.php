<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AdminProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        return view('web.admin.products.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = Product::query()->with('category');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('product_info', function ($row) {
                $img = $row->thumbnail
                    ? '<img src="' . asset('/' . $row->thumbnail) . '" alt="" class="avatar-xs rounded me-2" style="object-fit: cover; width: 36px; height: 36px;">'
                    : '<div class="avatar-xs rounded me-2 bg-light d-inline-flex align-items-center justify-content-center"><i class="ri-image-line text-muted"></i></div>';

                return '<div class="d-flex align-items-center">' .
                    $img .
                    '<div>' .
                    '<strong>' . e($row->name) . '</strong>' .
                    '<br><small class="text-muted">' . e(Str::limit($row->brand, 20)) . '</small>' .
                    '</div></div>';
            })
            ->addColumn('category_name', function ($row) {
                return $row->category ? e($row->category->name) : '<span class="text-muted">—</span>';
            })
            ->addColumn('price_display', function ($row) {
                if ($row->sale_price) {
                    return '<span class="text-decoration-line-through text-muted">$' . number_format($row->price, 2) . '</span> ' .
                        '<span class="text-danger fw-semibold">$' . number_format($row->sale_price, 2) . '</span>';
                }
                return '<span class="fw-semibold">$' . number_format($row->price, 2) . '</span>';
            })
            ->addColumn('stock_info', function ($row) {
                if ($row->track_stock) {
                    $badge = $row->stock > 0 ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' . $badge . '">' . $row->stock . '</span>';
                }
                return '<span class="badge bg-secondary">Unlimited</span>';
            })
            ->addColumn('status', function ($row) {
                $status = $row->is_active ? 'Active' : 'Inactive';
                $class  = $row->is_active ? 'bg-success' : 'bg-danger';
                return '<span class="badge ' . $class . '">' . $status . '</span>';
            })
            ->addColumn('action', function ($row) {
                $showBtn   = '<a href="' . route('admin.products.show', $row->id) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>';
                $editBtn   = '<a href="' . route('admin.products.edit', $row->id) . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>';
                $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                return '<div class="d-flex gap-1 justify-content-center">' . $showBtn . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['product_info', 'category_name', 'price_display', 'stock_info', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = ProductCategory::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('web.admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:products,slug',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0|lt:price',
            'stock'             => 'required|integer|min:0',
            'track_stock'       => 'nullable|boolean',
            'type'              => 'required|in:physical,digital,service',
            'brand'             => 'nullable|string|max:255',
            'thumbnail'         => 'nullable|image|max:2048',
            'images'            => 'nullable|array',
            'images.*'          => 'image|max:2048',
            'vendor_name'       => 'nullable|string|max:255',
            'vendor_email'      => 'nullable|email|max:255',
            'vendor_phone'      => 'nullable|string|max:50',
            'vendor_address'    => 'nullable|string|max:500',
            'vendor_details'    => 'nullable|string',
            'category_id'       => 'nullable|exists:product_categories,id',
            'is_featured'       => 'nullable|boolean',
            'is_active'         => 'nullable|boolean',
        ]);

        $data = $request->except(['thumbnail', 'images', 'slug']);
        $data['slug'] = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);
        $data['track_stock'] = $request->boolean('track_stock', true);
        $data['is_featured'] = $request->boolean('is_featured', false);
        $data['is_active']   = $request->boolean('is_active', true);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = FileHandle::fileUpload($request->file('thumbnail'), 'products/thumbnails');
        }

        $product = Product::create($data);

        // Handle multiple gallery images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = FileHandle::fileUpload($image, 'products/images');
                if ($path) {
                    $product->images()->create([
                        'image'      => $path,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'images']);
        return view('web.admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = ProductCategory::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('web.admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0|lt:price',
            'stock'             => 'required|integer|min:0',
            'track_stock'       => 'nullable|boolean',
            'type'              => 'required|in:physical,digital,service',
            'brand'             => 'nullable|string|max:255',
            'thumbnail'         => 'nullable|image|max:2048',
            'images'            => 'nullable|array',
            'images.*'          => 'image|max:2048',
            'delete_images'     => 'nullable|array',
            'delete_images.*'   => 'integer|exists:product_images,id',
            'vendor_name'       => 'nullable|string|max:255',
            'vendor_email'      => 'nullable|email|max:255',
            'vendor_phone'      => 'nullable|string|max:50',
            'vendor_address'    => 'nullable|string|max:500',
            'vendor_details'    => 'nullable|string',
            'category_id'       => 'nullable|exists:product_categories,id',
            'is_featured'       => 'nullable|boolean',
            'is_active'         => 'nullable|boolean',
        ]);

        $data = $request->except(['thumbnail', 'images', 'delete_images', 'slug']);
        $data['slug'] = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);
        $data['track_stock'] = $request->boolean('track_stock', true);
        $data['is_featured'] = $request->boolean('is_featured', false);
        $data['is_active']   = $request->boolean('is_active', true);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($product->thumbnail) {
                FileHandle::fileDelete($product->thumbnail);
            }
            $data['thumbnail'] = FileHandle::fileUpload($request->file('thumbnail'), 'products/thumbnails');
        }

        $product->update($data);

        // Delete selected existing images
        if ($request->filled('delete_images')) {
            $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)
                ->where('product_id', $product->id)
                ->get();

            foreach ($imagesToDelete as $image) {
                FileHandle::fileDelete($image->image);
                $image->delete();
            }
        }

        // Handle new gallery image uploads
        if ($request->hasFile('images')) {
            // Get the current max sort_order
            $maxSort = $product->images()->max('sort_order') ?? -1;

            foreach ($request->file('images') as $index => $image) {
                $path = FileHandle::fileUpload($image, 'products/images');
                if ($path) {
                    $product->images()->create([
                        'image'      => $path,
                        'sort_order' => $maxSort + 1 + $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Delete thumbnail
        if ($product->thumbnail) {
            FileHandle::fileDelete($product->thumbnail);
        }

        // Delete associated images
        foreach ($product->images as $image) {
            FileHandle::fileDelete($image->image);
            $image->delete();
        }

        $product->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
