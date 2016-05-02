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
                'a:href'=>'mailto:grace@aol.com', 
                'input:type'=>'radio', 
                'input:name'=>'dev', 
                'input:value'=>'Grace Hunag' 

            ], 
            [
                'name'=>'Ray Lin',
                'input:type'=>'radio', 
                'input:name'=>'dev', 
                'input:value'=>'Ray Lin' 

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
