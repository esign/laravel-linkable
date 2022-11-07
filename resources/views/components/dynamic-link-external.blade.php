<a {{ $attributes->merge([
    'href' => $model->link(),
    'target' => '_blank',
    'rel' => 'noopener'
]) }}>{{ $slot }}</a>