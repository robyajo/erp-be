<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'username' => $this->username,
            'email' => $this->email,
            'displayName' => $this->name,
            'name' => $this->name,
            'avatarUrl' => $this->avatar,
            'bio' => null,
            'whatsapp' => null,
            'instagram' => null,
            'tiktok' => null,
            'youtube' => null,
            'website' => null,
            'role' => $this->roles->pluck('name')->implode(','),
            'provider' => $this->provider_name ?? 'email',
            'isActive' => $this->is_active === 'active',
            'emailVerified' => !is_null($this->email_verified_at),
            'emailVerifiedAt' => $this->email_verified_at?->toIso8601String(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
