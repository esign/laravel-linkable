<?php

namespace Esign\Linkable\Concerns;

use Esign\Linkable\Contracts\LinkableUrlContract;
use Esign\Linkable\Relations\SingleColumnMorphTo;
use Illuminate\Support\Arr;

trait LinksDynamically
{
    public static string $linkTypeInternal = 'internal';
    public static string $linkTypeExternal = 'external';

    public function linkable(): SingleColumnMorphTo
    {
        $morphType = SingleColumnMorphTo::getSingleColumnMorphingType($this, 'linkable_model');

        $query = $morphType
            ? $this->newRelatedInstance(static::getActualClassNameForMorph($morphType))->newQuery()
            : $this->newQuery()->setEagerLoads([]);

        return new SingleColumnMorphTo(
            query: $query,
            parent: $this,
            foreignKey: 'linkable_model',
            ownerKey: 'id',
            relation: 'linkable'
        );
    }

    public function link(): ?string
    {
        return match ($this->link_type) {
            static::$linkTypeInternal => $this->linkable instanceof LinkableUrlContract ? $this->linkable->linkableUrl() : null,
            static::$linkTypeExternal => $this->link_url,
        };
    }

    public function hasLink(): bool
    {
        return ! empty($this->link());
    }

    public function linkIsOfType(string | array $type): bool
    {
        return in_array($this->link_type, Arr::wrap($type));
    }
}
