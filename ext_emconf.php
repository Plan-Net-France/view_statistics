<?php

$EM_CONF['view_statistics'] = [
    'title' => 'View frontend statistics',
    'description' => 'Logs frontend actions and display them in a backend module. Track page views, News, Downloads and custom objects. Optionally track frontend user and login durations.',
    'category' => 'module',
    'version' => '1.0.3',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Natalia Postnikova, Thomas Deuling',
    'author_email' => 'natalia@postnikova.de, typo3@coding.ms',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
