<?php

namespace Esign\Linkable\Tests\Feature\Concerns;

use Esign\Linkable\Enums\LinkType;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LinksDynamicallyTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_cast_to_a_link_type_enum()
    {
        $menuItem = MenuItem::create([
            'link_type' => LinkType::EXTERNAL,
            'link_entry' => null,
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);

        $this->assertInstanceOf(LinkType::class, $menuItem->link_type);
    }

    /** @test */
    public function it_can_check_if_it_has_an_internal_link()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'link_type' => LinkType::INTERNAL,
            'link_entry' => "post:{$post->id}",
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinkType::INTERNAL,
            'link_entry' => "post:non-existing-id",
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
            'link_type' => LinkType::EXTERNAL,
            'link_entry' => null,
            'link_url' => 'http://localhost',
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinkType::EXTERNAL,
            'link_entry' => null,
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
            'link_type' => LinkType::INTERNAL,
            'link_entry' => "post:{$post->id}",
            'link_url' => null,
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinkType::INTERNAL,
            'link_entry' => "post:non-existing-id",
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
            'link_type' => LinkType::EXTERNAL,
            'link_entry' => null,
            'link_url' => 'http://localhost',
            'link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'link_type' => LinkType::EXTERNAL,
            'link_entry' => null,
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
            'link_type' => LinkType::EXTERNAL,
            'link_entry' => null,
            'link_url' => 'http://localhost',
            'link_label' => 'Link to hello world',
        ]);

        $this->assertTrue($menuItem->linkIsOfType(LinkType::EXTERNAL));
        $this->assertTrue($menuItem->linkIsOfType([LinkType::EXTERNAL, LinkType::INTERNAL]));
        $this->assertFalse($menuItem->linkIsOfType(LinkType::INTERNAL));
        $this->assertFalse($menuItem->linkIsOfType([LinkType::INTERNAL]));
    }
}
