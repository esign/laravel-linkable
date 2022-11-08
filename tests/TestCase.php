<?php

namespace Esign\Linkable\Tests;

use Esign\Linkable\LinkableServiceProvider;
use Esign\Linkable\Tests\Support\Models\Post;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Relation::morphMap([
            'post' => Post::class,
        ]);

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('dynamic_link_type')->nullable();
            $table->string('linkable_model')->nullable();
            $table->string('dynamic_link_url')->nullable();
            $table->string('dynamic_link_label')->nullable();
        });

        DB::statement('
            CREATE OR REPLACE VIEW linkables AS
            SELECT
                CONCAT("post:", id) AS id,
                "post" AS linkable_type,
                id AS linkable_id,
                CONCAT("Post - ", title) AS label
            FROM posts
        ');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('menu_items');
        DB::statement("DROP VIEW IF EXISTS linkables");

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [LinkableServiceProvider::class];
    }
}
