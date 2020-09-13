<?php

namespace CodingMs\ViewStatistics\ViewHelpers\Be;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class EditLinkViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
        $this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
        $this->registerArgument('table', 'string', 'Table');
        $this->registerArgument('uid', 'int', 'Unique identifier');
        $this->registerArgument('action', 'string', 'Action', false, 'edit');
        $this->registerArgument('module', 'string', 'Module', false, '');
        $this->registerArgument('modal', 'array', 'Modal', false, []);
        $this->registerArgument('columnsOnly', 'string', 'Columns to edit (multiple must be comma separated)', false, '');
    }

    /**
     * Crafts a link to edit a database record or create a new one
     *
     * @return string The <a> tag
     * @see \TYPO3\CMS\Backend\Utility::editOnClick()
     */
    public function render()
    {
        $table = $this->arguments['table'];
        $uid = $this->arguments['uid'];
        $action = $this->arguments['action'];
        $module = $this->arguments['module'];
        $modal = $this->arguments['modal'];
        $uri = '#';
        $pageUid = (int)GeneralUtility::_GP('id');
        $returnUrl = BackendUtility::getModuleUrl($module, GeneralUtility::_GET());
        switch ($action) {
            case 'edit':
                $parameter = [
                    'returnUrl' => $returnUrl,
                    'id' => $pageUid,
                    'edit' => [
                        $table => [
                            $uid => 'edit'
                        ]
                    ]
                ];
                // Columns are defined?
                if (trim($this->arguments['columnsOnly']) != '') {
                    $parameter['columnsOnly'] = $this->arguments['columnsOnly'];
                }
                $uri = BackendUtility::getModuleUrl('record_edit', $parameter);
                break;
            case 'delete':
                $parameter = [
                    'redirect' => $returnUrl,
                    'id' => $pageUid,
                    'cmd' => [
                        $table => [
                            $uid => [
                                'delete' => 1
                            ]
                        ]
                    ]
                ];
                $uri = BackendUtility::getModuleUrl('tce_db', $parameter);
                // Add a modal
                if(!empty($modal)) {
                    if(isset($modal['severity'])) {
                        $this->tag->addAttribute('data-severity', $modal['severity']);
                    }
                    if(isset($modal['title'])) {
                        $this->tag->addAttribute('data-title', $modal['title']);
                    }
                    if(isset($modal['content'])) {
                        $this->tag->addAttribute('data-content', $modal['content']);
                    }
                    if(isset($modal['buttonCloseText'])) {
                        $this->tag->addAttribute('data-button-close-text', $modal['buttonCloseText']);
                    }
                }
                break;
            case 'hide':
                $parameter = [
                    'redirect' => $returnUrl,
                    'id' => $pageUid,
                    'data' => [
                        $table => [
                            $uid => [
                                'hidden' => 1
                            ]
                        ]
                    ]
                ];
                $uri = BackendUtility::getModuleUrl('tce_db', $parameter);
                break;
            case 'show':
                $parameter = [
                    'redirect' => $returnUrl,
                    'id' => $pageUid,
                    'data' => [
                        $table => [
                            $uid => [
                                'hidden' => 0
                            ]
                        ]
                    ]
                ];
                $uri = BackendUtility::getModuleUrl('tce_db', $parameter);
                break;
            case 'enable':
                $parameter = [
                    'redirect' => $returnUrl,
                    'id' => $pageUid,
                    'data' => [
                        $table => [
                            $uid => [
                                'disable' => 0
                            ]
                        ]
                    ]
                ];
                $uri = BackendUtility::getModuleUrl('tce_db', $parameter);
                break;
            case 'disable':
                $parameter = [
                    'redirect' => $returnUrl,
                    'id' => $pageUid,
                    'data' => [
                        $table => [
                            $uid => [
                                'disable' => 1
                            ]
                        ]
                    ]
                ];
                $uri = BackendUtility::getModuleUrl('tce_db', $parameter);
                break;
        }
        // Build attribute
        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);
        return $this->tag->render();
    }

}
