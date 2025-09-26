<?php

namespace Esign\Linkable\View\Components;

use Esign\Linkable\Concerns\HasDynamicLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use InvalidArgumentException;

class DynamicLink extends Component
{
    public function __construct(public Model $model)
    {
        if (! in_array(HasDynamicLink::class, class_uses_recursive($model))) {
            throw new InvalidArgumentException(sprintf(
                'The model `%s` does not use the `%s` trait',
                get_class($model),
                HasDynamicLink::class,
            ));
        }
    }

    public function render()
    {
        return match ($this->model->dynamicLinkType()) {
            HasDynamicLink::$linkTypeInternal => view('linkable::components.dynamic-link-internal'),
            HasDynamicLink::$linkTypeExternal => view('linkable::components.dynamic-link-external'),
            default => null,
        };
    }
}
