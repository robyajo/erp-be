<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_plugin', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plugin_name');
            $table->boolean('is_active')->default(true);
            $table->timestamp('installed_at')->nullable();
            $table->primary(['user_id', 'plugin_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_plugin');
    }
};
