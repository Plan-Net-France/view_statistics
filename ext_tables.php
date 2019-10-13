<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'CodingMs.ViewStatistics',
        'web',
        'viewstatistics',
        '',
        array(
            'Track' => 'list,listForUser,listForPage,listForObject,statistic',
        ),
        array(
            'access' => 'user,group',
            'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module-viewstatistics.svg',
            'labels' => 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf',
        )
    );
}
