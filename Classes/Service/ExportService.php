<?php

namespace CodingMs\ViewStatistics\Service;

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

use CodingMs\ViewStatistics\Utility\AuthorizationUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use PDO;

/**
 * Page Service
 *
 */
class ExportService
{

    /**
     * @var string
     */
    protected $dateFormat = 'd.m.Y H:i';

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var PageService
     */
    protected $pageService;

    /**
     * @param PageService $pageService
     */
    public function injectPageService(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * Statistic by Frontend-User
     * Respects editor authorizations.
     *
     * @param array $filter
     * @param array $settings
     * @return void
     */
    public function exportTracksAsCsv(array $filter = [], array $settings = [])
    {
        // Filename
        $filename = 'ViewStatistics_Tracks';
        $filename = date('Y-m-d_H-i-s') . '_' . $filename . '.csv';
        // Output file
        $output = fopen('php://output', 'w');
        // Header cells
        $headerCells = [
            'crdate' => 'Creation-Date',
            'action' => 'Action',
            'frontend_user' => 'Frontend-User',
            'login_duration' => 'Login-Duration',
            'page' => 'Page',
            'object' => 'Object',
            'ip_address' => 'IP-Address',
            'user_agent' => 'User-Agent',
            'request_params' => 'Request-Parameter',
            'request_uri' => 'Request-URI',
            'referrer' => 'Referrer',
        ];
        fputcsv($output, $headerCells);
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->select('*')->from('tx_viewstatistics_domain_model_track');
        // Don't export deleted records
        $queryBuilder->where(
            $queryBuilder->expr()->eq('deleted', '0')
        );
        // Time range
        if (isset($filter['mindate_ts']) && isset($filter['maxdate_ts'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->gte(
                    'crdate',
                    $queryBuilder->createNamedParameter((int)$filter['mindate_ts'], PDO::PARAM_INT)
                )
            );
            $queryBuilder->andWhere(
                $queryBuilder->expr()->lte(
                    'crdate',
                    $queryBuilder->createNamedParameter((int)$filter['maxdate_ts'], PDO::PARAM_INT)
                )
            );
        }
        // Filter by type or object
        if (isset($filter['type'])) {
            if ($filter['type'] === 'pageview') {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq(
                        'action',
                        $queryBuilder->createNamedParameter('pageview', PDO::PARAM_STR)
                    )
                );
            } else if ($filter['type'] === 'login') {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq(
                        'action',
                        $queryBuilder->createNamedParameter('login', PDO::PARAM_STR)
                    )
                );
            } else {
                // Filter by object type
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq(
                        'object_type',
                        $queryBuilder->createNamedParameter($filter['type'], PDO::PARAM_STR)
                    )
                );
            }
        }
        // Filter by frontend user
        if (isset($filter['frontendUser'])) {
            if ($filter['frontendUser'] === 'anonym') {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq('frontend_user', '0')
                );
            } else if ($filter['frontendUser'] === 'logged_in') {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->neq('frontend_user', '0')
                );
            }
        }
        // Editor page restriction
        if (!AuthorizationUtility::backendLoginIsAdmin()) {
            $accessiblePages = AuthorizationUtility::backendAccessiblePages();
            $accessiblePages = array_keys($accessiblePages);
            // And only data from current page
            if (in_array((int)$filter['pageUid'], $accessiblePages)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq('page', (int)$filter['pageUid'])
                );
            } else {
                // If page denied, don't load anything!
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq('page', '0')
                );
            }
        }
        $result = $queryBuilder->execute();
        while ($row = $result->fetch()) {
            $actionTranslationKey = 'tx_viewstatistics_label.track_type_' . $row['action'];
            $actionTranslation = LocalizationUtility::translate($actionTranslationKey, 'ViewStatistics');
            $csv = [
                'crdate' => date($this->dateFormat, $row['crdate']),
                'action' => $actionTranslation,
                'frontend_user' => $this->getDataField('fe_users', (int)$row['frontend_user'], 'username') . '[' . $row['frontend_user'] . ']',
                'login_duration' => $this->formatLoginDuration($row['login_duration']),
                'page' => $this->getRootline((int)$row['page']),
                'object' => $this->getObjectCell($row['object_type'], (int)$row['object_uid'], $settings),
                'ip_address' => $row['ip_address'],
                'user_agent' => $row['user_agent'],
                'request_parameter' => $row['request_parameter'],
                'request_uri' => $row['request_uri'],
                'referrer' => $row['referrer'],
            ];
            fputcsv($output, $csv);
        }
        // Create HTTP-Header
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        exit;
    }

    /**
     * Statistic by Frontend-User.
     * Respects editor authorizations.
     *
     * @param int $frontendUser
     * @param array $settings
     * @return void
     */
    public function exportTracksByFrontendUserAsCsv($frontendUser = 0, array $settings=[])
    {
        // Filename
        $filename = 'ViewStatistics_FrontendUser_' . $frontendUser;
        $filename = date('Y-m-d_H-i-s') . '_' . $filename . '.csv';
        // Output file
        $output = fopen('php://output', 'w');
        // Header cells
        $headerCells = [
            'crdate' => 'Creation-Date',
            'action' => 'Action',
            'frontend_user' => 'Frontend-User',
            'login_duration' => 'Login-Duration',
            'page' => 'Page',
            'object' => 'Object',
            'ip_address' => 'IP-Address',
            'user_agent' => 'User-Agent',
            'request_params' => 'Request-Parameter',
            'request_uri' => 'Request-URI',
            'referrer' => 'Referrer',
        ];
        fputcsv($output, $headerCells);
        // Get data
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->select('*')->from('tx_viewstatistics_domain_model_track');
        // Don't export deleted records
        $queryBuilder->where(
            $queryBuilder->expr()->eq('deleted', '0')
        );
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq('frontend_user', (int)$frontendUser)
        );
        // Editor page restriction
        if (!AuthorizationUtility::backendLoginIsAdmin()) {
            $accessiblePages = AuthorizationUtility::backendAccessiblePages();
            $accessiblePages = array_keys($accessiblePages);
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in('page', $accessiblePages)
            );
        }
        $result = $queryBuilder->execute();
        while ($row = $result->fetch()) {
            $actionTranslationKey = 'tx_viewstatistics_label.track_type_' . $row['action'];
            $actionTranslation = LocalizationUtility::translate($actionTranslationKey, 'ViewStatistics');
            $csv = [
                'crdate' => date($this->dateFormat, $row['crdate']),
                'action' => $actionTranslation,
                'frontend_user' => $this->getDataField('fe_users', (int)$row['frontend_user'], 'username') . '[' . $row['frontend_user'] . ']',
                'login_duration' => $this->formatLoginDuration($row['login_duration']),
                'page' => $this->getRootline((int)$row['page']),
                'object' => $this->getObjectCell($row['object_type'], (int)$row['object_uid'], $settings),
                'ip_address' => $row['ip_address'],
                'user_agent' => $row['user_agent'],
                'request_parameter' => $row['request_parameter'],
                'request_uri' => $row['request_uri'],
                'referrer' => $row['referrer'],
            ];
            fputcsv($output, $csv);
        }
        // Create HTTP-Header
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        exit;
    }

    /**
     * Returns a data field from database table
     *
     * @param string $table
     * @param int $uid
     * @param string $field
     * @return string
     */
    protected function getDataField(string $table, int $uid, string $field): string
    {
        $return = '';
        $data = $this->getDataRow($table, $uid);
        if (isset($data[$field])) {
            $return = $data[$field];
        }
        return $return;
    }

    /**
     * Returns a single row from database
     *
     * @param string $table
     * @param int $uid
     * @return array|null
     */
    protected function getDataRow(string $table, int $uid): ?array
    {
        if (isset($this->cache[$table]) && isset($this->cache[$table][$uid])) {
            $data = $this->cache[$table][$uid];
        } else {
            /**
             * @todo: what should happen, if Frontend-user is:
             * hidden - fe_users.disable
             * deleted
             */
            $data = BackendUtility::getRecord($table, (int)$uid);
            $this->cache[$table][$uid] = $data;
        }
        return $data;
    }

    /**
     * Format duration strings
     *
     * @param int $loginDuration
     * @return string
     */
    protected function formatLoginDuration(int $loginDuration = 0): string
    {
        $hours = floor($loginDuration / 3600);
        $minutes = floor($loginDuration / 60 % 60);
        $seconds = floor($loginDuration % 60);
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Get a rootline by page uid
     *
     * @param int $uid
     * @return string
     */
    protected function getRootline(int $uid): string
    {
        if (isset($this->cache['rootline']) && isset($this->cache['rootline'][$uid])) {
            $rootline = $this->cache['rootline'][$uid];
        } else {
            $rootlineArray = [];
            $rootlinePages = $this->pageService->getRootLine($uid, true);
            foreach ($rootlinePages as $rootlinePage) {
                $rootlineArray[] = $rootlinePage['title'] . '[' . $rootlinePage['uid'] . ']';
            }
            $rootline = implode(' > ', $rootlineArray);
            $this->cache['rootline'][$uid] = $rootline;
        }
        return $rootline;
    }

    /**
     * @param string $table
     * @param int $uid
     * @param array $settings
     * @return string
     */
    protected function getObjectCell(string $table, int $uid, array $settings = []): string
    {
        $label = '';
        if ($uid > 0) {
            $label = $table . ':' . $uid;
            if ($table === 'sys_file' || $table === 'sys_file_metadata') {
                $label = $this->getDataField('sys_file_metadata', $uid, 'title');
                if ($label === '') {
                    $label = $this->getDataField('sys_file', $uid, 'identifier');
                }
                $label .= ' [sys_file:' . $uid . ']';
            } else if (isset($settings['types'][$table]['field'])) {
                $label = $this->getDataField($table, $uid, $settings['types'][$table]['field']) . ' [' . $table . ':' . $uid . ']';
            }
        }
        return $label;
    }

}
