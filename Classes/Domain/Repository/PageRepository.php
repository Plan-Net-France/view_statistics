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
use \TYPO3\CMS\Extbase\Persistence\Repository;

class PageRepository extends Repository
{

    public function initializeObject()
    {
        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
        $querySettings->setRespectStoragePage(FALSE);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @param $title
     * @param $accessiblePages array Array with accessible page uids
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function searchByTitle($title, $accessiblePages=[])
    {
        $query = $this->createQuery();
        // If accessible pages is empty, all pages will be search
        if(count($accessiblePages) == 0) {
            $where = $query->like('title', '%'.$title.'%');
        }
        else {
            $whereParts = [
                $query->like('title', '%'.$title.'%'),
                $query->in('uid', $accessiblePages)
            ];
            $where = $query->logicalAnd($whereParts);
        }
        $query->matching($where);
        $query->setOrderings(array('uid' => QueryInterface::ORDER_ASCENDING));
        $result = $query->execute();
        return $result;
    }

}
