<?php

namespace Esign\Linkable\Tests\Feature\Concerns;

use Esign\Linkable\Concerns\LinksDynamically;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LinksDynamicallyTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_check_if_it_has_an_internal_link()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeInternal,
            'linkable_model' => "post:{$post->id}",
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeInternal,
            'linkable_model' => "post:non-existing-id",
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);

        $this->assertTrue($menuItemA->hasLink());
        $this->assertFalse($menuItemB->hasLink());
    }

    /** @test */
    public function it_can_check_if_it_has_an_external_link()
    {
        $menuItemA = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeExternal,
            'linkable_model' => null,
            'link_url' => 'http://localhost',
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeExternal,
            'linkable_model' => null,
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);

        $this->assertTrue($menuItemA->hasLink());
        $this->assertFalse($menuItemB->hasLink());
    }

    /** @test */
    public function it_can_get_an_internal_link()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeInternal,
            'linkable_model' => "post:{$post->id}",
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeInternal,
            'linkable_model' => "post:non-existing-id",
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);

        $this->assertEquals("http://localhost/posts/{$post->id}", $menuItemA->link());
        $this->assertNull($menuItemB->link());
    }

    /** @test */
    public function it_can_get_an_external_url()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeExternal,
            'linkable_model' => null,
            'link_url' => 'http://localhost',
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeExternal,
            'linkable_model' => null,
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);

        $this->assertEquals("http://localhost", $menuItemA->link());
        $this->assertNull($menuItemB->link());
    }

    /** @test */
    public function it_can_check_if_a_link_is_of_type()
    {
        $menuItem = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeExternal,
            'linkable_model' => null,
            'link_url' => 'http://localhost',
            'link_label' => 'Link to hello world',
        ]);

        $this->assertTrue($menuItem->linkIsOfType(LinksDynamically::$linkTypeExternal));
        $this->assertTrue($menuItem->linkIsOfType([LinksDynamically::$linkTypeExternal, LinksDynamically::$linkTypeInternal]));
        $this->assertFalse($menuItem->linkIsOfType(LinksDynamically::$linkTypeInternal));
        $this->assertFalse($menuItem->linkIsOfType([LinksDynamically::$linkTypeInternal]));
    }
}
