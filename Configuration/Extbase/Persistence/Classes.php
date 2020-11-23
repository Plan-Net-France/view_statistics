<?php
declare(strict_types=1);
/*
 * (c) 2020 Plan.Net France <typo3@plan-net.fr>
 */
return [
    \CodingMs\ViewStatistics\Domain\Model\Page::class => [
        'tableName' => 'pages',
    ],
    \CodingMs\ViewStatistics\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ]
];
