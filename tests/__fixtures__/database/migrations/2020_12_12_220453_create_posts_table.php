<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use StatamicRadPack\Runway\Tests\Fixtures\Enums\MembershipStatus;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->longText('body');
            $table->json('values')->nullable();
            $table->json('external_links')->nullable();
            $table->integer('author_id')->nullable();
            $table->integer('sort_order')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->boolean('published')->default(false);
            $table->string('mutated_value')->nullable();
            $table->string('membership_status')->default(MembershipStatus::Free->value);
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
        Schema::dropIfExists('posts');
    }
}
