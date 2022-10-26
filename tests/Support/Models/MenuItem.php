<?php

namespace Esign\Linkable\Tests\Support\Models;

use Esign\Linkable\Concerns\LinksDynamically;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use LinksDynamically;

    public $timestamps = false;
    protected $guarded = [];
}
