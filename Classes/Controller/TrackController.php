<?php

namespace CodingMs\ViewStatistics\Controller;

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

use CodingMs\ViewStatistics\Domain\Model\FrontendUser;
use CodingMs\ViewStatistics\Domain\Repository\FrontendUserRepository;
use CodingMs\ViewStatistics\Domain\Repository\TrackRepository;
use CodingMs\ViewStatistics\Service\ExportService;
use CodingMs\ViewStatistics\Utility\AuthorizationUtility;
use CodingMs\ViewStatistics\Utility\DataTransformer;
use DateTime;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use CodingMs\ViewStatistics\Service\ObjectService;
use CodingMs\ViewStatistics\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Validation\Error;

/**
 * TrackController
 */
class TrackController extends BackendActionController
{

    /**
     * @var TrackRepository
     */
    protected $trackRepository = null;

    /**
     * @param TrackRepository $trackRepository
     */
    public function injectTrackRepository(TrackRepository $trackRepository)
    {
        $this->trackRepository = $trackRepository;
    }

    /**
     * @var array
     */
    protected $filter;

    /**
     * @var ExportService
     */
    protected $exportService = null;

    /**
     * @param ExportService $exportService
     */
    public function injectExportService(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var int
     */
    protected $pageUid;

    /**
     * Admin is using the module
     * @var bool
     */
    protected $isAdmin = false;

    /**
     * Array with accessible pages for editor
     * @var array
     */
    protected $accessiblePages = [];

    /**
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        $this->pageUid = (int)GeneralUtility::_GP('id');
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
        if ($this->view->getModuleTemplate() !== null) {
            $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            //
            // Identify authorization data
            $this->isAdmin = AuthorizationUtility::backendLoginIsAdmin();
            $this->trackRepository->setIsAdmin($this->isAdmin);
            if(!$this->isAdmin) {
                // Accessible pages for user
                $accessiblePages = AuthorizationUtility::backendAccessiblePages();
                $this->accessiblePages = array_keys($accessiblePages);
                $this->trackRepository->setAccessiblePages($this->accessiblePages);
            }
        }
    }

    /**
     * Overview list.
     * This is the start up overview
     */
    public function listAction()
    {
        //
        // Get configuration
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['view_statistics']);
        //
        // Process filter
        $errors = $this->processFilter();
        $this->view->assign('filter', $this->filter);
        $this->view->assign('errors', $errors);

        if(!$this->isAdmin && $this->pageUid === 0) {
            $messageTitle = 'Notice';
            $messageBody = 'Please select a page for displaying tracking data!';
            $this->addFlashMessage($messageBody, $messageTitle, FlashMessage::INFO);
        }
        //
        // Export result as CSV
        if ($this->request->hasArgument('csv')) {
            $this->exportService->exportTracksAsCsv($this->filter, $this->settings);
        }
        //
        // Create buttons
        $this->createButtons();
        //
        // Show user columns
        $showUserColumn = false;
        if((bool)$configuration['track.']['trackLoggedInUserData']) {
            $showUserColumn = true;
        }
        $this->view->assign('showUserColumn', $showUserColumn);
        //
        // Show ip address column
        $showIpAddressColumn = false;
        if((bool)$configuration['track.']['trackIpAddress']) {
            $showIpAddressColumn = true;
        }
        $this->view->assign('showIpAddressColumn', $showIpAddressColumn);
        //
        // Get date for view
        $tracks = $this->trackRepository->findAllFiltered($this->filter);
        if ($this->request->hasArgument('search')) {
            if (count($tracks) === 0) {
                $messageTitle = 'Information';
                $messageBody = 'No tracking data found!';
                $this->addFlashMessage($messageBody, $messageTitle, FlashMessage::INFO);
            }
        }
        $this->view->assign('tracks', $tracks);
        $this->view->assign('typeOptions', $this->getTypeOptions());
        $this->view->assign('frontendUserOptions', $this->getFrontendUserOptions());
        // Date/time format
        $this->view->assign('dateFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']);
        $this->view->assign('timeFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']);
    }

    /**
     * List tracking data for a selected page
     */
    public function listForPageAction()
    {
        $pageUid = 0;
        /** @var PageRepository $repository */
        $repository = $this->objectManager->get(PageRepository::class);
        $search = $this->processSearch();
        $this->view->assign('search', $search);
        //
        // Page uid is passed by a link through other statistic views
        if ($this->request->hasArgument('uid')) {
            $pageUid = (int)$this->request->getArgument('uid');
        }
        //
        // Page uid is passed by search form
        if ($pageUid === 0 && (int)$search['uid'] > 0) {
            $pageUid = (int)$search['uid'];
        }
        //
        // Try to read page - is it accessible?
        if($pageUid > 0) {
            if(in_array($pageUid, $this->accessiblePages) || $this->isAdmin) {
                $page = $repository->findByUid($pageUid);
                //
                // Page found - read related tracking data
                if ($page) {
                    $this->view->assign('page', $page);
                    $type = 'pageview';
                    $tracks = $this->trackRepository->findByTypeAndUid($type, $pageUid);
                    $count = $this->trackRepository->countByTypeAndUid($type, $pageUid);
                    $this->view->assign('count', $count);
                    try {
                        $dataByDate = DataTransformer::transform($tracks, 'day');
                        $this->view->assign('dataByDate', $dataByDate);
                        $dataByFrontendUser = DataTransformer::transform($tracks, 'feuser');
                        $this->view->assign('dataByFrontendUser', $dataByFrontendUser);
                    } catch (Exception $e) {
                        $messageTitle = 'Error';
                        $messageBody = 'DataTransformer: ' . $e->getMessage();
                        $this->addFlashMessage($messageBody, $messageTitle, FlashMessage::ERROR);
                    }
                } else {
                    $this->view->assign('notFound', 1);
                }
            }
            else {
                $messageTitle = 'Error';
                $messageBody = 'Page authorization denied!';
                $this->addFlashMessage($messageBody, $messageTitle, FlashMessage::ERROR);
            }
        }
        else {
            // Search for pages
            if (isset($search['submit'])) {
                // ..but only accessible pages
                if(!$this->isAdmin) {
                    $items = $repository->searchByTitle($search['title'], $this->accessiblePages);
                }
                else{
                    $items = $repository->searchByTitle($search['title']);
                }
                if(count($items) === 0) {
                    $messageTitle = 'Information';
                    $messageBody = 'No pages found!';
                    $this->addFlashMessage($messageBody, $messageTitle, FlashMessage::INFO);
                }
                $this->view->assign('items', $items);
            }
        }
    }

    /**
     * List tracking data for a selected user
     */
    public function listForUserAction()
    {
        $userUid = 0;
        $frontendUser = null;
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $search = $this->processSearch();
        $this->createButtons();
        $this->view->assign('search', $search);
        //
        // User uid is passed by a link through other statistic views
        if ($this->request->hasArgument('uid')) {
            $userUid = (int)$this->request->getArgument('uid');
        }
        //
        // Page uid is passed by search form
        if ($userUid === 0 && (int)$search['uid'] > 0) {
            $userUid = (int)$search['uid'];
        }
        //
        // Try to read page - is it accessible?
        if($userUid > 0) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = $frontendUserRepository->findByUid($userUid);
            if ($frontendUser instanceof FrontendUser) {
                // Export result as CSV?
                if ($this->request->hasArgument('csv')) {
                    $this->exportService->exportTracksByFrontendUserAsCsv($userUid, $this->settings);
                }
                // Get tracking data by frontend user
                $tracks = $this->trackRepository->findByFrontendUser($frontendUser);
                $this->view->assign('tracks', $tracks);
                $this->view->assign('frontendUser', $frontendUser);
            }
            else {
                $this->view->assign('notFound', 1);
            }
        }
        else {
            // Search for user
            if (isset($search['submit'])) {
                $items = $frontendUserRepository->searchByNameOrEmail($search['name'], $search['email']);
                // Only list user that have already tracking data
                $itemsWithTrackingData = [];
                foreach($items as $item) {
                    $tracks = $this->trackRepository->findByFrontendUser($item);
                    if(count($tracks) > 0) {
                        $itemsWithTrackingData[] = $item;
                    }
                }
                if(count($itemsWithTrackingData) === 0) {
                    $messageTitle = 'Information';
                    $messageBody = 'No users found!';
                    $this->addFlashMessage($messageBody, $messageTitle, FlashMessage::INFO);
                }
                $this->view->assign('items', $itemsWithTrackingData);
            }
        }
    }

    /**
     * List tracking data for a selected object
     */
    public function listForObjectAction()
    {
        //
        // Validate type
        $type = 'sys_file';
        $this->view->assign('typeOptions', $this->getTypeOptions(true));
        if($this->request->hasArgument('type')) {
            $type = trim($this->request->getArgument('type'));
        }
        if(isset($this->settings['types'][$type])) {
            $settings = $this->settings['types'][$type];
        }
        else {
            throw new \Exception('Configuration for type ' . $type . ' not found');
        }
        $this->view->assign('type', $type);
        //
        // Get repository
        $repository = $this->objectManager->get($settings['repository']);
        //
        $search = $this->processSearch();
        $this->view->assign('search', $search);
        //
        $objectUid = 0;
        //
        // Object uid is passed by a link through other statistic views
        if ($this->request->hasArgument('uid')) {
            $objectUid = (int)$this->request->getArgument('uid');
        }
        //
        // Page uid is passed by search form
        if ($objectUid === 0 && (int)$search['uid'] > 0) {
            $objectUid = (int)$search['uid'];
        }
        //
        // Try to read page - is it accessible?
        if($objectUid > 0) {
            $object = $repository->findByUid($objectUid);
            if($object) {
                $this->view->assign('object', $object);
                //
                // Get label
                $label =ObjectService::getLabel($type, $objectUid, $settings['field']);
                $this->view->assign('label', $label);
                //
                // Get tracking data
                $tracks = $this->trackRepository->findByTypeAndUid($type, $objectUid);
                $count = $this->trackRepository->countByTypeAndUid($type, $objectUid);
                $this->view->assign('count', $count);
                try {
                    $dataByDate = DataTransformer::transform($tracks, 'day');
                    $this->view->assign('dataByDate', $dataByDate);
                    $dataByFrontendUser = DataTransformer::transform($tracks, 'feuser');
                    $this->view->assign('dataByFrontendUser', $dataByFrontendUser);
                } catch (Exception $e) {
                    $this->addFlashMessage('DataTransformer: ' . $e->getMessage(), 'Error', FlashMessage::ERROR);
                }

            }
            else {
                $this->view->assign('notFound', 1);
            }
        }
        else {
            if (isset($search['submit'])) {
                $items = ObjectService::getItems($type, $search['title'], $settings['field']);
                $this->view->assign('items', $items);
            }
        }
    }

    public function statisticAction()
    {
        $errors = $this->processFilter();
        $this->view->assign('filter', $this->filter);
        $this->view->assign('errors', $errors);

        $statisticConfig = $this->getStatisticConfig();
        $configOptions = [];
        foreach($statisticConfig as $key => $config) {
            $configOptions[$key] = $config['title'];
        }
        $this->view->assign('configOptions', $configOptions);

        if($this->request->hasArgument('submit')) {
            $currentConfig = $statisticConfig[$this->request->getArgument('config')];

            if($this->request->getArgument('config') === 'pages') {
                $items = $this->trackRepository->findTopPages($currentConfig['limit'],
                    $this->filter['mindate_ts'], $this->filter['maxdate_ts']);
                $this->view->assign('type', 'pages');
            } else {
                $items = $this->trackRepository->findTopNews($currentConfig['pid'], $currentConfig['limit'], $currentConfig['feuser'],
                    $this->filter['mindate_ts'], $this->filter['maxdate_ts']);
                $this->view->assign('type', 'news');
            }
            $this->view->assign('config', $currentConfig);
            $this->view->assign('items', $items);
        }
    }

    protected function getStatisticConfig()
    {
        $newsConfig = [
            'medizin' => [
                'title' => 'Artikel aus Bereich Medizin und Forschung',
                'pid' => 50,
                'limit' => '10',
                'feuser' => 1,
            ],
            'praxis' => [
                'title' => 'Artikel aus Bereich Praxis und Wirtschaft',
                'pid' => 70,
                'limit' => 10,
                'feuser' => -1
            ],
            'meinung' => [
                'title' => 'Artikel aus Bereich Meinung und Dialog',
                'pid' => 71,
                'limit' => 10,
                'feuser' => -1
            ],
            'verlag' => [
                'title' => 'Artikel aus Bereich Verlag',
                'pid' => 96,
                'limit' => 5,
                'feuser' => -1
            ],
            'pages' => [
                'title' => 'Seiten',
                'limit' => 10,
            ]
        ];
        return $newsConfig;
    }

    protected function getTypeOptions($withoutDefault=false)
    {
        // Default types
        $types = [];
        if(!$withoutDefault) {
            $types = [
                'pageview' => $this->translate('tx_viewstatistics_label.track_type_pageview'),
                'login' => $this->translate('tx_viewstatistics_label.track_type_login')
            ];
        }
        // Types from configuration
        if(count($this->settings['types'])>0) {
            foreach($this->settings['types'] as $typeKey=>$typeSettings) {
                if(ExtensionManagementUtility::isLoaded($typeSettings['extensionKey'])) {
                    $types[$typeKey] = $typeSettings['label'];
                }
            }
        }
        return $types;
    }

    /**
     * @return array
     */
    protected function getFrontendUserOptions()
    {
        return [
            'anonym' => $this->translate('tx_viewstatistics_label.frontend_user_type_anonym'),
            'logged_in' => $this->translate('tx_viewstatistics_label.frontend_user_type_logged_in')
        ];
    }

    protected function processFilter()
    {
        /** @todo restore filter */
        $this->filter = [];
        $errors = [];
        $datefields = ['mindate', 'maxdate'];
        foreach ($datefields as $field) {
            if ($this->request->hasArgument($field) && !empty($this->request->getArgument($field))) {
                $date = DateTime::createFromFormat('d-m-Y', $this->request->getArgument($field));
                if ($date === false) {
                    $errors[$field] = new Error('Invalid date', '1492512962');
                } else {
                    $this->filter[$field] = $this->sanitizeInput($this->request->getArgument($field));
                    if ($field == 'mindate') $date->setTime(0, 0, 0);
                    if ($field == 'maxdate') $date->setTime(23, 59, 59);
                    $this->filter[$field . '_ts'] = $date->getTimestamp();
                }
            }
        }
        $fields = ['type', 'frontendUser', 'config'];
        foreach ($fields as $field) {
            if ($this->request->hasArgument($field) && !empty($this->request->getArgument($field))) {
                $this->filter[$field] = $this->sanitizeInput($this->request->getArgument($field));
            }
        }
        // When there's no default type selected
        // Initialize the object node
        if(isset($this->filter['type'])) {
            if($this->filter['type']!='pageview' && $this->filter['type']!='login') {
                $this->filter['object'] = $this->filter['type'];
            }
        }
        $this->filter['pageUid'] = $this->pageUid;
    }

    /**
     * Returns all search parameters, contained in search array
     * @return array|string
     */
    protected function processSearch()
    {
        $searchArguments = [];
        if ($this->request->hasArgument('search')) {
            $searchArguments = $this->request->getArgument('search');
        }
        return $searchArguments;
    }

    /*
    * remove tags and trim user input
    */
    protected function sanitizeInput($value)
    {
        return trim(strip_tags($value));
    }

    /**
     * @param $key
     * @param array $arguments
     * @return null|string
     */
    protected function translate($key, $arguments=[]) {
        return LocalizationUtility::translate($key, 'ViewStatistics', $arguments);
    }

    /**
     * Add menu buttons for specific actions
     *
     * @return void
     */
    protected function createButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        $buttons = [];
        switch ($this->request->getControllerActionName()) {
            case 'list': {
                // Export as CSV
                $parameter = [
                    'id' => $this->pageUid,
                    'tx_viewstatistics_web_viewstatisticsviewstatistics' => [
                        'action' => 'list',
                        'controller' => 'Track',
                        'csv' => 1,
                    ]
                ];
                // Filter parameter
                $moduleKey = 'tx_viewstatistics_web_viewstatisticsviewstatistics';
                if(isset($this->filter['mindate'])) {
                    $parameter[$moduleKey]['mindate'] = $this->filter['mindate'];
                }
                if(isset($this->filter['maxdate'])) {
                    $parameter[$moduleKey]['maxdate'] = $this->filter['maxdate'];
                }
                if(isset($this->filter['type'])) {
                    $parameter[$moduleKey]['type'] = $this->filter['type'];
                }
                if(isset($this->filter['object'])) {
                    $parameter[$moduleKey]['object'] = $this->filter['object'];
                }
                if(isset($this->filter['frontendUser'])) {
                    $parameter[$moduleKey]['frontendUser'] = $this->filter['frontendUser'];
                }
                $title = LocalizationUtility::translate('tx_viewstatistics_label.csv_export',
                    $this->extensionName);
                $buttons[] = $buttonBar->makeLinkButton()
                    ->setHref(BackendUtility::getModuleUrl('web_ViewStatisticsViewstatistics', $parameter))
                    ->setTitle($title)
                    ->setIcon($this->iconFactory->getIcon('actions-document-export-csv', Icon::SIZE_SMALL));
                break;
            }
            case 'listForUser': {
                // Export as CSV
                if ($this->request->hasArgument('uid')) {
                    $uid = (int)$this->request->getArgument('uid');
                    $parameter = [
                        'id' => $this->pageUid,
                        'tx_viewstatistics_web_viewstatisticsviewstatistics' => [
                            'action' => 'listForUser',
                            'controller' => 'Track',
                            'csv' => 1,
                            'uid' => $uid
                        ]
                    ];
                    $title = LocalizationUtility::translate('tx_viewstatistics_label.csv_export',
                        $this->extensionName);
                    $buttons[] = $buttonBar->makeLinkButton()
                        ->setHref(BackendUtility::getModuleUrl('web_ViewStatisticsViewstatistics', $parameter))
                        ->setTitle($title)
                        ->setIcon($this->iconFactory->getIcon('actions-document-export-csv', Icon::SIZE_SMALL));
                }
                break;
            }
        }
        foreach ($buttons as $button) {
            $buttonBar->addButton($button, ButtonBar::BUTTON_POSITION_LEFT);
        }
    }

}
