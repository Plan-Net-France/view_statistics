<?php
return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track',
        'label' => 'action',
        'label_alt' => 'frontend_user',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'hideTable' => 1,
        'delete' => 'deleted',
        'enablecolumns' => [
        ],
        'searchFields' => 'action, frontend_user, page, ip_address,',
        'iconfile' => 'EXT:view_statistics/Resources/Public/Icons/iconmonstr-chart-18.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'action, frontend_user, login_duration, page, root_page, language, ip_address, request_params, request_uri, referrer, user_agent, object_uid, object_type',
    ],
    'types' => [
        '1' => ['showitem' => '
            action,
            --palette--;;frontend_user_login_duration,
            --palette--;;page_language,
            root_page,
            ip_address, request_params,
            request_uri,
            referrer,
            user_agent,
            --palette--;;object_uid_object_type
        '],
    ],
    'palettes' => [
        'frontend_user_login_duration' => ['showitem' => 'frontend_user, login_duration', 'canNotCollapse' => 1],
        'page_language' => ['showitem' => 'page, language', 'canNotCollapse' => 1],
        'object_uid_object_type' => ['showitem' => 'object_uid, object_type', 'canNotCollapse' => 1],
    ],
    'columns' => [
        'action' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.action',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    /** @todo translations */
                    ['Login', 'login'],
                    ['Seitenaufruf', 'pageview'],
                    ['Logout', 'logout']
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => '',
                'readOnly' => 1,
            ],
        ],
        'frontend_user' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.frontend_user',
            'config' => [
                'type' => 'group',
                'foreign_table' => 'fe_users',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 1,
                'maxitems' => 1,
                'items' => [
                    ['', '0'],
                ],
                'readOnly' => 1,
            ],
        ],
        'login_duration' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.login_duration',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'page' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'foreign_table' => 'pages',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'items' => [
                    ['', '0'],
                ],
                'readOnly' => 1,
            ],
        ],
        'root_page' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.root_page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'foreign_table' => 'pages',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'items' => [
                    ['', '0'],
                ],
                'readOnly' => 1,
            ],
        ],
        'language' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.language',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'foreign_table' => 'sys_language',
                'allowed' => 'sys_language',
                'size' => 1,
                'maxitems' => 1,
                'items' => [
                    ['', '0'],
                ],
                'readOnly' => 1,
            ],
        ],
        'ip_address' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.ip_address',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'request_params' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.request_params',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '3',
                'readOnly' => 1,
            ],
        ],
        'request_uri' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.request_uri',
            'config' => [
                'type' => 'input',
                //'size' => 15,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'referrer' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.referrer',
            'config' => [
                'type' => 'input',
                //'size' => 15,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'user_agent' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.user_agent',
            'config' => [
                'type' => 'input',
                //'size' => 15,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'object_uid' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.object_uid',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'object_type' => [
            'exclude' => false,
            'label'	=> 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf:tx_viewstatistics_domain_model_track.object_type',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
    ],
];
