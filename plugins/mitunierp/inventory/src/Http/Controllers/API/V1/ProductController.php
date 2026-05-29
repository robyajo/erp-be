<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Http\Controllers\API\V1;

use Mitunierp\Inventory\Models\Product;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ProductController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedIncludes('category')
            ->allowedFilters(
                AllowedFilter::exact('category_id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('sku'),
                AllowedFilter::partial('barcode'),
                AllowedFilter::exact('is_active'),
            )
            ->allowedSorts('name', 'sku', 'price', 'created_at')
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'stockMovements']);

        $data = $product->toArray();
        $data['current_stock'] = $product->currentStock();

        return $this->success($data);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:inventory_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:inventory_products,sku'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:inventory_products,barcode'],
            'image' => ['nullable', 'string', 'max:255'],
            'min_stock' => ['integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $product = Product::query()->create($validated);

        return $this->created($product, 'Product created successfully.');
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:inventory_categories,id'],
            'name' => ['string', 'max:255'],
            'sku' => ['string', 'max:100', 'unique:inventory_products,sku,' . $product->id],
            'description' => ['nullable', 'string'],
            'price' => ['numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:inventory_products,barcode,' . $product->id],
            'image' => ['nullable', 'string', 'max:255'],
            'min_stock' => ['integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $product->update($validated);

        return $this->success($product, 'Product updated successfully.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->noContent();
    }
}
