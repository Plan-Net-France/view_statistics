<?php

namespace CodingMs\ViewStatistics\Service;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Object Service
 *
 */
class ObjectService
{

    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * Get a label for a tracked object
     * @param string $table
     * @param int $uid
     * @param string $field
     * @return string
     */
    public static function getLabel($table, $uid, $field) {
        if($table == 'sys_file') {
            $table = 'sys_file_metadata';
        }
        $label = $table . ':' . $uid . ':' . $field;
        if(isset(self::$cache[$table]) && isset(self::$cache[$table][$uid])) {
            $label = self::$cache[$table][$uid];
        }
        else {
            /** @todo:
             * hidden - fe_users.disable
             * deleted
             */
            /** @var  \TYPO3\CMS\Core\Database\DatabaseConnection $db */
            $db = $GLOBALS['TYPO3_DB'];
            $data = $db->exec_SELECTgetSingleRow('*', $table, 'uid=' . (int)$uid);
            if(is_array($data)) {
                // Append file description
                if($table === 'sys_file_metadata' && $field !== 'description') {
                    $description = trim(strip_tags($data['description']));
                    if($description !== '') {
                        $description = ' (' . $description . ')';
                    }
                    $title = $data[$field] . $description;
                    // If title empty, use file path and name
                    if(trim($title) === '') {
                        $identifier = $db->exec_SELECTgetSingleRow('identifier', 'sys_file', 'uid=' . (int)$uid);
                        $title = $identifier['identifier'];
                    }
                    self::$cache[$table][$uid] = $title;
                }
                else {
                    self::$cache[$table][$uid] = $data[$field];
                }
                $label = self::$cache[$table][$uid];
            }
        }
        return $label;
    }

    /**
     * @param $table
     * @param $search
     * @param $field
     * @return array
     */
    public static function getItems($table, $search, $field) {
        /** @var  \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $items = [];
        // Get objects by relation
        // So we get only records that have been already tracked!
        $objects = [];
        $where = 'object_type=\'' . $table . '\' AND object_uid>0';
        $trackTable = 'tx_viewstatistics_domain_model_track';
        $res = $db->exec_SELECTquery('object_uid', $trackTable, $where, 'object_uid,crdate', 'crdate DESC', '0, 5000');
        while (($row = $db->sql_fetch_assoc($res))) {
            $objects[] = $row['object_uid'];
        }
        $db->sql_free_result($res);
        //
        // Fix table name for sys_files
        if($table == 'sys_file') {
            $table = 'sys_file_metadata';
        }
        //
        // Fetch records, which were found in tracking data
        $where = '1=1';
        if(count($objects) > 0) {
            $where .= ' AND uid IN (' . implode(', ', $objects) . ')';
        }
        if(trim($search) != '') {
            // Search for title and description in files
            if($table === 'sys_file_metadata') {
                $where .= ' AND (' . $field . ' LIKE \'%' . $db->escapeStrForLike($search, $table) .  '%\'';
                $where .= ' OR description LIKE \'%' . $db->escapeStrForLike($search, $table) .  '%\')';
            }
            else {
                $where .= ' AND ' . $field . ' LIKE \'%' . $db->escapeStrForLike($search, $table) .  '%\'';
            }
        }
        $res = $db->exec_SELECTquery('*', $table, $where, '', 'crdate DESC', '0, 5000');
        while (($row = $db->sql_fetch_assoc($res))) {
            $item = [
                'uid' => $row['uid'],
                'title' => $row[$field],
                'creationDate' => $row['crdate']
            ];
            // Append file description
            if($table === 'sys_file_metadata') {
                $description = trim(strip_tags($row['description']));
                if($description !== '') {
                    $item['title'] .= ' (' . $description . ')';
                }
                // If title empty, use file path and name
                if(trim($item['title']) === '') {
                    $identifier = $db->exec_SELECTgetSingleRow('identifier', 'sys_file', 'uid=' . (int)$row['uid']);
                    $item['title'] = $identifier['identifier'];
                }
            }
            $items[] = $item;
        }
        $db->sql_free_result($res);
        return $items;
    }

}
