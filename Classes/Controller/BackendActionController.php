<?php
namespace CodingMs\ViewStatistics\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Natalia Postnikova <natalia@postnikova.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TrackController
 */
class BackendActionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
        $this->generateMenu();
        $this->view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());
    }

    /**
     * Generates the action menu
     *
     * @return void
     */
    protected function generateMenu()
    {
        $menuItems = [
            'index' => [
                'controller' => 'Track',
                'action' => 'list',
                'label' => $this->translate('tx_viewstatistics_label.module_menu_list'),
            ],
            'user' => [
                'controller' => 'Track',
                'action' => 'listForUser',
                'label' => $this->translate('tx_viewstatistics_label.module_menu_list_for_user'),
            ],
            'page' => [
                'controller' => 'Track',
                'action' => 'listForPage',
                'label' => $this->translate('tx_viewstatistics_label.module_menu_list_for_page'),
            ],
            'object' => [
                'controller' => 'Track',
                'action' => 'listForObject',
                'label' => $this->translate('tx_viewstatistics_label.module_menu_list_for_object'),
            ],
            /*'statistic' => [
                'controller' => 'Track',
                'action' => 'statistic',
                'label' => 'Statistiken',
            ],*/
        ];

        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('BackendUserModuleMenu');

        foreach ($menuItems as  $menuItemConfig) {
            if ($this->request->getControllerName() === $menuItemConfig['controller']) {
                $isActive = $this->request->getControllerActionName() === $menuItemConfig['action'] ? true : false;
            } else {
                $isActive = false;
            }
            $menuItem = $menu->makeMenuItem()
                ->setTitle($menuItemConfig['label'])
                ->setHref($this->getHref($menuItemConfig['controller'], $menuItemConfig['action']))
                ->setActive($isActive);
            $menu->addMenuItem($menuItem);
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Creates te URI for a backend action
     *
     * @param string $controller
     * @param string $action
     * @param array $parameters
     * @return string
     */
    protected function getHref($controller, $action, $parameters = [])
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->reset()->uriFor($action, $parameters, $controller);
    }

    /**
     * @param $key
     * @param array $arguments
     * @return NULL|string
     */
    protected function translate($key, $arguments=[]) {
        return LocalizationUtility::translate($key, 'ViewStatistics', $arguments);
    }

}
