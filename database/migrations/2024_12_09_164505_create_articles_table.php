<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_id');
            $table->unsignedBigInteger('category_id');
            $table->text('link');
            $table->text('thumbnail')->nullable();
            $table->mediumText('headline')->fulltext('headline');
            $table->longText('body')->fulltext('body');
            $table->dateTime('published_at');
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropUnique(['provider', 'provider_id']);
        });
        Schema::dropIfExists('articles');
    }
};
