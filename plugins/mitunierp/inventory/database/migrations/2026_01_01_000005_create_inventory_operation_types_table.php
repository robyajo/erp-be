<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_operation_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->enum('type', ['receipt', 'delivery', 'internal_transfer']);
            $table->foreignId('source_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_operation_types');
    }
};
