<?php

namespace Esign\Linkable\Tests\Feature\Relations;

use PHPUnit\Framework\Attributes\Test;
use Error;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PDOException;

class SingleColumnMorphToTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_can_query_a_related_model(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => "post:{$post->id}"]);

        $this->assertTrue($menuItem->dynamicLinkLinkable->is($post));
    }

    #[Test]
    public function it_can_return_null_if_a_related_model_does_not_exist(): void
    {
        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => "post:non-existing-id"]);

        $this->assertNull($menuItem->dynamicLinkLinkable);
    }

    #[Test]
    public function it_can_return_null_if_the_foreign_key_is_null(): void
    {
        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => null]);

        $this->assertNull($menuItem->dynamicLinkLinkable);
    }

    #[Test]
    public function it_can_throw_an_exception_if_the_foreign_key_is_empty(): void
    {
        $this->expectException(PDOException::class);

        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => '']);

        $this->assertNull($menuItem->dynamicLinkLinkable);
    }

    #[Test]
    public function it_can_throw_an_exception_if_the_model_does_not_exist(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Class "article" not found');
        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => 'article:1']);

        $this->assertNull($menuItem->dynamicLinkLinkable);
    }

    #[Test]
    public function it_can_eager_load_related_models(): void
    {
        $postA = Post::create(['title' => 'Hello World']);
        $postB = Post::create(['title' => 'Hello World 2']);
        MenuItem::create(['dynamic_link_linkable_model' => "post:{$postA->id}"]);
        MenuItem::create(['dynamic_link_linkable_model' => "post:{$postB->id}"]);

        $menuItemLinkables = MenuItem::with('dynamicLinkLinkable')->get()->map->dynamicLinkLinkable;

        $this->assertTrue($menuItemLinkables->contains($postA));
        $this->assertTrue($menuItemLinkables->contains($postB));
    }

    #[Test]
    public function it_can_associate_a_model(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => null]);
        $menuItem = $menuItem->dynamicLinkLinkable()->associate($post);

        $this->assertEquals("post:{$post->id}", $menuItem->dynamic_link_linkable_model);
        $this->assertTrue($menuItem->dynamicLinkLinkable->is($post));
    }

    #[Test]
    public function it_can_associate_a_null_value(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => "post:{$post->id}"]);
        $menuItem = $menuItem->dynamicLinkLinkable()->associate(null);

        $this->assertNull($menuItem->dynamic_link_linkable_model);
        $this->assertNull($menuItem->dynamicLinkLinkable);
    }

    #[Test]
    public function it_can_dissociate_a_model(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create(['dynamic_link_linkable_model' => "post:{$post->id}"]);

        $this->assertTrue($menuItem->dynamicLinkLinkable->is($post));

        $menuItem = $menuItem->dynamicLinkLinkable()->dissociate();

        $this->assertNull($menuItem->dynamic_link_linkable_model);
        $this->assertNull($menuItem->dynamicLinkLinkable);
    }
}
