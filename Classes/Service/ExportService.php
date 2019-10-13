<?php

namespace CodingMs\ViewStatistics\Service;

use CodingMs\ViewStatistics\Utility\AuthorizationUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $db;

    /**
     * @var \CodingMs\ViewStatistics\Service\PageService
     * @inject
     */
    protected $pageService;

    /**
     * Statistic by Frontend-User
     * Respects editor authorizations.
     *
     * @param array $filter
     * @param array $settings
     * @return void
     */
    public function exportTracksAsCsv($filter=array(), $settings) {
        $this->db = $GLOBALS['TYPO3_DB'];
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
        ];
        fputcsv($output, $headerCells);
        // Get data
        $table = 'tx_viewstatistics_domain_model_track';
        // Date/time range
        $where = '';
        if(isset($filter['mindate_ts']) && isset($filter['maxdate_ts'])) {
            $where .= 'crdate >=' . (int)$filter['mindate_ts'] . ' AND crdate<=' . (int)$filter['maxdate_ts'] . ' AND ';
        }
        // Filter by type or object
        if (isset($filter['type'])) {
            if($filter['type']=='pageview') {
                $where .= 'action=\'pageview\' AND ';
            }
            else if($filter['type']=='login') {
                $where .= 'action=\'login\' AND ';
            }
            else {
                // Filter by object type
                $where .= 'object_type=\'' . $filter['type'] . '\' AND ';
            }
        }
        // Filter by frontend user
        if (isset($filter['frontendUser'])) {
            if ($filter['frontendUser'] == 'anonym') {
                $where .= 'frontend_user=0 AND ';
            } else if ($filter['frontendUser'] == 'logged_in') {
                $where .= 'frontend_user>0 AND ';
            }
        }
        // Don't export deleted records
        $where .= 'deleted=0 AND ';
        // Editor page restriction
        if(!AuthorizationUtility::backendLoginIsAdmin()) {
            $accessiblePages = AuthorizationUtility::backendAccessiblePages();
            $accessiblePages = array_keys($accessiblePages);
            // And only data from current page
            if(in_array((int)$filter['pageUid'], $accessiblePages)) {
                $where .= 'page = ' . (int)$filter['pageUid'] . ' AND ';
            }
            else {
                // If page denied, don't load anything!
                $where .= '1 = 0 AND ';
            }
        }
        $where .= '1 = 1';
        //
        $res = $this->db->exec_SELECTquery('*', $table, $where, '', 'crdate DESC', '0, 5000');
        while (($row = $this->db->sql_fetch_assoc($res))) {
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
            ];
            fputcsv($output, $csv);
        }
        $this->db->sql_free_result($res);
        // Create HTTP-Header
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        exit;
    }

    /**
     * Statistic by Frontend-User.
     * Respects editor authorizations.
     *
     * @param integer $frontendUser
     * @param array $settings
     * @return void
     */
    public function exportTracksByFrontendUserAsCsv($frontendUser=0, $settings) {
        $this->db = $GLOBALS['TYPO3_DB'];
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
        ];
        fputcsv($output, $headerCells);
        // Get data
        $table = 'tx_viewstatistics_domain_model_track';
        $where = 'frontend_user=' . (int)$frontendUser . ' AND deleted=0';
        // Editor page restriction
        if(!AuthorizationUtility::backendLoginIsAdmin()) {
            $accessiblePages = AuthorizationUtility::backendAccessiblePages();
            $accessiblePages = array_keys($accessiblePages);
            $where .= ' AND page IN(' . implode(', ', $accessiblePages) . ')';
        }
        //
        $res = $this->db->exec_SELECTquery('*', $table, $where, '', 'crdate DESC', '0, 5000');
        while (($row = $this->db->sql_fetch_assoc($res))) {
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
            ];
            fputcsv($output, $csv);
        }
        $this->db->sql_free_result($res);
        // Create HTTP-Header
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        exit;
    }

    /**
     * Returns a data field from database table
     *
     * @param $table
     * @param $uid
     * @param $field
     * @return string
     */
    protected function getDataField($table, $uid, $field) {
        $return = '';
        $data = $this->getDataRow($table, $uid);
        if(isset($data[$field])) {
            $return = $data[$field];
        }
        return $return;
    }

    /**
     * Returns a single row from database
     *
     * @param $table
     * @param $uid
     * @return mixed
     */
    protected function getDataRow($table, $uid) {
        if(isset($this->cache[$table]) && isset($this->cache[$table][$uid])) {
            $data = $this->cache[$table][$uid];
        }
        else {
            /**
             * @todo: what should happen, if Frontend-user is:
             * hidden - fe_users.disable
             * deleted
             */
            $data = $this->db->exec_SELECTgetSingleRow('*', $table, 'uid=' . (int)$uid);
            $this->cache[$table][$uid] = $data;
        }
        return $data;
    }

    /**
     * Format duration strings
     *
     * @param null $loginDuration
     * @return string
     */
    protected function formatLoginDuration($loginDuration = null)
    {
        $hours = floor($loginDuration / 3600);
        $minutes = floor($loginDuration / 60 % 60);
        $seconds = floor($loginDuration % 60);
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Get a rootline by page uid
     *
     * @param $uid
     * @return string
     */
    protected function getRootline($uid) {
        if(isset($this->cache['rootline']) && isset($this->cache['rootline'][$uid])) {
            $rootline = $this->cache['rootline'][$uid];
        }
        else {
            $rootline = '';
            $rootlinePages = $this->pageService->getRootLine($uid);
            foreach($rootlinePages as $rootlinePage) {
                if($rootline != '') {
                    $rootline .= ' > ';
                }
                $rootline .= $rootlinePage['title'] . '[' . $rootlinePage['uid'] . ']';
            }
            $this->cache['rootline'][$uid] = $rootline;
        }
        return $rootline;
    }

    /**
     * @param string $table
     * @param int $uid
     * @return string
     */
    protected function getObjectCell($table, $uid, $settings) {
        $label = '';
        if($uid > 0) {
            $label = $table . ':' . $uid;
            if($table == 'sys_file' || $table == 'sys_file_metadata') {
                $label = $this->getDataField('sys_file_metadata', $uid, 'title');
                if($label === '') {
                    $label = $this->getDataField('sys_file', $uid, 'identifier');
                }
                $label .= ' [sys_file:' . $uid . ']';
            }
            else if(isset($settings['types'][$table]['field'])) {
                $label = $this->getDataField($table, $uid, $settings['types'][$table]['field']) . ' [' . $table . ':' . $uid . ']';
            }
        }
        return $label;
    }

}
