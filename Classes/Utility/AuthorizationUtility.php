<?php
namespace CodingMs\ViewStatistics\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Thomas Deuling <typo3@coding.ms>, coding.ms
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Authorization Tools
 *
 * @package guidelines
 * @subpackage Utility
 *
 * ChangeLog Version: 1.1.0
 * *    [FEATURE] Adding backendAccessiblePages method
 *
 * ChangeLog Version: 1.0.1
 * *    [BUGFIX] Fix backendLoginIsAdmin method
 *
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
     * @return array
     */
    public static function backendAccessiblePages($fields='uid')
    {
        if (isset($GLOBALS['BE_USER'])) {
            if (isset($GLOBALS['BE_USER']->user)) {
                $permClause = $GLOBALS['BE_USER']->getPagePermsClause(1);
                return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields, 'pages', $permClause, '', 'sorting', '', 'uid');
            }
        }
        return [];
    }

    /**
     * Frontend user login
     * @param $username \string Frontend user username
     * @param $password \string Frontend user password
     * @return boolean
     */
    public static function frontendUserLogin($username, $password)
    {
        $check = false;
        $loginData = array(
            'username' => $username,
            'uident_text' => $password,
            'status' => 'login',
        );
        $GLOBALS['TSFE']->fe_user->checkPid = ''; //do not use a particular pid
        $info = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
        $user = $GLOBALS['TSFE']->fe_user->fetchUserRecord($info['db_user'], $loginData['username']);
        if ($GLOBALS['TSFE']->fe_user->compareUident($user, $loginData)) {
            $GLOBALS["TSFE"]->fe_user->createUserSession($user);
            $check = true;
        }
        return $check;
    }

    /**
     * Frontend user logout
     */
    public static function frontendUserLogout()
    {
        $GLOBALS["TSFE"]->fe_user->logoff();
    }
}
