<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Helpers\FileHandle;
use App\Models\Product;
use App\Support\ProductForm;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public $components;

    public function __construct()
    {
        $this->components = ['name', 'image', 'description', 'price', 'discount_price', 'status', 'stock'];
    }

    public static function productType($variable)
    {
        switch ($variable) {
            case 'digital':
                return ['name', 'image', 'description', 'price', 'discount_price', 'target_audience', 'status'];
                break;
            case 'vendor':
                return ['name', 'image', 'description', 'price', 'status', 'target_audience', 'delivery_type'];
                break;

            default:
                return ['name', 'image', 'description', 'price',  'category', 'status', 'stock'];
                break;
        }
    }
    public function index()
    {
        $totalProducts   = Product::count();
        $activeProducts  = Product::where('status', 'active')->count();
        $inactiveProducts = Product::where('status', 'inactive')->count();
        $uniqueTypes     = Product::select('type')->distinct()->count();

        // For filter dropdown
        $types = Product::select('type')->whereNotNull('type')->distinct()->pluck('type')->sort()->values();
        $categories = Product::select('category')->whereNotNull('category')->distinct()->pluck('category')->sort()->values();

        return view('web.admin.products.index', compact(
            'totalProducts',
            'activeProducts',
            'inactiveProducts',
            'uniqueTypes',
            'types',
            'categories'
        ));
    }

    public function create(Request $request,$target = null )
    {
        $type = $request->get('type', 'physical');
        $fields = ProductForm::fields($type);
        $components = self::productType($target);

        return view('web.admin.products.create', ['components' => $components,'target' => $target]);
    }

    public function store(Request $request,$target = null)
    {
        $type = $target;
        $fields = ProductForm::fields($type);

        $validated = $request->validate(ProductForm::rules($fields));
        $validated['type'] = $type;

        $validated = $this->handleFileUploads($request, $validated);

        $product = Product::create($validated);

        return redirect()
            ->route('admin.products.create', $product->type)
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product, Request $request, $target = null)
    {
        $type = $request->get('type', $product->type ?? 'physical');
        $fields = ProductForm::fields($type);
        $components = self::productType($target);


        return view('web.admin.products.edit', compact('product', 'fields', 'type', 'components','target'));
    }

    public function update(Request $request, Product $product, $target = null)
    {
        $type = $target ?? $product->type ?? 'physical';
        $fields = ProductForm::fields($type);

        $validated = $request->validate(ProductForm::rules($fields, $product->id));
        $validated['type'] = $type;

        $validated = $this->handleFileUploads($request, $validated, $product);

        $product->update($validated);

        return redirect()
            ->route('admin.products.edit', [$product,$product->type])
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function getData(Request $request)
    {
        $query = Product::query();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        return DataTables::of($query->latest())
            ->addIndexColumn()
            ->addColumn('product', function (Product $product) {
                $name = e($product->name);
                $type = e($product->type ?? 'standard');
                $category = e($product->category ?? '—');

                $meta = sprintf(
                    '<small class="text-muted">SKU: %s &nbsp;|&nbsp; Display ID: %s</small><br><small class="text-muted">Type: %s | Category: %s</small>',
                    e($product->sku ?? 'auto'),
                    e($product->display_id ?? 'auto'),
                    $type,
                    $category
                );

                return "<div class=\"fw-semibold\">{$name}</div>{$meta}";
            })
            ->addColumn('price', function (Product $product) {
                $price = number_format($product->price, 2);
                $discount = $product->discount_price ? number_format($product->discount_price, 2) : null;
                return $discount
                    ? "<div>\${$discount} <span class=\"text-muted text-decoration-line-through\">\${$price}</span></div>"
                    : "\${$price}";
            })
            ->addColumn('status', function (Product $product) {
                $class = $product->status === 'active'
                    ? 'bg-success-subtle text-success'
                    : 'bg-secondary-subtle text-secondary';
                return '<span class="badge ' . $class . '">' . e(ucfirst($product->status)) . '</span>';
            })
            ->addColumn('created_at', fn(Product $product) => $product->created_at?->format('M d, Y'))
            ->addColumn('action', function (Product $product) {
                $editUrl = route('admin.products.edit', [$product, $product->type]);
                $deleteUrl = route('admin.products.destroy', $product);

                return '<div class="d-flex gap-2 justify-content-center">'
                    . '<a href="' . $editUrl . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>'
                    . '<button type="button" class="btn btn-sm btn-soft-danger btn-delete" data-url="' . $deleteUrl . '" data-name="' . e($product->name) . '" title="Delete"><i class="ri-delete-bin-line"></i></button>'
                    . '</div>';
            })
            ->filterColumn('product', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('sku', 'like', "%{$keyword}%")
                        ->orWhere('display_id', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%")
                        ->orWhere('type', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('product', function ($query, $order) {
                $query->orderBy('name', $order);
            })
            ->rawColumns(['product', 'price', 'status', 'action'])
            ->make(true);
    }

    /**
     * Upload image if present.
     */
    private function handleFileUploads(Request $request, array $data, ?Product $product = null): array
    {
        if ($request->hasFile('image')) {
            if ($product && $product->image) {
                FileHandle::fileDelete($product->image);
            }
            $path = FileHandle::fileUpload($request->file('image'), 'products');
            if ($path) {
                $data['image'] = $path;
            }
        }

        return $data;
    }
}
