<?php

require_once __DIR__.'/../src/Nano/Engine.php';

$vars = [
    'page' => [
        'title' => 'Welcome to Nano', 
        'body' => 'Nano is a micro template <b><u>engine</u></b> for PHP.',
        'devs' => [
            [
                'name'=>'Lanre Onabanjo <script>alert("I am malificient");</script>', 
                'a:href'=>'mailto:lbanjo@gmail.com'
            ], 
            [
                'name'=>'Grace Huang', 
                'a:href'=>'mailto:grace@aol.com'
            ], 
            [
                'name'=>'Ray Lin',
            ], 
            [
                'name'=>'Gboyega Dada',
                'a:href'=>'mailto:boyega@gmail.com'
            ]
        ]

    ]
];

$n = new Nano\Engine(__DIR__);

echo $n->render('hello-again.html.php', $vars);
