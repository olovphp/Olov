<?php

require_once __DIR__.'/../src/Nano/Engine.php';

$vars = [
    'page' => [
        'title' => 'Welcome to Nano', 
        'body' => 'Nano is a micro template <b><u>engine</u></b> for PHP.',
        'devs' => [
            'Lanre Onabanjo <script>alert("I am malificient");</script>', 
            'Grace Huang', 
            'Ray Lin', 
            'Gboyega Dada'
            ]
    ]
];

$n = new Nano\Engine(__DIR__);

echo $n->render('hello-again.html.php', $vars);
