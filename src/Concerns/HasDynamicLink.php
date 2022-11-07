<?php

namespace Esign\Linkable\Concerns;

use Esign\Linkable\Contracts\LinkableUrlContract;
use Esign\Linkable\Relations\SingleColumnMorphTo;
use Illuminate\Support\Arr;

trait HasDynamicLink
{
    use HasSingleMorphToRelation;

    public static string $linkTypeInternal = 'internal';
    public static string $linkTypeExternal = 'external';

    public function linkable(): SingleColumnMorphTo
    {
        return $this->singleColumnMorphTo();
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
