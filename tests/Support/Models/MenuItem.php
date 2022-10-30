<?php

namespace Esign\Linkable\Tests\Support\Models;

use Esign\Linkable\Concerns\HasDynamicLinks;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasDynamicLinks;

    public $timestamps = false;
    protected $guarded = [];
}
