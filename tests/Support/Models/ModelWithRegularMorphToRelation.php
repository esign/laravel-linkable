<?php

namespace Esign\Linkable\Tests\Support\Models;

use Esign\Linkable\Concerns\HasDynamicLink;
use Esign\Linkable\Relations\SingleColumnMorphTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelWithRegularMorphToRelation extends Model
{
    use HasDynamicLink;

    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'model_with_regular_morph_to_relations';

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
