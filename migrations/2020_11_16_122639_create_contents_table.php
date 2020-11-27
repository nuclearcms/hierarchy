<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->bigInteger('content_type_id')->unsigned()->nullable();
            $table->bigInteger('position', false, true);
            $table->softDeletes();

            $table->boolean('is_visible')->default(1);
            $table->boolean('is_sterile')->default(0);
            $table->boolean('is_locked')->default(0);
            $table->boolean('hides_children')->default(0);
            $table->integer('status')->default(30);
            $table->timestamp('published_at')->nullable();
            $table->double('priority')->unsigned()->default(1);
            $table->enum('children_display_mode', ['tree', 'list'])->default('list');

            $table->json('title');
            $table->json('slug');
            $table->json('keywords')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('author')->nullable();
            $table->json('cover_image')->nullable();

            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('contents')
                ->onDelete('set null');

            $table->foreign('content_type_id')
                ->references('id')
                ->on('content_types')
                ->onDelete('cascade');
        });

        Schema::create('content_closure', function (Blueprint $table) {
            $table->bigIncrements('closure_id');

            $table->bigInteger('ancestor', false, true);
            $table->bigInteger('descendant', false, true);
            $table->bigInteger('depth', false, true);

            $table->foreign('ancestor')
                ->references('id')
                ->on('contents')
                ->onDelete('cascade');

            $table->foreign('descendant')
                ->references('id')
                ->on('contents')
                ->onDelete('cascade');
        });

        Schema::create('content_extensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('content_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('type');
            $table->json('value')->nullable();

            $table->foreign('content_id')
                ->references('id')
                ->on('contents')
                ->onDelete('cascade');
        });

        Schema::table('taggables', function (Blueprint $table) {
            $table->foreign('taggable_id')
                ->references('id')
                ->on('contents')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taggables', function (Blueprint $table) {
            $table->dropForeign('taggables_taggable_id_foreign');
        });
        Schema::dropIfExists('content_extensions');
        Schema::dropIfExists('content_closure');
        Schema::dropIfExists('contents');
    }
}
