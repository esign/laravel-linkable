<?php

namespace Esign\Linkable\Tests\Feature\Relations;

use Esign\Linkable\Concerns\LinksDynamically;
use Esign\Linkable\Tests\Support\Models\MenuItem;
use Esign\Linkable\Tests\Support\Models\Post;
use Esign\Linkable\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BelongsToLinkableTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_has_a_linkable_relationship()
    {
        $post = Post::create(['title' => 'Hello World']);
        $menuItem = MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeInternal,
            'link_entry' => "post:{$post->id}",
            'link_url' => null,
            'link_label' => 'Mijn eerste post',
        ]);

        $this->assertTrue($menuItem->linkable->is($post));
    }

    /** @test */
    public function it_can_eager_load_the_linkable_relationship()
    {
        $postA = Post::create(['title' => 'Hello World']);
        $postB = Post::create(['title' => 'Hello World 2']);
        MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeInternal,
            'link_entry' => "post:{$postA->id}",
            'link_url' => null,
            'link_label' => 'Mijn eerste post',
        ]);
        MenuItem::create([
            'link_type' => LinksDynamically::$linkTypeInternal,
            'link_entry' => "post:{$postB->id}",
            'link_url' => null,
            'link_label' => 'Mijn tweede post',
        ]);

        $menuItemLinkables = MenuItem::with('linkable')->get()->map->linkable;

        $this->assertTrue($menuItemLinkables->contains($postA));
        $this->assertTrue($menuItemLinkables->contains($postB));
    }
}
