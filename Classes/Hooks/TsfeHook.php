<?php

namespace CodingMs\ViewStatistics\Hooks;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use PDO;

/**
 * @package TYPO3
 * @subpackage view_statistics
 */
class TsfeHook
{

    /**
     *
     */
    public function checkDataSubmission()
    {
        //
        // Don't track anything, when you're a logged in backend user!
        if (isset($GLOBALS['BE_USER'])) {
            return;
        }
        //
        // Get configuration
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['view_statistics']);
        $trackUser = $extensionConfiguration['track.']['trackUser'];
        $trackLoggedInUserData = (bool)$extensionConfiguration['track.']['trackLoggedInUserData'];
        //
        // Get the current page
        $pageUid = (int)$GLOBALS['TSFE']->id;
        //
        // Identify logged in user
        $frontendUserUid = 0;
        $loginDuration = 0;
        if ($GLOBALS['TSFE']->loginUser) {
            $frontendUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
            // Login duration
            $loginDuration = time() - $GLOBALS['TSFE']->fe_user->user['lastlogin'];
            $this->updateLoginDuration($frontendUserUid, $loginDuration);

        }
        //
        // Collect tracking information
        $requestParams = serialize(['GET' => GeneralUtility::_GET(), 'POST' => GeneralUtility::_POST()]);
        $fields = [
            'frontend_user' => $frontendUserUid,
            'page' => $pageUid,
            'request_params' => $requestParams,
            'login_duration' => $loginDuration,
            'referrer' => GeneralUtility::getIndpEnv('HTTP_REFERER'),
            'request_uri' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            'user_agent' => GeneralUtility::getIndpEnv('HTTP_USER_AGENT'),
            'language' => $GLOBALS['TSFE']->sys_language_uid,
            /**
             * @todo start by page with is_root=1
             */
            'root_page' => $GLOBALS['TSFE']->rootLine[0]['uid']
        ];
        //
        // Track data from logged in user?
        // -> If not setted, unset data!
        if(!$trackLoggedInUserData) {
            $fields['frontend_user'] = 0;
            $fields['login_duration'] = 0;
        }
        //
        // Track logout
        //if (GeneralUtility::_GP('logintype') === 'logout') {
            // Track Logout
            // ..is not possible, because the frontend user is already unset
            // when we start tracking this!
            //$this->trackLogout($fields);
        //}
        //
        // Track logged in user only?!
        // -> Exit in case of no login available
        if ($trackUser === 'loggedInOnly' && $frontendUserUid === 0) {
            return;
        }
        //
        // Track only anonym user
        // -> Exit in case of login available
        if($trackUser === 'nonLoggedInOnly' && $frontendUserUid > 0) {
            return;
        }
        //
        // Track an object
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
        $configurationTypeSettings = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS;
        $configuration = $configurationManager->getConfiguration($configurationTypeSettings, 'ViewStatistics');
        if(is_array($configuration['objects'])) {
            foreach($configuration['objects'] as $arrayKey => $object) {
                $objectArray = GeneralUtility::_GP($arrayKey);
                if(is_array($objectArray)) {
                    foreach($configuration['objects'][$arrayKey] as $valueKey => $valueConfiguration) {
                        $objectUid = (int)$objectArray[$valueKey];
                        if($objectUid > 0) {
                            $fields['object_uid'] = $objectUid;
                            $fields['object_type'] = $valueConfiguration['table'];
                        }
                    }
                }
            }
        }
        //
        // Track user login/logout/page view
        if (GeneralUtility::_GP('logintype') === 'login') {
            // Track Login
            $this->trackLogin($fields, $extensionConfiguration);
        } else {
            // Track page view
            $this->trackPageview($fields, $extensionConfiguration);
        }
    }

    /**
     * @param int $frontendUser
     * @param int $loginDuration
     */
    protected function updateLoginDuration(int $frontendUser, int $loginDuration) {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->update('tx_viewstatistics_domain_model_track')
            ->where(
                $queryBuilder->expr()->eq(
                    'frontend_user',
                    $queryBuilder->createNamedParameter((int)$frontendUser, PDO::PARAM_INT)
                )
            )
            ->andWhere('action="login"')
            ->set('login_duration', (int)$loginDuration)
            ->orderBy('crdate')
            ->setMaxResults(1)
            ->execute();
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackLogin(array $fields, array$configuration)
    {
        $fields['action'] = 'login';
        if((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackPageview(array $fields, array $configuration)
    {
        $fields['action'] = 'pageview';
        if((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackLogout(array $fields, array $configuration)
    {
        $fields['action'] = 'logout';
        if((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    /**
     * @param array $fields
     */
    protected function insertRecord(array $fields) {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->insert('tx_viewstatistics_domain_model_track')
            ->values($fields)
            ->execute();
    }

}
