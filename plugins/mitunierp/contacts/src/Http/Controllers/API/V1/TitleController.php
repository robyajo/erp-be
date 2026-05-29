<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Contacts\Models\Title;
use Spatie\QueryBuilder\QueryBuilder;

final class TitleController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $titles = QueryBuilder::for(Title::class)
            ->allowedSorts('name', 'created_at')
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($titles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
        ]);

        return $this->created(Title::query()->create($validated), 'Title created.');
    }

    public function update(Request $request, Title $title): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
        ]);

        $title->update($validated);

        return $this->success($title, 'Title updated.');
    }

    public function destroy(Title $title): JsonResponse
    {
        $title->delete();

        return $this->noContent();
    }
}
