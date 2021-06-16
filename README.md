## Prototürk Template Engine

Prototürk'de sorulan bir soru üzerine videoda birlikte hazırladığımız php ile geliştirilmiş basit bir tema motoru.

### Geçerli direktifler

- `@if`, `@elseif` ve `@else`
- `@empty` ve `@endempty`
- `@isset` ve `@endisset`
- `@foreach` ve `@endforeach`
- `@forelse`, `@empty` ve `@endforelse`
- `@php` ve `@endphp`
- `@json`
- `@dump` ve `@dd`
- `@include`
- `@extends`, `@yield` ve `@section`

### Kullanımı

```php
<?php

require __DIR__ . '/PtEngine.php';

$pt = new PtEngine([
    'views' => __DIR__ . '/views',
    'cache' => __DIR__ . '/cache',
    'suffix' => 'blade'
]);

echo $pt->view('index', [
    'name' => 'Tayfun',
    'title' => 'Prototurk.com',
    'todos' => [
        'todo1',
        'todo2',
        'todo3',
        'todo4'
    ]
]); // views/index.blade.php dosyasını çağırır
```
Ayrıca özel direktiflerde tanımlayabilirsiniz, örneğin;

```php
$pt->directive('style', function($href = null) {
    if ($href) {
        return '<link rel="stylesheet" type="text/css" href="' . $href . '" />';
    }
    return '<style>';
});

$pt->directive('endstyle', function() {
    return '</style>';
});

$pt->directive('page', function($page) {
    return '<?php if (isset($_GET["page"]) && $_GET["page"] === \'' . $page . '\'): ?>';
});

$pt->directive('endpage', function() {
    return '<?php endif; ?>';
});
```
Daha fazla örnek kullanım için `views/` klasörüne gözatın.