<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('author')->nullable();
            $table->text('summary')->nullable();
            $table->string('version')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_installed')->default(false);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('plugin_dependencies', function (Blueprint $table): void {
            $table->foreignId('plugin_id')->constrained('plugins')->cascadeOnDelete();
            $table->foreignId('dependency_id')->constrained('plugins')->cascadeOnDelete();
            $table->unique(['plugin_id', 'dependency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_dependencies');
        Schema::dropIfExists('plugins');
    }
};
