<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graph8_records', function (Blueprint $table) {
            $table->id();

            $table->string('record_type')->index();
            $table->string('external_id');
            $table->string('name')->nullable()->index();

            $table->json('payload');

            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique([
                'record_type',
                'external_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graph8_records');
    }
};