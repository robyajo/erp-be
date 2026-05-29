<?php

declare(strict_types=1);

namespace Mitunierp\Blog\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Blog\Models\Post;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class PostController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedIncludes(['category', 'tags'])
            ->allowedFilters([
                AllowedFilter::exact('category_id'),
                AllowedFilter::partial('title'),
                AllowedFilter::exact('is_published'),
            ])
            ->allowedSorts(['title', 'published_at', 'created_at'])
            ->defaultSort('-published_at')
            ->paginate($request->input('per_page', 15));

        return $this->success($posts);
    }

    public function show(Post $post): JsonResponse
    {
        return $this->success($post->load(['category', 'tags']));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'exists:blog_categories,id'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:blog_tags,id'],
        ]);

        $post = Post::query()->create($validated);

        if (!empty($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return $this->created($post->load('tags'), 'Post created.');
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['string', 'max:255'],
            'content' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'exists:blog_categories,id'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:blog_tags,id'],
        ]);

        $post->update($validated);

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return $this->success($post->load('tags'), 'Post updated.');
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return $this->noContent();
    }
}
