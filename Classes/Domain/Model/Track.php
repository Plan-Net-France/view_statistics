<?php
namespace CodingMs\ViewStatistics\Domain\Model;

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

use GeorgRinger\News\Domain\Model\News;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Track
 */
class Track extends AbstractEntity
{

    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var \CodingMs\ViewStatistics\Domain\Model\FrontendUser
     */
    protected $frontendUser;

    /**
     * @var int
     */
    protected $loginDuration = 0;

    /**
     * @var \CodingMs\ViewStatistics\Domain\Model\Page
     */
    protected $page;

    /**
     * @var \CodingMs\ViewStatistics\Domain\Model\Page
     */
    protected $rootPage;

    /**
     * @var int
     */
    protected $language;

    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @var string
     */
    protected $requestParams;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * @var string
     */
    protected $referrer;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var int
     */
    protected $objectUid = 0;

    /**
     * @var string
     */
    protected $objectType = '';

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return \CodingMs\ViewStatistics\Domain\Model\FrontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * @param \CodingMs\ViewStatistics\Domain\Model\FrontendUser $frontendUser
     */
    public function setFrontendUser($frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * @return int
     */
    public function getLoginDuration()
    {
        return $this->loginDuration;
    }

    /**
     * @param int $loginDuration
     */
    public function setLoginDuration($loginDuration)
    {
        $this->loginDuration = $loginDuration;
    }

    /**
     * @return \CodingMs\ViewStatistics\Domain\Model\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param \CodingMs\ViewStatistics\Domain\Model\Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return Page
     */
    public function getRootPage()
    {
        return $this->rootPage;
    }

    /**
     * @param Page $rootPage
     */
    public function setRootPage($rootPage)
    {
        $this->rootPage = $rootPage;
    }

    /**
     * @return int
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param int $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return string
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * @param string $requestParams
     */
    public function setRequestParams($requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * @param string $requestUri
     */
    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;
    }

    /**
     * @return string
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @param string $referrer
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return int
     */
    public function getObjectUid()
    {
        return $this->objectUid;
    }

    /**
     * @param int $objectUid
     */
    public function setObjectUid($objectUid)
    {
        $this->objectUid = $objectUid;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        $object = null;
        $objectUid = $this->getObjectUid();
        $objectType = $this->getObjectType();
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        if($objectType == 'file') {
            /** @var \TYPO3\CMS\Core\Resource\FileRepository $repository */
            $repository = $objectManager->get(FileRepository::class);
            /** @var News $object */
            $object = $repository->findByIdentifier($objectUid);
        }
        if($objectType === 'tx_news_domain_model_news') {
            /** @var \TYPO3\CMS\Core\Resource\FileRepository $repository */
            $repository = $objectManager->get(\GeorgRinger\News\Domain\Repository\NewsDefaultRepository::class);
            /** @var File $object */
            $object = $repository->findByIdentifier($objectUid);
        }

        /**
         * @todo refactor
         */

        return $object;
    }

}
