<?php
namespace CodingMs\ViewStatistics\ViewHelpers\Format;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Format the login duration
 */
class LoginDurationViewHelper extends AbstractViewHelper
{

    /**
     * Format the login duration
     *
     * @param int $loginDuration
     * @return string
     */
    public function render($loginDuration = null)
    {
        $hours = floor($loginDuration / 3600);
        $minutes = floor($loginDuration / 60 % 60);
        $seconds = floor($loginDuration % 60);
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
