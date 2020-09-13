<?php

namespace CodingMs\ViewStatistics\Utility;

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

use CodingMs\ViewStatistics\Domain\Repository\FrontendUserRepository;
use CodingMs\ViewStatistics\Domain\Model\FrontendUser;
use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @package TYPO3
 * @subpackage view_statistics
 */
class DataTransformer
{

    /**
     * @param array $tracks
     * @param string $type
     * @return mixed
     * @throws Exception
     */
    public static function transform($tracks, $type)
    {
        $functionName = 'transform' . ucfirst($type);
        if (method_exists(__CLASS__, $functionName)) {
            return self::$functionName($tracks);
        }
        throw new Exception('Funktion ' . $functionName . ' existiert nicht');
    }

    /**
     * @param $tracks
     * @return array
     */
    public static function transformDay(array $tracks)
    {
        $data = [];
        foreach ($tracks as $track) {
            $key = date('Ymd', $track['crdate']);
            if (!array_key_exists($key, $data)) {
                $data[$key] = [
                    'label' => date('d.m.Y', $track['crdate']),
                    'total' => 0,
                    'frontend_user' => 0
                ];
            }
            $data[$key]['total']++;
            if ($track['frontend_user'] > 0) {
                $data[$key]['frontend_user']++;
            }
        }
        return $data;
    }

    /**
     * @param $tracks
     * @return array
     */
    public static function transformFeuser(array $tracks)
    {
        $data = [];
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
        foreach ($tracks as $track) {
            if ($track['frontend_user'] > 0) {
                $key = 'user' . $track['frontend_user'];
                if (!array_key_exists($key, $data)) {
                    /** @var FrontendUser $frontendUser */
                    $frontendUser = $frontendUserRepository->findByUid($track['frontend_user']);
                    if ($frontendUser instanceof FrontendUser) {
                        $data[$key] = [
                            'uid' => $track['frontend_user'],
                            'username' => $frontendUser->getUsername(),
                            'name' => $frontendUser->getFirstName() . ' ' . $frontendUser->getLastName(),
                            'email' => $frontendUser->getEmail(),
                            'showlink' => 1,
                            'total' => 0,
                            'date' => [],
                        ];
                    } else {
                        $data[$key] = [
                            'uid' => $track['frontend_user'],
                            'username' => '[deleted, uid:' . $track['frontend_user'] . ']',
                            'name' => '[deleted, uid:' . $track['frontend_user'] . ']',
                            'email' => '[deleted, uid:' . $track['frontend_user'] . ']',
                            'showlink' => 0,
                            'total' => 0,
                            'date' => [],
                        ];
                    }
                }
                $data[$key]['total']++;
                $data[$key]['date'][] = date('d.m.Y H:i', $track['crdate']);
            }
        }
        return $data;
    }

}
