<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand', 'primaryImage'])
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('stock_quantity', '>', 0)
                      ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            } elseif ($request->stock_status === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            }
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['sku'] = strtoupper(trim($data['sku']));
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 5;

        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                $isFirst = true;
                foreach ($request->file('images') as $index => $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $isFirst,
                        'sort_order' => $index,
                    ]);
                    $isFirst = false;
                }
            }

            // Log initial stock movement
            if ($product->stock_quantity > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type' => 'purchase',
                    'quantity_change' => $product->stock_quantity,
                    'previous_stock' => 0,
                    'current_stock' => $product->stock_quantity,
                    'remarks' => $request->input('stock_remarks', 'Initial stock entry upon product creation'),
                    'created_by_user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully with inventory recorded.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'brand', 'images', 'inventoryMovements.creator', 'reviews.user']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('images');
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['sku'] = strtoupper(trim($data['sku']));
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 5;

        DB::transaction(function () use ($data, $request, $product) {
            $oldStock = $product->stock_quantity;
            $newStock = (int) $data['stock_quantity'];

            $product->update($data);

            // Log stock adjustment if stock quantity was directly changed in edit form
            if ($oldStock !== $newStock) {
                $difference = $newStock - $oldStock;
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity_change' => $difference,
                    'previous_stock' => $oldStock,
                    'current_stock' => $newStock,
                    'remarks' => $request->input('stock_remarks', 'Stock updated via product edit form'),
                    'created_by_user_id' => auth()->id(),
                ]);
            }

            // Handle additional image uploads
            if ($request->hasFile('images')) {
                $hasPrimary = $product->images()->where('is_primary', true)->exists();
                $lastSort = $product->images()->max('sort_order') ?? 0;

                foreach ($request->file('images') as $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    $lastSort++;
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => !$hasPrimary,
                        'sort_order' => $lastSort,
                    ]);
                    $hasPrimary = true;
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete(); // Soft delete

        return redirect()->route('admin.products.index')->with('success', 'Product moved to trash successfully.');
    }

    public function deleteImage(Product $product, ProductImage $image): RedirectResponse
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $wasPrimary = $image->is_primary;
        $image->delete();

        // If primary image was deleted, promote the first remaining image
        if ($wasPrimary) {
            $nextImage = $product->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        return back()->with('success', 'Product image removed successfully.');
    }

    public function setPrimaryImage(Product $product, ProductImage $image): RedirectResponse
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return back()->with('success', 'Primary product image updated successfully.');
    }

    /* -------------------------------------------------------------------------
     | Stock & Inventory Management
     | ------------------------------------------------------------------------- */

    public function stockIndex(Request $request): View
    {
        $query = Product::with(['category', 'brand'])
            ->orderBy('stock_quantity', 'asc');

        if ($request->filled('filter')) {
            if ($request->filter === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($request->filter === 'low_stock') {
                $query->where('stock_quantity', '>', 0)
                      ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(20)->withQueryString();
        $recentMovements = InventoryMovement::with(['product', 'creator'])
            ->latest('id')
            ->take(10)
            ->get();

        return view('admin.products.stock', compact('products', 'recentMovements'));
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'adjustment_type' => ['required', 'in:add,subtract,set'],
            'quantity' => ['required', 'integer', 'min:0'],
            'remarks' => ['required', 'string', 'max:255'],
        ]);

        $adjustmentType = $request->input('adjustment_type');
        $qty = (int) $request->input('quantity');
        $remarks = trim($request->input('remarks'));

        DB::transaction(function () use ($product, $adjustmentType, $qty, $remarks) {
            $oldStock = $product->stock_quantity;

            if ($adjustmentType === 'add') {
                $newStock = $oldStock + $qty;
                $change = $qty;
            } elseif ($adjustmentType === 'subtract') {
                $newStock = max(0, $oldStock - $qty);
                $change = -min($qty, $oldStock);
            } else { // 'set'
                $newStock = $qty;
                $change = $newStock - $oldStock;
            }

            $product->update(['stock_quantity' => $newStock]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity_change' => $change,
                'previous_stock' => $oldStock,
                'current_stock' => $newStock,
                'remarks' => $remarks,
                'created_by_user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', "Stock for {$product->name} (SKU: {$product->sku}) updated successfully.");
    }
}
