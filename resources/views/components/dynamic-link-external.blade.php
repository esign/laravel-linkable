<a {{ $attributes->merge([
    'href' => $model->dynamicLink(),
    'target' => '_blank',
    'rel' => 'noopener'
]) }}>{{ $slot }}</a>