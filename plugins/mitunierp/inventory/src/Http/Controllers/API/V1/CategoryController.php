<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Http\Controllers\API\V1;

use Mitunierp\Inventory\Models\Category;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

final class CategoryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $categories = QueryBuilder::for(Category::class)
            ->allowedFilters(['name', 'is_active'])
            ->allowedSorts(['name', 'sort', 'created_at'])
            ->defaultSort('sort')
            ->paginate($request->input('per_page', 15));

        return $this->success($categories);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('products');

        return $this->success($category);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:inventory_categories,id'],
            'is_active' => ['boolean'],
            'sort' => ['integer', 'min:0'],
        ]);

        $category = Category::query()->create($validated);

        return $this->created($category, 'Category created successfully.');
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:inventory_categories,id'],
            'is_active' => ['boolean'],
            'sort' => ['integer', 'min:0'],
        ]);

        $category->update($validated);

        return $this->success($category, 'Category updated successfully.');
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return $this->error('Cannot delete category with existing products.', 409);
        }

        $category->delete();

        return $this->noContent();
    }
}
