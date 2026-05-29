<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts_partner_tag', function (Blueprint $table): void {
            $table->foreignId('partner_id')->constrained('contacts_partners')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('contacts_tags')->cascadeOnDelete();
            $table->primary(['partner_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts_partner_tag');
    }
};
