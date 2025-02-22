<?php

namespace Esign\Linkable\Tests\Feature\Concerns;

use PHPUnit\Framework\Attributes\Test;
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
            $table->nullableMorphs('dynamic_link_linkable', 'linkable_index');
            $table->string('dynamic_link_url')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('model_with_regular_morph_to_relations');

        parent::tearDown();
    }

    #[Test]
    public function it_can_check_if_it_has_an_internal_link(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'dynamic_link_linkable_model' => "post:{$post->id}",
            'dynamic_link_url' => null,
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'dynamic_link_linkable_model' => "post:non-existing-id",
            'dynamic_link_url' => null,
        ]);

        $this->assertTrue($menuItemA->hasDynamicLink());
        $this->assertFalse($menuItemB->hasDynamicLink());
    }

    #[Test]
    public function it_can_check_if_it_has_an_external_link(): void
    {
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => null,
        ]);

        $this->assertTrue($menuItemA->hasDynamicLink());
        $this->assertFalse($menuItemB->hasDynamicLink());
    }

    #[Test]
    public function it_can_get_an_internal_link(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'dynamic_link_linkable_model' => "post:{$post->id}",
            'dynamic_link_url' => null,
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'dynamic_link_linkable_model' => "post:non-existing-id",
            'dynamic_link_url' => null,
        ]);

        $this->assertEquals("http://localhost/posts/{$post->id}", $menuItemA->dynamicLink());
        $this->assertNull($menuItemB->dynamicLink());
    }

    #[Test]
    public function it_can_get_null_as_a_link_when_the_link_type_isnt_internal_or_external(): void
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => null,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => null,
        ]);

        $this->assertNull($menuItem->dynamicLink());
    }

    #[Test]
    public function it_can_get_an_external_url(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItemA = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
        ]);
        $menuItemB = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => null,
        ]);

        $this->assertEquals("http://localhost", $menuItemA->dynamicLink());
        $this->assertNull($menuItemB->dynamicLink());
    }

    #[Test]
    public function it_can_check_if_a_link_is_of_type(): void
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
        ]);

        $this->assertTrue($menuItem->dynamicLinkIsOfType(HasDynamicLink::$linkTypeExternal));
        $this->assertTrue($menuItem->dynamicLinkIsOfType([HasDynamicLink::$linkTypeExternal, HasDynamicLink::$linkTypeInternal]));
        $this->assertFalse($menuItem->dynamicLinkIsOfType(HasDynamicLink::$linkTypeInternal));
        $this->assertFalse($menuItem->dynamicLinkIsOfType([HasDynamicLink::$linkTypeInternal]));
    }

    #[Test]
    public function it_can_get_the_dynamic_link_type(): void
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
        ]);

        $this->assertEquals(HasDynamicLink::$linkTypeExternal, $menuItem->dynamicLinkType());
    }

    #[Test]
    public function it_can_get_the_dynamic_link_url(): void
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => 'http://localhost',
        ]);

        $this->assertEquals('http://localhost', $menuItem->dynamicLinkUrl());
    }

    #[Test]
    public function it_can_use_a_regular_morph_to_relation(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = ModelWithRegularMorphToRelation::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'dynamic_link_linkable_type' => 'post',
            'dynamic_link_linkable_id' => $post->id,
            'dynamic_link_url' => null,
        ]);

        $this->assertTrue($menuItem->dynamicLinkLinkable->is($post));
    }
}
