<?php

namespace Esign\Linkable;

use Illuminate\Support\ServiceProvider;

class LinkableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('linkable.php')], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'linkable');

        $this->app->singleton('linkable', function () {
            return new Linkable;
        });
    }

    protected function configPath(): string
    {
        return __DIR__ . '/../config/linkable.php';
    }
}
