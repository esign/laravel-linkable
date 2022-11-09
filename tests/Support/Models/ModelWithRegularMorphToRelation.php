<?php

namespace Esign\Linkable\Tests\Support\Models;

use Esign\Linkable\Concerns\HasDynamicLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelWithRegularMorphToRelation extends Model
{
    use HasDynamicLink;

    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'model_with_regular_morph_to_relations';

    public function dynamicLinkLinkable(): MorphTo
    {
        return $this->morphTo();
    }
}
