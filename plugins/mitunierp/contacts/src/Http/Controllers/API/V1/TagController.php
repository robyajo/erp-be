<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Contacts\Models\Tag;
use Spatie\QueryBuilder\QueryBuilder;

final class TagController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $tags = QueryBuilder::for(Tag::class)
            ->allowedFilters('name')
            ->allowedSorts('name', 'created_at')
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($tags);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contacts_tags,name'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        return $this->created(Tag::query()->create($validated), 'Tag created.');
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255', 'unique:contacts_tags,name,' . $tag->id],
            'color' => ['nullable', 'string', 'max:50'],
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
