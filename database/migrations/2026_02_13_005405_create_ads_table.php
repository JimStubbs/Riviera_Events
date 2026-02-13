<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('placement'); // list_inline, sidebar, leaderboard, carousel_sponsor

            // null town_id = global ad
            $table->foreignId('town_id')->nullable()->constrained()->nullOnDelete();

            $table->string('image_url')->nullable();
            $table->text('html_snippet')->nullable();
            $table->string('target_url')->nullable();

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);

            $table->timestamps();

            $table->index(['placement', 'is_active']);
            $table->index(['town_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
