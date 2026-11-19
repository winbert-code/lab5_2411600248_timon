<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $brandId = $request->integer('brand_id');
        $categoryId = $request->integer('category_id');
        $status = $request->string('status')->toString();

        if (! in_array($status, ['active', 'inactive', 'all'], true)) {
            $status = 'all';
        }

        $products = Product::query()
            ->with(['brand', 'category'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%");
                });
            })
            ->when($brandId > 0, fn ($query) => $query->where('brand_id', $brandId))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($status === 'active', fn ($query) => $query->active())
            ->when($status === 'inactive', fn ($query) => $query->inactive())
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'search' => $search,
            'brandId' => $brandId,
            'categoryId' => $categoryId,
            'status' => $status,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product(),
            'brands' => $this->brandOptions(new Product()),
            'categories' => $this->categoryOptions(new Product()),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($request->validated());

            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => InventoryTransaction::TYPE_IN,
                'quantity' => $product->quantity,
                'reference' => 'Initial stock',
                'user_id' => auth()->id(),
            ]);

            return $product;
        });

        return redirect()->route('products.show', $product)
            ->with('success', "{$product->name} was added to the inventory.");
    }

    public function show(Product $product): View
    {
        $product->load(['brand', 'category', 'transactions.user']);

        return view('products.show', ['product' => $product]);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'brands' => $this->brandOptions($product),
            'categories' => $this->categoryOptions($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $quantityBefore = $product->quantity;

        DB::transaction(function () use ($request, $product, $quantityBefore) {
            $product->update($request->validated());

            $difference = $product->quantity - $quantityBefore;

            if ($difference !== 0) {
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'type' => $difference > 0 ? InventoryTransaction::TYPE_IN : InventoryTransaction::TYPE_OUT,
                    'quantity' => abs($difference),
                    'reference' => 'Stock adjustment',
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('products.show', $product)
            ->with('success', "{$product->name} was updated.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->deactivate();

        return redirect()->route('products.index')
            ->with('success', "{$name} was marked inactive. Its inventory record and stock history were kept.");
    }

    public function restore(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->activate();

        return redirect()->route('products.index')
            ->with('success', "{$name} is active again.");
    }

    /**
     * Inactive brands cannot be chosen for new stock, but an inactive brand
     * already assigned to this product must stay visible on the edit form.
     */
    private function brandOptions(Product $product)
    {
        $brands = Brand::active()->orderBy('name')->get();

        if ($product->brand && ! $product->brand->isActive()) {
            $brands->push($product->brand);
        }

        return $brands->sortBy('name')->values();
    }

    /**
     * Inactive categories cannot be chosen for new stock, but an inactive
     * category already assigned to this product must stay visible on edit.
     */
    private function categoryOptions(Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();

        if ($product->category && ! $product->category->isActive()) {
            $categories->push($product->category);
        }

        return $categories->sortBy('name')->values();
    }
}
