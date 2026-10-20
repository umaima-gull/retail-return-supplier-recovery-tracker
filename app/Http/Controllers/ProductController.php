<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'supplier'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w
                    ->where('name', 'like', $t)
                    ->orWhere('name_ur', 'like', $t)
                    ->orWhere('sku', 'like', $t)
                    ->orWhere('barcode', 'like', $t));
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->status === 'low', fn ($q) => $q->whereColumn('stock_qty', '<=', 'reorder_level'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $product = new Product([
            'unit' => 'pcs', 'cost_price' => 0, 'sale_price' => 0, 'stock_qty' => 0,
            'reorder_level' => 10, 'has_expiry' => true, 'is_returnable' => true, 'is_active' => true,
        ]);

        return view('products.form', $this->formData($product));
    }

    public function store(ProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('products.index')->with('success', __('products.saved'));
    }

    public function edit(Product $product)
    {
        return view('products.form', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.index')->with('success', __('products.updated'));
    }

    public function destroy(Product $product)
    {
        $product->delete(); // soft delete: purane claims ka record bacha rehta hai

        return redirect()->route('products.index')->with('success', __('products.deleted'));
    }

    /** Form ke dropdowns ka data */
    private function formData(Product $product): array
    {
        return [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers'  => Supplier::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $product->supplier_id))
                                ->orderBy('name')->get(),
        ];
    }
}