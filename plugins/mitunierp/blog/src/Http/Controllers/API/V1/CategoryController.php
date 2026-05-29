<?php

declare(strict_types=1);

namespace Mitunierp\Blog\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Blog\Models\Category;
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
        return $this->success($category->load('posts'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort' => ['integer', 'min:0'],
        ]);

        $category = Category::query()->create($validated);

        return $this->created($category, 'Category created.');
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort' => ['integer', 'min:0'],
        ]);

        $category->update($validated);

        return $this->success($category, 'Category updated.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return $this->noContent();
    }
}
