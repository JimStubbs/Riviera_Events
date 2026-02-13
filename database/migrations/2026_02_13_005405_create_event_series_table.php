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
        Schema::create('event_series', function (Blueprint $table) {
            $table->id();

            // Ownership
            $table->foreignId('organizer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contact_email')->nullable();

            // Taxonomy
            $table->foreignId('town_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            // Content
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();

            // Time
            $table->string('timezone'); // copied from location at creation
            $table->boolean('is_all_day')->default(false);
            $table->dateTime('starts_at_local');
            $table->dateTime('ends_at_local');

            // Recurrence
            $table->string('rrule')->nullable();
            $table->dateTime('until_local')->nullable();
            $table->unsignedInteger('count')->nullable();
            $table->json('exdates')->nullable();

            // Monetization (premium applies to entire series)
            $table->boolean('is_premium')->default(false);
            $table->unsignedInteger('premium_price_mxn')->default(200);
            $table->dateTime('premium_paid_at')->nullable();
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();

            // Workflow + snapshots
            $table->string('status')->default('draft');
            $table->json('published_data')->nullable();
            $table->json('draft_data')->nullable();
            $table->dateTime('last_submitted_at')->nullable();
            $table->dateTime('last_approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->unique(['town_id', 'slug']);
            $table->index(['status', 'is_premium']);
            $table->index(['town_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_series');
    }
};
