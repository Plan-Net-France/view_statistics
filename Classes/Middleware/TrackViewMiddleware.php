<?php
declare(strict_types=1);
/*
 * (c) 2020 Plan.Net France <typo3@plan-net.fr>
 */

namespace CodingMs\ViewStatistics\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * TrackViewMiddleware
 *
 * @author Pierrick Caillon <pierrick.caillon@plan-net.fr>
 */
class TrackViewMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->track($request);
        return $handler->handle($request);
    }

    protected function track(ServerRequestInterface $request): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        //
        // Don't track anything, when you're a logged in backend user!
        if ($context->getPropertyFromAspect('backend.user', 'isLoggedIn')) {
            return;
        }
        //
        // Get configuration
        $extensionConfiguration = $this->getExtensionConfiguration();
        $trackUser = $extensionConfiguration['track.']['trackUser'];
        $trackLoggedInUserData = (bool)$extensionConfiguration['track.']['trackLoggedInUserData'];
        $trackUserAgent = (bool)$extensionConfiguration['track.']['userAgent'];
        $trackLoginDuration = (bool)$extensionConfiguration['track.']['loginDuration'];
        //
        // Get the current page
        $pageUid = (int)$GLOBALS['TSFE']->id;
        //
        // Identify logged in user
        $frontendUserUid = 0;
        $loginDuration = 0;
        if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) {
            $frontendUserUid = $context->getPropertyFromAspect('frontend.user', 'id');
        }
        // Login duration
        if ($frontendUserUid && $trackLoginDuration) {
            $loginDuration = time() - GeneralUtility::makeInstance(ConnectionPool::class)
                                                    ->getConnectionForTable('fe_users')
                                                    ->select(['lastlogin'], 'fe_users', ['uid' => $frontendUserUid])
                                                    ->fetchOne();
            $this->updateLoginDuration($frontendUserUid, $loginDuration);
        }
        //
        // Collect tracking information
        $fields = [
            'frontend_user'  => ($trackLoggedInUserData) ? $frontendUserUid : 0,
            'page'           => $pageUid,
            'login_duration' => ($trackLoginDuration) ? $loginDuration : 0,
            'referrer'       => GeneralUtility::getIndpEnv('HTTP_REFERER'),
            'request_uri'    => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            'user_agent'     => ($trackUserAgent) ? GeneralUtility::getIndpEnv('HTTP_USER_AGENT') : '',
            'language'       => $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId(),
            /**
             * @todo start by page with is_root=1
             */
            'root_page'      => $request->getAttribute('site')->getRootpageId(),
        ];
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
        if ($trackUser === 'nonLoggedInOnly' && $frontendUserUid > 0) {
            return;
        }
        //
        // Track an object
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configurationTypeSettings = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS;
        $configuration = $configurationManager->getConfiguration($configurationTypeSettings, 'ViewStatistics');
        if (is_array($configuration['objects'])) {
            foreach ($configuration['objects'] as $arrayKey => $object) {
                $objectArray = GeneralUtility::_GP($arrayKey);
                if (is_array($objectArray)) {
                    foreach ($configuration['objects'][$arrayKey] as $valueKey => $valueConfiguration) {
                        $objectUid = (int)$objectArray[$valueKey];
                        if ($objectUid > 0) {
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

    public function getExtensionConfiguration(): array
    {
        // Get configuration
        $extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_statistics'];
        if (empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_statistics'])) {
            $extensionConfiguration['track.']['trackUser'] = 'all';
            $extensionConfiguration['track.']['trackLoggedInUserData'] = false;
            $extensionConfiguration['track.']['userAgent'] = false;
            $extensionConfiguration['track.']['loginDuration'] = false;
            $extensionConfiguration['track.']['trackIpAddress'] = false;
        }
        return $extensionConfiguration;
    }

    protected function updateLoginDuration(int $frontendUser, int $loginDuration): void
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->update('tx_viewstatistics_domain_model_track')
                     ->where(
                         $queryBuilder->expr()->eq(
                             'frontend_user',
                             $queryBuilder->createNamedParameter((int)$frontendUser, \PDO::PARAM_INT)
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
    protected function trackLogin(array $fields, array $configuration): void
    {
        $fields['action'] = 'login';
        if ((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    protected function trackPageview(array $fields, array $configuration): void
    {
        $fields['action'] = 'pageview';
        if ((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    protected function trackLogout(array $fields, array $configuration): void
    {
        $fields['action'] = 'logout';
        if ((bool)$configuration['track.']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    protected function insertRecord(array $fields): void
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->insert('tx_viewstatistics_domain_model_track')
                     ->values($fields)
                     ->execute();
    }
}
