<?php

namespace Esign\Linkable\Tests\Feature\Relations;

use Error;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SingleColumnMorphToTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_query_a_related_model()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['link_entry' => "post:{$post->id}"]);

        $this->assertTrue($menuItem->linkable->is($post));
    }

    /** @test */
    public function it_can_return_null_if_a_related_model_does_not_exist()
    {
        $menuItem = MenuItem::create(['link_entry' => "post:non-existing-id"]);

        $this->assertNull($menuItem->linkable);
    }

    /** @test */
    public function it_can_return_null_if_the_foreign_key_is_empty()
    {
        $menuItemA = MenuItem::create(['link_entry' => null]);
        $menuItemB = MenuItem::create(['link_entry' => '']);

        $this->assertNull($menuItemA->linkable);
        $this->assertNull($menuItemB->linkable);
    }

    /** @test */
    public function it_can_throw_an_exception_if_the_model_does_not_exist()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Class "article" not found');
        $menuItem = MenuItem::create(['link_entry' => 'article:1']);

        $this->assertNull($menuItem->linkable);
    }

    /** @test */
    public function it_can_eager_load_related_models()
    {
        $postA = Post::create(['title' => 'Hello World']);
        $postB = Post::create(['title' => 'Hello World 2']);
        MenuItem::create(['link_entry' => "post:{$postA->id}"]);
        MenuItem::create(['link_entry' => "post:{$postB->id}"]);

        $menuItemLinkables = MenuItem::with('linkable')->get()->map->linkable;

        $this->assertTrue($menuItemLinkables->contains($postA));
        $this->assertTrue($menuItemLinkables->contains($postB));
    }

    /** @test */
    public function it_can_associate_a_model()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['link_entry' => null]);
        $menuItem = $menuItem->linkable()->associate($post);

        $this->assertEquals("post:{$post->id}", $menuItem->link_entry);
        $this->assertTrue($menuItem->linkable->is($post));
    }

    /** @test */
    public function it_can_associate_a_null_value()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['link_entry' => "post:{$post->id}"]);
        $menuItem = $menuItem->linkable()->associate(null);

        $this->assertNull($menuItem->link_entry);
        $this->assertNull($menuItem->linkable);
    }

    /** @test */
    public function it_can_dissociate_a_model()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['link_entry' => "post:{$post->id}"]);

        $this->assertTrue($menuItem->linkable->is($post));

        $menuItem = $menuItem->linkable()->dissociate();

        $this->assertNull($menuItem->link_entry);
        $this->assertNull($menuItem->linkable);
    }
}
