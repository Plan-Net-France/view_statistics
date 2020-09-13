<?php
namespace CodingMs\ViewStatistics\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

/**
 * Authorization Tools
 *
 * @package view_statistics
 * @subpackage Utility
 *
 */
class AuthorizationUtility
{

    /**
     * Checks if a backend user is an admin user
     * @return boolean
     */
    public static function backendLoginIsAdmin()
    {
        if (isset($GLOBALS['BE_USER'])) {
            if (isset($GLOBALS['BE_USER']->user)) {
                return (bool)$GLOBALS['BE_USER']->user['admin'];
            }
        }
        return false;
    }

    /**
     * Checks if a backend user is logged in
     * @return boolean
     */
    public static function backendLoginExists()
    {
        if (isset($GLOBALS['BE_USER'])) {
            if (isset($GLOBALS['BE_USER']->user)) {
                return (bool)$GLOBALS['BE_USER']->user['uid'];
            }
        }
        return false;
    }

    /**
     * Returns accessible pages for current backend user
     *
     * @param string $fields
     * @return array
     */
    public static function backendAccessiblePages($fields='uid')
    {
        if (isset($GLOBALS['BE_USER'])) {
            if (isset($GLOBALS['BE_USER']->user)) {
                /** @var ConnectionPool $connectionPool */
                $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
                $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');
                $queryBuilder->select($fields)->from('pages');
                $queryBuilder->where($GLOBALS['BE_USER']->getPagePermsClause(1));
                $queryBuilder->orderBy('sorting');
                return $queryBuilder->execute()->fetchAll();
            }
        }
        return [];
    }

}
