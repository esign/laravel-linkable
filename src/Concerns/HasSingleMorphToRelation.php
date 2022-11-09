<?php

namespace Esign\Linkable\Concerns;

use Esign\Linkable\Relations\SingleColumnMorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSingleMorphToRelation
{
    public function singleColumnMorphTo(
        ?string $name = null,
        ?string $foreignKey = null,
        ?string $ownerKey = null
    ): SingleColumnMorphTo {
        $name = $name ?: $this->guessBelongsToRelation();
        $nameAsSnakeCase = Str::snake($name);
        $foreignKey = $foreignKey ?: "{$nameAsSnakeCase}_model";
        $morphType = SingleColumnMorphTo::getSingleColumnMorphingType($this, $foreignKey);

        return empty($morphType)
            ? $this->singleColumnMorphEagerTo($name, $foreignKey, $ownerKey)
            : $this->singleColumnMorphInstanceTo($morphType, $name, $foreignKey, $ownerKey);
    }

    protected function newSingleColumnMorphTo(
        Builder $query,
        Model $parent,
        string $foreignKey,
        ?string $ownerKey,
        string $relation
    ): SingleColumnMorphTo {
        return new SingleColumnMorphTo(
            query: $query,
            parent: $parent,
            foreignKey: $foreignKey,
            ownerKey: $ownerKey,
            relation: $relation,
        );
    }

    protected function singleColumnMorphEagerTo(
        string $name,
        string $id,
        ?string $ownerKey
    ): SingleColumnMorphTo {
        return $this->newSingleColumnMorphTo(
            query: $this->newQuery()->setEagerLoads([]),
            parent: $this,
            foreignKey: $id,
            ownerKey: $ownerKey,
            relation: $name
        );
    }

    protected function singleColumnMorphInstanceTo(
        string $target,
        string $name,
        string $id,
        ?string $ownerKey
    ): SingleColumnMorphTo {
        $instance = $this->newRelatedInstance(
            static::getActualClassNameForMorph($target)
        );

        return $this->newSingleColumnMorphTo(
            query: $instance->newQuery(),
            parent: $this,
            foreignKey: $id,
            ownerKey: $ownerKey ?? $instance->getKeyName(),
            relation: $name,
        );
    }
}
