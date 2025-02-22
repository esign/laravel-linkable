<?php

namespace Esign\Linkable\Tests\Feature\View\Components;

use PHPUnit\Framework\Attributes\Test;
use Esign\Linkable\Concerns\HasDynamicLink;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\ModelWithoutDynamicLinkTrait;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\View\ViewException;

class DynamicLinkTest extends TestCase
{
    use InteractsWithViews;

    #[Test]
    public function it_can_render_the_view_for_an_external_link(): void
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeExternal,
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => 'https://www.esign.eu',
        ]);

        $component = $this->blade(
            '<x-linkable-dynamic-link :model="$menuItem">Esign</x-linkable-dynamic-link>',
            ['menuItem' => $menuItem]
        );

        $component->assertSee(
            '<a href="https://www.esign.eu" target="_blank" rel="noopener">Esign</a>',
            false
        );
    }

    #[Test]
    public function it_can_render_the_view_for_an_internal_link(): void
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create([
            'dynamic_link_type' => HasDynamicLink::$linkTypeInternal,
            'dynamic_link_linkable_model' => "post:{$post->id}",
            'dynamic_link_url' => null,
        ]);

        $component = $this->blade(
            '<x-linkable-dynamic-link :model="$model">Esign</x-linkable-dynamic-link>',
            ['model' => $menuItem]
        );

        $component->assertSee(
            "<a href=\"http://localhost/posts/{$post->id}\">Esign</a>",
            false
        );
    }

    #[Test]
    public function it_can_render_null_for_a_non_existing_dynamic_link_type(): void
    {
        $menuItem = MenuItem::create([
            'dynamic_link_type' => 'non-existing-link-type',
            'dynamic_link_linkable_model' => null,
            'dynamic_link_url' => null,
        ]);

        $component = $this->blade(
            '<x-linkable-dynamic-link :model="$model">Esign</x-linkable-dynamic-link>',
            ['model' => $menuItem]
        );

        $component->assertSee(null);
    }

    #[Test]
    public function it_can_throw_an_exception_when_given_a_model_that_does_not_implement_the_dynamic_link_trait(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage(sprintf(
            'The model `%s` does not use the `%s` trait',
            ModelWithoutDynamicLinkTrait::class,
            HasDynamicLink::class,
        ));
        $model = new ModelWithoutDynamicLinkTrait();

        $this->blade(
            '<x-linkable-dynamic-link :model="$model">Esign</x-linkable-dynamic-link>',
            ['model' => $model]
        );
    }
}
