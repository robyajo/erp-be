<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plugins', function (Blueprint $table): void {
            if (!Schema::hasColumn('plugins', 'description')) {
                $table->text('description')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('plugins', 'latest_version')) {
                $table->string('latest_version')->nullable()->after('version');
            }
            if (!Schema::hasColumn('plugins', 'license')) {
                $table->string('license')->nullable()->after('latest_version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table): void {
            $table->dropColumn(['description', 'latest_version', 'license']);
        });
    }
};
