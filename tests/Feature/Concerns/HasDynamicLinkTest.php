<?php

namespace Esign\Linkable\Tests\Feature\Concerns;

use Esign\Linkable\Concerns\HasDynamicLink;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\ModelWithRegularMorphToRelation;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;

class HasDynamicLinkTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('model_with_regular_morph_to_relations', function (Blueprint $table) {
            $table->id();
            $table->string('dynamic_link_type')->nullable();
            $table->nullableMorphs('linkable', 'linkable_index');
            $table->string('dynamic_link_url')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('model_with_regular_morph_to_relations');

        parent::tearDown();
    }

    /** @test */
    public function it_can_check_if_it_has_an_internal_link()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_model' => "post:{$post->id}",
            'dynamic_link_url' => null,
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_model' => "post:non-existing-id",
            'dynamic_link_url' => null,
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
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => null,
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
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_model' => "post:non-existing-id",
            'dynamic_link_url' => null,
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
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'linkable_model' => null,
            'dynamic_link_url' => null,
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
        ]);

        $this->assertEquals('http://localhost', $menuItem->dynamicLinkUrl());
    }

    /** @test */
    public function it_can_use_a_regular_morph_to_relation()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = ModelWithRegularMorphToRelation::create([ 
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'linkable_type' => 'post',
            'linkable_id' => $post->id,
            'dynamic_link_url' => null,
        ]);

        $this->assertEquals('http://localhost', $menuItem->linkable->is($post));
    }
}
