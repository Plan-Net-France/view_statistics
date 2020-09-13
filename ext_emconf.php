<?php

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 Thomas Deuling <typo3@coding.ms>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

$EM_CONF['view_statistics'] = [
    'title' => 'View frontend statistics',
    'description' => 'Logs frontend actions and display them in a backend module. Track page views, News, Downloads and custom objects. Optionally track frontend user and login durations. Alternative extension for Google-Analytics, Matomo, Piwik - this extension doesntuse any cookies!',
    'category' => 'module',
    'version' => '2.0.0',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Natalia Postnikova, Thomas Deuling',
    'author_email' => 'natalia@postnikova.de, typo3@coding.ms',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
