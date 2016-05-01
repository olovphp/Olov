<?php

require_once __DIR__.'/../src/Nano/Engine.php';

use Cataleya\Apps\Nano;

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

$n = new Nano\Engine();
$n->setPath(__DIR__);
echo $n->render('base.html.php', $vars);
echo $n->render('hello-again.html.php', $vars);
