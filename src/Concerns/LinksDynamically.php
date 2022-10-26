<?php

namespace Esign\Linkable\Concerns;

use Esign\Linkable\Contracts\LinkableUrlContract;
use Esign\Linkable\Enums\LinkType;
use Esign\Linkable\Relations\BelongsToLinkable;
use Illuminate\Support\Arr;

trait LinksDynamically
{
    protected function initializeLinksDynamically(): void
    {
        $this->mergeCasts(['link_type' => LinkType::class]);
    }

    public function linkable(): BelongsToLinkable
    {
        return new BelongsToLinkable((new static())->newQuery(), $this, 'link_entry', 'id', 'linkable');
    }

    public function link(): ?string
    {
        return match ($this->link_type) {
            LinkType::INTERNAL => $this->linkable instanceof LinkableUrlContract ? $this->linkable->linkableUrl() : null,
            LinkType::EXTERNAL => $this->link_url,
        };
    }

    public function hasLink(): bool
    {
        return ! empty($this->link());
    }

    public function linkIsOfType(LinkType | array $type): bool
    {
        return in_array($this->link_type, Arr::wrap($type));
    }
}
