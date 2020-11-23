<?php

namespace CodingMs\ViewStatistics\Domain\Repository;

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
use TYPO3\CMS\Core\DataHandling\TableColumnSubType;
use TYPO3\CMS\Core\DataHandling\TableColumnType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * The repository for Track
 */
class TrackRepository extends Repository
{

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
     * @param $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    /**
     * @param array $accessiblePages
     */
    public function setAccessiblePages(array $accessiblePages = [])
    {
        $this->accessiblePages = $accessiblePages;
    }

    public function initializeObject()
    {
        /** @var $querySettings Typo3QuerySettings */
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
        $this->registerInternalTimeFields();
    }

    /** @var DataMapFactory */
    protected $dataMapFactory;

    protected function registerInternalTimeFields()
    {
        $dataMap = $this->dataMapFactory->buildDataMap($this->objectType);
        foreach (['crdate'] as $fieldName) {
            if ($dataMap->getColumnMap($fieldName) !== null) {
                continue;
            }
            $columnMap = GeneralUtility::makeInstance(ColumnMap::class, $fieldName, $fieldName);
            $columnMap->setType(TableColumnType::cast('input'));
            $columnMap->setInternalType(TableColumnSubType::cast(null));
            $columnMap->setTypeOfRelation(ColumnMap::RELATION_NONE);
            $dataMap->addColumnMap($columnMap);
        }
    }

    public function injectDataMapFactory(DataMapFactory $dataMapFactory): void
    {
        $this->dataMapFactory = $dataMapFactory;
    }

    /**
     * Get all tracking data.
     * Respects editor authorizations.
     *
     * @param string $sortingField
     * @param string $sortingOrder
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAll($sortingField = 'crdate', $sortingOrder = QueryInterface::ORDER_DESCENDING, $dateFrom = null, $dateTo = null)
    {
        $query = $this->createQuery();
        $orderings = [
            $sortingField => $sortingOrder,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        // Non admin must have access to related page!
        if (!$this->isAdmin) {
            $query->matching($query->in('page', $this->accessiblePages));
        }
        $query->setOrderings($orderings);
        return $query->execute();
    }

    /**
     * Counts all tracking data by type and page uid.
     * Respects editor authorizations.
     *
     * @param string $type What kind of tracking data should be found
     * @param int $pageUid
     * @return int
     * @throws InvalidQueryException
     */
    public function countByTypeAndUid($type, $pageUid)
    {
        $query = $this->createQuery();
        $orderings = [
            'crdate' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        //
        $whereParts = [];
        // Page uid
        // if($pageUid > 0) {
        //     $whereParts[] = $query->equals('page', $pageUid);
        // }
        // Type
        switch ($type) {
            case 'pageview':
                $whereParts[] = $query->equals('action', 'pageview');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            case 'login':
                $whereParts[] = $query->equals('action', 'login');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            default:
                // Filter by object type
                $whereParts[] = $query->equals('object_type', $type);
                $whereParts[] = $query->equals('object_uid', $pageUid);
                break;
        }
        // Non admin must have access to related page!
        if (!$this->isAdmin) {
            $whereParts[] = $query->in('page', $this->accessiblePages);
        }
        //
        if (count($whereParts) === 1) {
            $query->matching($whereParts[0]);
        } else {
            $query->matching($query->logicalAnd($whereParts));
        }
        $query->setOrderings($orderings);
        return $query->execute()->count();
    }

    /**
     * Get all tracking data by type and page uid.
     * Respects editor authorizations.
     *
     * @param string $type What kind of tracking data should be found
     * @param int $pageUid
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByTypeAndUid($type, $pageUid)
    {
        $query = $this->createQuery();
        $orderings = [
            'crdate' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        //
        $whereParts = [];
        // Page uid
        //if($pageUid > 0) {
        // $whereParts[] = $query->equals('page', $pageUid);
        //}
        // Type
        switch ($type) {
            case 'pageview':
                $whereParts[] = $query->equals('action', 'pageview');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            case 'login':
                $whereParts[] = $query->equals('action', 'login');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            default:
                // Filter by object type
                $whereParts[] = $query->equals('object_type', $type);
                $whereParts[] = $query->equals('object_uid', $pageUid);
                break;
        }
        // Non admin must have access to related page!
        if (!$this->isAdmin) {
            $whereParts[] = $query->in('page', $this->accessiblePages);
        }
        //
        if (count($whereParts) === 1) {
            $query->matching($whereParts[0]);
        } else {
            $query->matching($query->logicalAnd($whereParts));
        }
        $query->setOrderings($orderings);
        $query->setLimit(50000);
        $result = $query->execute(true);
        return $result;
    }

    /**
     * Get all tracking data by frontend user
     * Respects editor authorizations.
     *
     * @param FrontendUser $frontendUser
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByFrontendUser($frontendUser)
    {
        $query = $this->createQuery();
        $ordering = [
            'crdate' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        //
        $whereParts = [];
        $whereParts[] = $query->equals('frontend_user', $frontendUser->getUid());
        // Non admin must have access to related page!
        if (!$this->isAdmin) {
            $whereParts[] = $query->in('page', $this->accessiblePages);
        }
        //
        if (count($whereParts) === 1) {
            $query->matching($whereParts[0]);
        } else {
            $query->matching($query->logicalAnd($whereParts));
        }
        $query->setOrderings($ordering);
        $result = $query->execute(false);
        return $result;
    }

    /**
     * Get all tracking data for main overview.
     * Respects editor authorizations.
     *
     * @param array $filter
     * @param string $sortingField
     * @param QueryInterface $sortingOrder
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllFiltered($filter, $sortingField = 'crdate', $sortingOrder = QueryInterface::ORDER_DESCENDING)
    {
        $query = $this->createQuery();
        $whereParts = [];
        // Date from
        if (isset($filter['mindate_ts'])) {
            $whereParts[] = $query->greaterThanOrEqual('crdate', $filter['mindate_ts']);
        }
        // Date to
        if (isset($filter['maxdate_ts'])) {
            $whereParts[] = $query->lessThanOrEqual('crdate', $filter['maxdate_ts']);
        }
        // Type
        if (isset($filter['type'])) {
            switch ($filter['type']) {
                case 'pageview':
                    $whereParts[] = $query->equals('action', 'pageview');
                    break;
                case 'login':
                    $whereParts[] = $query->equals('action', 'login');
                    break;
                default:
                    // Filter by object type
                    $whereParts[] = $query->equals('object_type', $filter['type']);
                    break;
            }
        }
        // Frontend user
        if (isset($filter['frontendUser'])) {
            switch ($filter['frontendUser']) {
                case 'anonym':
                    $whereParts[] = $query->equals('frontend_user', 0);
                    break;
                case 'logged_in':
                    $whereParts[] = $query->greaterThan('frontend_user', 0);
                    break;
            }
        }
        // User is admin?
        // Non admins are not permitted to read all pages, they must have access to related page!
        if (!$this->isAdmin) {
            $whereParts[] = $query->equals('page', $filter['pageUid']);
            $whereParts[] = $query->in('page', $this->accessiblePages);
        }
        // Are there some where-parts?
        if (count($whereParts) > 0) {
            // ..we need a logical AND for matching
            $where = $query->logicalAnd($whereParts);
            $query->matching($where);
        }
        $orderings = [
            $sortingField => $sortingOrder,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        $query->setOrderings($orderings);
        $result = $query->execute();
        return $result;
    }

}
