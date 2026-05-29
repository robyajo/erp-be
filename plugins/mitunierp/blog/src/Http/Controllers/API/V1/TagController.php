<?php

declare(strict_types=1);

namespace Mitunierp\Blog\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Blog\Models\Tag;
use Spatie\QueryBuilder\QueryBuilder;

final class TagController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $tags = QueryBuilder::for(Tag::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['name', 'sort'])
            ->defaultSort('sort')
            ->paginate($request->input('per_page', 15));

        return $this->success($tags);
    }

    public function show(Tag $tag): JsonResponse
    {
        return $this->success($tag->load('posts'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:blog_tags,name'],
            'color' => ['nullable', 'string', 'max:50'],
            'sort' => ['integer', 'min:0'],
        ]);

        $tag = Tag::query()->create($validated);

        return $this->created($tag, 'Tag created.');
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255', 'unique:blog_tags,name,' . $tag->id],
            'color' => ['nullable', 'string', 'max:50'],
            'sort' => ['integer', 'min:0'],
        ]);

        $tag->update($validated);

        return $this->success($tag, 'Tag updated.');
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return $this->noContent();
    }
}
