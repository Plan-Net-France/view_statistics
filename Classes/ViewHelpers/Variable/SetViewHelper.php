<?php

namespace CodingMs\ViewStatistics\ViewHelpers\Variable;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 Thomas Deuling <typo3@coding.ms>
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

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SetViewHelper extends AbstractViewHelper {

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'mixed', 'Value to set');
        $this->registerArgument('name', 'string', 'Name of variable to assign');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $name = $arguments['name'];
        $value = $renderChildrenClosure();
        if ($value === null) {
            $value = $arguments['value'];
        }
        $variableProvider = $renderingContext->getVariableProvider();
        if (false === strpos($name, '.')) {
            if (true === $variableProvider->exists($name)) {
                $variableProvider->remove($name);
            }
            $variableProvider->add($name, $value);
        } elseif (1 === mb_substr_count($name, '.')) {
            $parts = explode('.', $name);
            $objectName = array_shift($parts);
            $path = implode('.', $parts);
            if (false === $variableProvider->exists($objectName)) {
                return null;
            }
            $object = $variableProvider->get($objectName);
            try {
                ObjectAccess::setProperty($object, $path, $value);
                // Note: re-insert the variable to ensure unreferenced values like arrays also get updated
                $variableProvider->remove($objectName);
                $variableProvider->add($objectName, $object);
            } catch (\Exception $error) {
                return null;
            }
        }
        return null;
    }

}
