<?php

define ('ROOT_PATH', getcwd());

require_once ROOT_PATH.'/../src/Nano/Engine.php';

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
$n->setPath(ROOT_PATH);
echo $n->render('hello-again.html.php', $vars);
