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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use PDO;

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
        if($table === 'sys_file') {
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
            $data = BackendUtility::getRecord($table, (int)$uid);
            if(is_array($data)) {
                // Append file description
                if($table === 'sys_file_metadata' && $field !== 'description') {
                    $description = trim(strip_tags($data['description']));
                    if($description !== '') {
                        $description = ' (' . $description . ')';
                    }
                    $title = $data[$field] . $description;
                    // If title empty,use file path and name
                    if(trim($title) === '') {
                        $identifier = BackendUtility::getRecord('sys_file', (int)$uid);
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
        $items = [];
        // Get objects by relation
        // So we get only records that have been already tracked!
        $objects = [];
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->select('*')->from('tx_viewstatistics_domain_model_track');
        $queryBuilder->where(
            $queryBuilder->expr()->gt('object_uid', '0')
        );
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                'object_type',
                $queryBuilder->createNamedParameter($table, PDO::PARAM_STR)
            )
        );
        $queryBuilder->setMaxResults(5000);
        $queryBuilder->orderBy('crdate', 'DESC');
        $queryBuilder->groupBy('object_uid', 'crdate');
        $result = $queryBuilder->execute();
        while ($row = $result->fetch()) {
            $objects[$row['object_uid']] = $row['object_uid'];
        }
        //
        // Fix table name for sys_files
        if($table === 'sys_file') {
            $table = 'sys_file_metadata';
        }
        //
        // Fetch records, which were found in tracking data
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->select('*')->from($table);
        $queryBuilder->where(
            $queryBuilder->expr()->gt('uid', '0')
        );
        if(count($objects) > 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in('uid', $objects)
            );
        }
        if(trim($search) != '') {
            // Search for title and description in files
            if($table === 'sys_file_metadata') {
                $orWhere = $queryBuilder->orWhere(
                    $queryBuilder->expr()->like(
                        $field,
                        $queryBuilder->createNamedParameter('%' . $search . '%')
                    ),
                    $queryBuilder->expr()->like(
                        'description',
                        $queryBuilder->createNamedParameter('%' . $search . '%')
                    )
                );
                $queryBuilder->andWhere($orWhere);
            }
            else {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->like(
                        $field,
                        $queryBuilder->createNamedParameter('%' . $search . '%')
                    )
                );
            }
        }
        $queryBuilder->setMaxResults(5000);
        $queryBuilder->orderBy('crdate', 'DESC');
        $result = $queryBuilder->execute();
        while ($row = $result->fetch()) {
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
                // If title empty,use file path and name
                if(trim($item['title']) === '') {
                    $identifier = BackendUtility::getRecord('sys_file', (int)$row['uid']);
                    $item['title'] = $identifier['identifier'];
                }
            }
            $items[] = $item;
        }
        return $items;
    }

}
