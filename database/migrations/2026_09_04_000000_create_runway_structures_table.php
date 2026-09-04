<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('runway.tables.structures', 'runway_structures'), function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('model_id', 36);
            $table->unsignedInteger('order');
            $table->timestamps();

            $table->unique(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('runway.tables.structures', 'runway_structures'));
    }
};
