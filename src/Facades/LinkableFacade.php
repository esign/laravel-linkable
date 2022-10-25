<?php

namespace Esign\Linkable\Facades;

use Illuminate\Support\Facades\Facade;

class LinkableFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'linkable';
    }
}
