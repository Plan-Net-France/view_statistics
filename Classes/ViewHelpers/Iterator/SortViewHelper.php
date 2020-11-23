<?php
namespace CodingMs\ViewStatistics\ViewHelpers\Iterator;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Exception;

/**
 * Sorts an instance of ObjectStorage, an Iterator implementation,
 * an Array or a QueryResult (including Lazy counterparts).
 *
 */
class SortViewHelper extends AbstractViewHelper
{

    /**
     * Contains all flags that are allowed to be used
     * with the sorting functions
     *
     * @var array
     */
    protected $allowedSortFlags = [
        'SORT_REGULAR',
        'SORT_STRING',
        'SORT_NUMERIC',
        'SORT_NATURAL',
        'SORT_LOCALE_STRING',
        'SORT_FLAG_CASE'
    ];

    /**
     * Registers the "as" argument foruse with the
     * implementing ViewHelper.
     *
     * @return void
     */
    protected function registerAsArgument() {
        $this->registerArgument('as', 'string', 'Template variable name to assign; if not specified the ViewHelper returns the variable instead.');
    }

    /**
     * Initialize arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerAsArgument();
        $this->registerArgument('subject', 'mixed', 'The array/Traversable instance to sort');
        $this->registerArgument(
            'sortBy',
            'string',
            'Which property/field to sort by - leave out for numeric sorting based on indexes(keys)'
        );
        $this->registerArgument(
            'order',
            'string',
            'ASC, DESC, RAND or SHUFFLE. RAND preserves keys, SHUFFLE does not - but SHUFFLE is faster',
            false,
            'ASC'
        );
        $this->registerArgument(
            'sortFlags',
            'string',
            'Constant name from PHP for `SORT_FLAGS`: `SORT_REGULAR`, `SORT_STRING`, `SORT_NUMERIC`, ' .
            '`SORT_NATURAL`, `SORT_LOCALE_STRING` or `SORT_FLAG_CASE`. You can provide a comma seperated list or ' .
            'array touse a combination of flags.',
            false,
            'SORT_REGULAR'
        );
    }

    /**
     * "Render" method - sorts a target list-type target. Either $array or
     * $objectStorage must be specified. If both are, ObjectStorage takes precedence.
     *
     * Returns the same type as $subject. Ignores null values which would be
     * OK touse in an f:for (empty loop as result)
     * @return mixed
     * @throws \Exception
     */
    public function render()
    {
        $subject = $this->getArgumentFromArgumentsOrTagContent('subject');
        $sorted = null;
        if (true === is_array($subject)) {
            $sorted = $this->sortArray($subject);
        } else {
            if (true === $subject instanceof ObjectStorage || true === $subject instanceof LazyObjectStorage) {
                $sorted = $this->sortObjectStorage($subject);
            } elseif (true === $subject instanceof \Iterator) {
                $array = iterator_to_array($subject, true);
                $sorted = $this->sortArray($array);
            } elseif (true === $subject instanceof QueryResultInterface) {
                $sorted = $this->sortArray($subject->toArray());
            } elseif (null !== $subject) {
                // a null value is respected and ignored, but any
                // unrecognized value other than this is considered a
                // fatal error.
                throw new \Exception(
                    'Unsortable variable type passed to Iterator/SortViewHelper. Expected any of Array, QueryResult, ' .
                    ' ObjectStorage or Iterator implementation but got ' . gettype($subject),
                    1351958941
                );
            }
        }
        return $this->renderChildrenWithVariableOrReturnInput($sorted);
    }

    /**
     * Sort an array
     *
     * @param array|\Iterator $array
     * @return array
     */
    protected function sortArray($array)
    {
        $sorted = [];
        foreach ($array as $index => $object) {
            if (true === isset($this->arguments['sortBy'])) {
                $index = $this->getSortValue($object);
            }
            while (isset($sorted[$index])) {
                $index .= '.1';
            }
            $sorted[$index] = $object;
        }
        if ('ASC' === $this->arguments['order']) {
            ksort($sorted, $this->getSortFlags());
        } elseif ('RAND' === $this->arguments['order']) {
            $sortedKeys = array_keys($sorted);
            shuffle($sortedKeys);
            $backup = $sorted;
            $sorted = [];
            foreach ($sortedKeys as $sortedKey) {
                $sorted[$sortedKey] = $backup[$sortedKey];
            }
        } elseif ('SHUFFLE' === $this->arguments['order']) {
            shuffle($sorted);
        } else {
            krsort($sorted, $this->getSortFlags());
        }
        return $sorted;
    }

    /**
     * Sort an ObjectStorage instance
     *
     * @param ObjectStorage $storage
     * @return ObjectStorage
     */
    protected function sortObjectStorage($storage)
    {
        /** @var ObjectStorage $temp */
        $temp = GeneralUtility::makeInstance(ObjectStorage::class);
        foreach ($storage as $item) {
            $temp->attach($item);
        }
        $sorted = $this->sortArray($storage);
        $storage = GeneralUtility::makeInstance(ObjectStorage::class);
        foreach ($sorted as $item) {
            $storage->attach($item);
        }
        return $storage;
    }

    /**
     * Gets the value touse as sorting value from $object
     *
     * @param mixed $object
     * @return mixed
     */
    protected function getSortValue($object)
    {
        $field = $this->arguments['sortBy'];
        $value = ObjectAccess::getPropertyPath($object, $field);
        if (true === $value instanceof \DateTime) {
            $value = intval($value->format('U'));
        } elseif (true === $value instanceof ObjectStorage || true === $value instanceof LazyObjectStorage) {
            $value = $value->count();
        } elseif (is_array($value)) {
            $value = count($value);
        }
        return $value;
    }

    /**
     * Parses the supplied flags into the proper value for the sorting
     * function.
     * @return int
     * @throws Exception
     */
    protected function getSortFlags()
    {
        $constants = $this->arrayFromArrayOrTraversableOrCSV($this->arguments['sortFlags']);
        $flags = 0;
        foreach ($constants as $constant) {
            if (false === in_array($constant, $this->allowedSortFlags)) {
                throw new Exception(
                    'The constant "' . $constant . '" you\'re trying touse as a sortFlag is not allowed. Allowed ' .
                    'constants are: ' . implode(', ', $this->allowedSortFlags) . '.',
                    1404220538
                );
            }
            $flags = $flags | constant(trim($constant));
        }
        return $flags;
    }

    /**
     * Retrieve an argument either from arguments if
     * specified there, else from tag content.
     *
     * @param string $argumentName
     * @return mixed
     */
    protected function getArgumentFromArgumentsOrTagContent($argumentName) {
        if (false === $this->hasArgument($argumentName)) {
            $value = $this->renderChildren();
        } else {
            $value = $this->arguments[$argumentName];
        }
        return $value;
    }

    /**
     * Override of VhsViewHelperTrait equivalent. Does what
     * that function does, but also ensures an array return.
     *
     * @param string $argumentName
     * @return mixed
     */
    protected function getArgumentFromArgumentsOrTagContentAndConvertToArray($argumentName) {
        if (false === $this->hasArgument($argumentName)) {
            $value = $this->renderChildren();
        } else {
            $value = $this->arguments[$argumentName];
        }
        return $this->arrayFromArrayOrTraversableOrCSV($value);
    }

    /**
     * @param mixed $candidate
     * @param boolean $useKeys
     *
     * @return array
     * @throws Exception
     */
    protected function arrayFromArrayOrTraversableOrCSV($candidate, $useKeys = TRUE) {
        if (TRUE === $candidate instanceof \Traversable) {
            return iterator_to_array($candidate, $useKeys);
        } elseif (TRUE === $candidate instanceof QueryResultInterface) {
            /** @var QueryResultInterface $candidate */
            return $candidate->toArray();
        }
        if (TRUE === is_string($candidate)) {
            return GeneralUtility::trimExplode(',', $candidate, TRUE);
        } elseif (TRUE === is_array($candidate)) {
            return $candidate;
        }
        throw new Exception('Unsupported input type; cannot convert to array!');
    }

    /**
     * @param $array1
     * @param $array2
     * @return array
     */
    protected function mergeArrays($array1, $array2) {
        ArrayUtility::mergeRecursiveWithOverrule($array1, $array2);
        return $array1;
    }

    /**
     * @return mixed
     */
    protected function renderChildrenWithVariableOrReturnInput($variable = null) {
        $as = $this->arguments['as'];
        if (TRUE === empty($as)) {
            return $variable;
        } else {
            $variables = array($as => $variable);
            $content = static::renderChildrenWithVariables($variables);
        }
        return $content;
    }

    /**
     * @param mixed $variable
     * @param string $as
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @param \Closure $renderChildrenClosure
     * @return mixed
     */
    protected static function renderChildrenWithVariableOrReturnInputStatic(
        $variable,
        $as,
        RenderingContextInterface $renderingContext,
        \Closure $renderChildrenClosure
    ) {
        if (true === empty($as)) {
            return $variable;
        } else {
            $variables = [$as => $variable];
            $content = static::renderChildrenWithVariablesStatic(
                $variables,
                $renderingContext->getVariableProvider(),
                $renderChildrenClosure
            );
        }
        return $content;
    }

    /**
     * Renders tag content of ViewHelper and inserts variables
     * in $variables into $variableContainer while keeping backups
     * of each existing variable, restoring it after rendering.
     * Returns the output of the renderChildren() method on $viewHelper.
     *
     * @param array $variables
     * @return mixed
     */
    protected function renderChildrenWithVariables(array $variables)
    {
        return static::renderChildrenWithVariablesStatic(
            $variables,
            $this->templateVariableContainer,
            $this->buildRenderChildrenClosure()
        );
    }

    /**
     * Renders tag content of ViewHelper and inserts variables
     * in $variables into $variableContainer while keeping backups
     * of each existing variable, restoring it after rendering.
     * Returns the output of the renderChildren() method on $viewHelper.
     *
     * @param array $variables
     * @param VariableProviderInterface $templateVariableContainer
     * @param \Closure $renderChildrenClosure
     * @return mixed
     */
    protected static function renderChildrenWithVariablesStatic(
        array $variables,
        $templateVariableContainer,
        $renderChildrenClosure
    ) {
        $backups = static::backupVariables($variables, $templateVariableContainer);
        $content = $renderChildrenClosure();
        static::restoreVariables($variables, $backups, $templateVariableContainer);
        return $content;
    }

    /**
     * @param array $variables
     * @param VariableProviderInterface $templateVariableContainer
     * @return array
     */
    private static function backupVariables(array $variables, $templateVariableContainer)
    {
        $backups = [];
        foreach ($variables as $variableName => $variableValue) {
            if (true === $templateVariableContainer->exists($variableName)) {
                $backups[$variableName] = $templateVariableContainer->get($variableName);
                $templateVariableContainer->remove($variableName);
            }
            $templateVariableContainer->add($variableName, $variableValue);
        }
        return $backups;
    }

    /**
     * @param array $variables
     * @param array $backups
     * @param VariableProviderInterface $templateVariableContainer
     * @return void
     */
    private static function restoreVariables(array $variables, array $backups, $templateVariableContainer)
    {
        foreach ($variables as $variableName => $variableValue) {
            $templateVariableContainer->remove($variableName);
            if (true === isset($backups[$variableName])) {
                $templateVariableContainer->add($variableName, $variableValue);
            }
        }
    }
}
