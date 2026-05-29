<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Contacts\Models\Industry;
use Spatie\QueryBuilder\QueryBuilder;

final class IndustryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $industries = QueryBuilder::for(Industry::class)
            ->allowedFilters('name', 'is_active')
            ->allowedSorts('name', 'created_at')
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($industries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        return $this->created(Industry::query()->create($validated), 'Industry created.');
    }

    public function update(Request $request, Industry $industry): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $industry->update($validated);

        return $this->success($industry, 'Industry updated.');
    }

    public function destroy(Industry $industry): JsonResponse
    {
        $industry->delete();

        return $this->noContent();
    }
}
