<?php

namespace Esign\Linkable;

use Esign\Linkable\View\Components\DynamicLink;
use Illuminate\Support\ServiceProvider;

class LinkableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom($this->viewPath(), 'linkable');
        $this->loadViewComponentsAs('linkable', [
            'dynamic-link' => DynamicLink::class,
        ]);
    }

    protected function viewPath(): string
    {
        return __DIR__ . '/../resources/views';
    }
}
