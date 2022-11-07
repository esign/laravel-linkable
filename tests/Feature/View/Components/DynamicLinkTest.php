<?php

namespace Esign\Linkable\Tests\Feature\View\Components;

use Esign\Linkable\Concerns\HasDynamicLinks;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\ModelWithoutDynamicLinkTrait;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\View\ViewException;

class DynamicLinkTest extends TestCase
{
    use InteractsWithViews;

    /** @test */
    public function it_can_render_the_view_for_an_external_link()
    {
        $menuItem = MenuItem::create([
            'link_type' => HasDynamicLinks::$linkTypeExternal,
            'linkable_model' => null,
            'link_url' => 'https://www.esign.eu',
            'link_label' => 'Esign',
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

    /** @test */
    public function it_can_render_the_view_for_an_internal_link()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create([
            'link_type' => HasDynamicLinks::$linkTypeInternal,
            'linkable_model' => "post:{$post->id}",
            'link_url' => null,
            'link_label' => 'Esign',
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

    /** @test */
    public function it_can_throw_an_exception_when_given_a_model_that_does_not_implement_the_dynamic_link_trait()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage(sprintf(
            'The model `%s` does not use the `%s` trait',
            ModelWithoutDynamicLinkTrait::class,
            HasDynamicLinks::class,
        ));
        $model = new ModelWithoutDynamicLinkTrait();

        $this->blade(
            '<x-linkable-dynamic-link :model="$model">Esign</x-linkable-dynamic-link>',
            ['model' => $model]
        );
    }
}
