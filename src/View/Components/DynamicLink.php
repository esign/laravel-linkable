<?php

namespace Esign\Linkable\View\Components;

use Esign\Linkable\Concerns\HasDynamicLinks;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use InvalidArgumentException;

class DynamicLink extends Component
{
    public function __construct(public Model $model)
    {
        if (! in_array(HasDynamicLinks::class, class_uses_recursive($model))) {
            throw new InvalidArgumentException(sprintf(
                'The model `%s` does not use the `%s` trait',
                get_class($model),
                HasDynamicLinks::class,
            ));
        }
    }

    public function render(): ?View
    {
        return match ($this->model->link_type) {
            HasDynamicLinks::$linkTypeInternal => view('linkable::components.dynamic-link-internal'),
            HasDynamicLinks::$linkTypeExternal => view('linkable::components.dynamic-link-external'),
            default => null,
        };
    }
}
