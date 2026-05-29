<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts_partners', function (Blueprint $table): void {
            $table->id();
            $table->enum('account_type', ['individual', 'company'])->default('individual');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('job_title')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->string('reference')->nullable();
            $table->string('street1')->nullable();
            $table->string('street2')->nullable();
            $table->string('city')->nullable();
            $table->string('zip', 20)->nullable();
            $table->foreignId('title_id')->nullable()->constrained('contacts_titles')->nullOnDelete();
            $table->foreignId('industry_id')->nullable()->constrained('contacts_industries')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('contacts_partners')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('contacts_partners')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts_partners');
    }
};
