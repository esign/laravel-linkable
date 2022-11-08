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

    public function dynamicLink(): ?string
    {
        return match ($this->dynamicLinkType()) {
            static::$linkTypeInternal => $this->linkable instanceof LinkableUrlContract ? $this->linkable->linkableUrl() : null,
            static::$linkTypeExternal => $this->dynamicLinkUrl(),
            default => null,
        };
    }

    public function hasDynamicLink(): bool
    {
        return ! empty($this->dynamicLink());
    }

    public function dynamicLinkIsOfType(string | array $type): bool
    {
        return in_array($this->dynamicLinkType(), Arr::wrap($type));
    }

    public function dynamicLinkType(): ?string
    {
        return $this->dynamic_link_type;
    }

    public function dynamicLinkUrl(): ?string
    {
        return $this->dynamic_link_url;
    }
}
