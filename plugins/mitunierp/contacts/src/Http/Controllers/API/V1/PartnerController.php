<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Contacts\Models\Partner;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class PartnerController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $partners = QueryBuilder::for(Partner::class)
            ->allowedIncludes('title', 'industry', 'company', 'tags')
            ->allowedFilters(
                AllowedFilter::exact('account_type'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('city'),
                AllowedFilter::exact('industry_id'),
                AllowedFilter::exact('is_active'),
            )
            ->allowedSorts('name', 'created_at', 'city')
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($partners);
    }

    public function show(Partner $partner): JsonResponse
    {
        return $this->success($partner->load(['title', 'industry', 'company', 'tags', 'contacts']));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_type' => ['required', 'string', 'in:individual,company'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'street1' => ['nullable', 'string', 'max:255'],
            'street2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'title_id' => ['nullable', 'exists:contacts_titles,id'],
            'industry_id' => ['nullable', 'exists:contacts_industries,id'],
            'parent_id' => ['nullable', 'exists:contacts_partners,id'],
            'company_id' => ['nullable', 'exists:contacts_partners,id'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:contacts_tags,id'],
        ]);

        $partner = Partner::query()->create($validated);

        if (!empty($validated['tags'])) {
            $partner->tags()->sync($validated['tags']);
        }

        return $this->created($partner->load('tags'), 'Partner created.');
    }

    public function update(Request $request, Partner $partner): JsonResponse
    {
        $validated = $request->validate([
            'account_type' => ['string', 'in:individual,company'],
            'name' => ['string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'street1' => ['nullable', 'string', 'max:255'],
            'street2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'title_id' => ['nullable', 'exists:contacts_titles,id'],
            'industry_id' => ['nullable', 'exists:contacts_industries,id'],
            'parent_id' => ['nullable', 'exists:contacts_partners,id'],
            'company_id' => ['nullable', 'exists:contacts_partners,id'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:contacts_tags,id'],
        ]);

        $partner->update($validated);

        if (isset($validated['tags'])) {
            $partner->tags()->sync($validated['tags']);
        }

        return $this->success($partner->load('tags'), 'Partner updated.');
    }

    public function destroy(Partner $partner): JsonResponse
    {
        $partner->delete();

        return $this->noContent();
    }
}
