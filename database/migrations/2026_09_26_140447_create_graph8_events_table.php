<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graph8_events', function (Blueprint $table) {
            $table->id();

            $table->string('external_id')
                ->nullable()
                ->unique();

            $table->string('event_type')->index();
            $table->string('source')->default('graph8');

            $table->string('company_id')->nullable()->index();
            $table->string('contact_id')->nullable()->index();
            $table->string('deal_id')->nullable()->index();

            $table->json('payload');
            $table->timestamp('occurred_at')->nullable()->index();

            $table->boolean('processed')
                ->default(false)
                ->index();

            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graph8_events');
    }
};