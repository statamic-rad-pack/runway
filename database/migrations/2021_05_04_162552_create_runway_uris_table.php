<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRunwayUrisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('runway.uris_table', 'runway_uris'), function (Blueprint $table) {
            $table->id();
            $table->string('uri');
            $table->string('model_type');
            $table->string('model_id', 36);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('runway.uris_table', 'runway_uris'));
    }
}
