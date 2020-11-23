<?php
namespace CodingMs\ViewStatistics\ViewHelpers\Iterator;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Implode ViewHelper
 *
 * Implodes an array or array-convertible object by $glue.
 */
class ImplodeViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('content', 'string', 'Content', TRUE);
        $this->registerArgument('glue', 'string', 'Glue', TRUE);
    }

    /**
     * Render method
     *
     * @return string
     */
    public function render() {
        return implode($this->arguments['glue'], $this->arguments['content']);
    }
}
