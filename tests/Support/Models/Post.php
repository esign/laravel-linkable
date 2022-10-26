<?php

namespace Esign\Linkable\Tests\Support\Models;

use Esign\Linkable\Contracts\LinkableUrlContract;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements LinkableUrlContract
{
    public $timestamps = false;
    protected $guarded = [];

    public function linkableUrl(): ?string
    {
        return url("posts/{$this->id}");
    }
}
