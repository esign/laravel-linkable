<?php

namespace Esign\Linkable\Tests\Support\Models;

use Esign\Linkable\Concerns\HasDynamicLink;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasDynamicLink;

    public $timestamps = false;
    protected $guarded = [];
}
