<?php

namespace CodingMs\ViewStatistics\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
        //if (GeneralUtility::_GP('logintype') == 'logout') {
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
        if (GeneralUtility::_GP('logintype') == 'login') {
            // Track Login
            $this->trackLogin($fields, $extensionConfiguration);
        } else {
            // Track page view
            $this->trackPageview($fields, $extensionConfiguration);
        }
    }

    /**
     * @param $frontendUser
     * @param $loginDuration
     */
    protected function updateLoginDuration($frontendUser, $loginDuration) {
        $table = 'tx_viewstatistics_domain_model_track';
        $field = [
            'login_duration' => (int)$loginDuration
        ];
        $where = 'frontend_user = ' . (int)$frontendUser . ' AND action=\'login\' ';
        $where .= 'ORDER BY crdate DESC LIMIT 1';
        $this->getDb()->exec_UPDATEquery($table, $where, $field);
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackLogin($fields, $configuration)
    {
        $table = 'tx_viewstatistics_domain_model_track';
        $fields['action'] = 'login';
        if((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->getDb()->exec_INSERTquery($table, $fields);
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackPageview($fields, $configuration)
    {
        $table = 'tx_viewstatistics_domain_model_track';
        $fields['action'] = 'pageview';
        if((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->getDb()->exec_INSERTquery($table, $fields);
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackLogout($fields, $configuration)
    {
        $table = 'tx_viewstatistics_domain_model_track';
        $fields['action'] = 'logout';
        if((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->getDb()->exec_INSERTquery($table, $fields);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDb()
    {
        return $GLOBALS['TYPO3_DB'];
    }

}
