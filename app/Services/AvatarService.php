<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    public function ensureLocalAvatar(User $user): void
    {
        $avatar = $user->avatar;

        if (!$avatar) return;

        $url = config('app.url');
        $baseUrl = rtrim(is_string($url) ? $url : 'http://localhost', '/');

        // Jika sudah local URL, pastikan file masih ada
        if (str_starts_with($avatar, $baseUrl . '/storage/')) {
            $filename = str_replace($baseUrl . '/storage/', '', $avatar);
            if (!Storage::disk('public')->exists($filename)) {
                $user->updateQuietly(['avatar' => null]);
            }
            return;
        }

        // Remote URL — download/cache ulang
        $ext = $this->guessExtension($avatar);
        $filename = "avatars/{$user->id}.{$ext}";

        // Hapus cache lama jika ada
        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }

        try {
            $response = Http::timeout(5)->get($avatar);

            if ($response->successful()) {
                Storage::disk('public')->put($filename, $response->body());
                $user->updateQuietly(['avatar' => $this->localUrl($baseUrl, $filename)]);
            }
        } catch (ConnectionException) {
            // Keep original remote URL as fallback
        }
    }

    private function guessExtension(string $url): string
    {
        if (preg_match('/\.(jpe?g|png|gif|webp)(?:[?#]|$)/i', $url, $m)) {
            return strtolower($m[1]);
        }
        return 'jpg';
    }

    private function localUrl(string $baseUrl, string $filename): string
    {
        return "{$baseUrl}/storage/{$filename}";
    }
}
