<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_moves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('operation_id')->constrained('inventory_operations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('inventory_products')->restrictOnDelete();
            $table->foreignId('source_location_id')->constrained('inventory_locations')->restrictOnDelete();
            $table->foreignId('destination_location_id')->constrained('inventory_locations')->restrictOnDelete();
            $table->integer('requested_qty')->default(0);
            $table->integer('reserved_qty')->default(0);
            $table->integer('done_qty')->default(0);
            $table->enum('state', ['draft', 'waiting', 'confirmed', 'assigned', 'partially_assigned', 'done', 'canceled'])->default('draft');
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_moves');
    }
};
