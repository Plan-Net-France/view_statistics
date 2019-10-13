<?php
namespace CodingMs\ViewStatistics\Domain\Repository;


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

use \TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * The repository for Track
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
{

    public function initializeObject()
    {
        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
        $querySettings->setRespectStoragePage(FALSE);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @param $name
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function searchByName($name)
    {
        $query = $this->createQuery();
        $where = $query->logicalOr(
            [
                $query->like('first_name', '%'.$name.'%'),
                $query->like('last_name', '%'.$name.'%'),
                $query->like('username', '%'.$name.'%')
            ]
        );
        $query->matching($where);
        $query->setOrderings(array('uid' => QueryInterface::ORDER_ASCENDING));
        $result = $query->execute();
        return $result;
    }

    /**
     * @param $email
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function searchByEmail($email)
    {
        $query = $this->createQuery();
        $query->matching($query->like('email', '%'.$email.'%'));
        $query->setOrderings(array('uid' => QueryInterface::ORDER_ASCENDING));
        $result = $query->execute();
        return $result;
    }

    /**
     * @param string $name Name or username
     * @param string $email E-Mail
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function searchByNameOrEmail($name = '', $email = '') {
        $query = $this->createQuery();
        $whereParts = [];
        if(!empty($name)) {
            $whereParts[] = $query->logicalOr(
                [
                    $query->like('first_name', '%'.$name.'%'),
                    $query->like('last_name', '%'.$name.'%'),
                    $query->like('username', '%'.$name.'%')
                ]
            );
        }
        if(!empty($email)) {
            $whereParts[] = $query->logicalOr(
                [
                    $query->like('email', '%'.$email.'%'),
                    $query->like('username', '%'.$email.'%')
                ]
            );
        }
        if(count($whereParts) > 0) {
            $where = $query->logicalAnd($whereParts);
        }
        if(isset($where)) {
            $query->matching($where);
        }
        $query->setOrderings(array('uid' => QueryInterface::ORDER_ASCENDING));
        $result = $query->execute();
        return $result;
    }

}
