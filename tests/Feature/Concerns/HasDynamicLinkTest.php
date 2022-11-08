<?php

namespace Esign\Linkable\Tests\Feature\Concerns;

use Esign\Linkable\Concerns\HasDynamicLink;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class HasDynamicLinkTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_check_if_it_has_an_internal_link()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_model' => "post:{$post->id}",
            'dynamic_link_url' => null,
            'dynamic_link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_model' => "post:non-existing-id",
            'dynamic_link_url' => null,
            'dynamic_link_label' => 'Link to hello world',
        ]);

        $this->assertTrue($menuItemA->hasDynamicLink());
        $this->assertFalse($menuItemB->hasDynamicLink());
    }

    /** @test */
    public function it_can_check_if_it_has_an_external_link()
    {
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
            'dynamic_link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => null,
            'dynamic_link_label' => 'Link to hello world',
        ]);

        $this->assertTrue($menuItemA->hasDynamicLink());
        $this->assertFalse($menuItemB->hasDynamicLink());
    }

    /** @test */
    public function it_can_get_an_internal_link()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_model' => "post:{$post->id}",
            'dynamic_link_url' => null,
            'dynamic_link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_model' => "post:non-existing-id",
            'dynamic_link_url' => null,
            'dynamic_link_label' => 'Link to hello world',
        ]);

        $this->assertEquals("http://localhost/posts/{$post->id}", $menuItemA->dynamicLink());
        $this->assertNull($menuItemB->dynamicLink());
    }

    /** @test */
    public function it_can_get_an_external_url()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
            'dynamic_link_label' => 'Link to hello world',
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => null,
            'dynamic_link_label' => 'Link to hello world',
        ]);

        $this->assertEquals("http://localhost", $menuItemA->dynamicLink());
        $this->assertNull($menuItemB->dynamicLink());
    }

    /** @test */
    public function it_can_check_if_a_link_is_of_type()
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
            'dynamic_link_label' => 'Link to hello world',
        ]);

        $this->assertTrue($menuItem->dynamicLinkIsOfType(HasDynamicLink::$linkTypeExternal));
        $this->assertTrue($menuItem->dynamicLinkIsOfType([HasDynamicLink::$linkTypeExternal, HasDynamicLink::$linkTypeInternal]));
        $this->assertFalse($menuItem->dynamicLinkIsOfType(HasDynamicLink::$linkTypeInternal));
        $this->assertFalse($menuItem->dynamicLinkIsOfType([HasDynamicLink::$linkTypeInternal]));
    }

    /** @test */
    public function it_can_get_the_dynamic_link_type()
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
            'dynamic_link_label' => 'Hello World',
        ]);

        $this->assertEquals(HasDynamicLink::$linkTypeExternal, $menuItem->dynamicLinkType());
    }

    /** @test */
    public function it_can_get_the_dynamic_link_url()
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
            'dynamic_link_label' => 'Hello World',
        ]);

        $this->assertEquals('http://localhost', $menuItem->dynamicLinkUrl());
    }
}
