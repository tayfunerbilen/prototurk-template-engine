<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require __DIR__ . '/PtEngine.php';

$pt = new PtEngine([
    'views' => __DIR__ . '/views',
    'cache' => __DIR__ . '/cache',
    'suffix' => 'blade'
]);

$pt->directive('style', function($href = null) {
    if ($href) {
        return '<link rel="stylesheet" type="text/css" href="' . $href . '" />';
    }
    return '<style>';
});

$pt->directive('endstyle', function() {
    return '</style>';
});

if (!isset($_GET['page'])) {
    echo $pt->view('index', [
        'name' => 'Tayfun',
        'title' => 'Prototurk.com',
        'todos' => [
            'todo1',
            'todo2',
            'todo3',
            'todo4'
        ]
    ]);
} else if ($_GET['page'] == 'contact') {
    echo $pt->view('contact', [
        'title' => 'İletişim | prototurk'
    ]);
}