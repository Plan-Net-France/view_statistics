<?php
namespace CodingMs\ViewStatistics\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Get a label for a tracked object
 */
class ObjectLabelViewHelper extends AbstractViewHelper
{

    /**
     * Format the login duration
     *
     * @param string $table
     * @param int $uid
     * @param string $field
     * @return string
     */
    public function render($table, $uid, $field)
    {
        return \CodingMs\ViewStatistics\Service\ObjectService::getLabel($table, $uid, $field);
    }
}
