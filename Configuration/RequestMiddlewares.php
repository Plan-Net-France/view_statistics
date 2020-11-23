<?php
/*
 * (c) 2020 Plan.Net France <typo3@plan-net.fr>
 */
return [
    'frontend' => [
        'view-statistics/track-view' => [
            'target' => \CodingMs\ViewStatistics\Middleware\TrackViewMiddleware::class,
            'after'  => ['typo3/cms-frontend/output-compression'],
        ],
    ],
];
