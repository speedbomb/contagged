<?php
namespace Speedbomb\Contagged\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Marco Schrepfer <typo3@speedbomb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

use Speedbomb\Contagged\Domain\Model\Term;
use Speedbomb\Contagged\Domain\Repository\TermRepository;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Parser service
 *
 * @package contagged
 */
class ParserService implements SingletonInterface {

    /**
     * The content object
     * @var ContentObjectRenderer
     */
    protected $cObj;

    /**
     * Contagged settings
     * @var array
     */
    protected $settings = array();

    /**
     * Term repository
     * @var TermRepository
     */
    protected $termRepository = null;


    protected $terms;

    /**
     * The TS config
     * @var array
     */
    protected $tsConfig = array();

    /**
     * Constructor
     *
     * Initializes:
     *  - objectManager to get class instances
     *  - configuration manager for ts settings
     *  - contentObjectRenderer for generating links etc.
     *  - termRepository to get the Terms
     *
     */
    public function __construct() {
        // Make instance of object manager
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        // Get configuration manager
        /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager */
        $configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManager');

        // Inject content object renderer
        $this->cObj = $objectManager->get('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

        // Get query settings
        /** @var QuerySettingsInterface $querySettings */
        $querySettings = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface');

        // Get term repository
        $this->termRepository = $objectManager->get('Speedbomb\Contagged\Domain\Repository\TermRepository');

        // Get TypoScript configuration and reduce TS config to plugin
        $this->tsConfig = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->tsConfig = $this->tsConfig['plugin.']['tx_contagged.'];

        if($this->tsConfig !== null && count($this->tsConfig) > 0) {
            $this->initSettings();

            // Set StoragePid in the query settings object
            $querySettings->setStoragePageIds(GeneralUtility::trimExplode(',', $this->tsConfig['persistence.']['storagePid']));

            // Set current language uid
            $querySettings->setLanguageUid($GLOBALS['TSFE']->sys_language_uid);

            // Assign query settings object to repository
            $this->termRepository->setDefaultQuerySettings($querySettings);
        }

        unset($querySettings, $configurationManager, $objectManager);
    }

    /**
     * Initialize the TS settings
     */
    protected function initSettings() {
        // Save extension settings without ts dots
        $this->settings = GeneralUtility::removeDotsFromTS($this->tsConfig['settings.']);

        // type conversion: boolean
        foreach(array('disableParser') as $setting) {
            $this->settings[$setting] = $this->settings[$setting] == true;
        }

        // type conversion: integer
        foreach(array('detailPage', 'listPage') as $setting) {
            $this->settings[$setting] = intval($this->settings[$setting]);
        }

        // type conversion: list to array
        foreach(array('includeRootPages', 'excludeRootPages', 'includePages', 'excludePages') as $setting) {
            $this->settings[$setting] =  GeneralUtility::trimExplode(',', $this->settings[$setting], true);
        }
    }

    /**
     * Main function called by hook 'contentPostProc-all'
     *
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController[] $pobjs Controller references
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $controller Controller
     * @return void
     */
    public function pageParser($pobjs, &$controller) {

        // Abort parser if no settings available or
        if(!is_array($this->settings) || count($this->settings) == 0 ||
            // parser is disabled
            $this->settings['disableParser'] == true ||
            // Pagetype not 0
            $GLOBALS['TSFE']->type !== 0 ||
            // current page is the glossary detailpage
            $GLOBALS['TSFE']->id == $this->settings['detailPage'] ||
            // current page is the glossary listpage
            $GLOBALS['TSFE']->id == $this->settings['listPage'] ||
            // no config is given
            count($this->tsConfig) == 0 ||
            // page skip test
            $this->isPageToSkip()
        ) {
            return;
        }

        // get the terms
        $this->terms = $this->termRepository->findTerms();

        // get the content
        $content = $controller->content;
        $htmlParser = GeneralUtility::makeInstance('TYPO3\CMS\Core\Html\HtmlParser');
        $splittedContent = $htmlParser->splitIntoBlock($this->getTagsToOmitt(), $content);

        foreach ((array)$splittedContent as $intKey => $HTMLvalue) {
            if (!($intKey % 2)) {
                $positionsArray = array();
                foreach ($sortedTerms as $termAndKey) {
                    if (empty($termAndKey['term'])) {
                        continue;
                    }
                    $this->getPositions($splittedContent[$intKey], $positionsArray, $termAndKey['term'], $termAndKey['key']);
                }
                ksort($positionsArray);
                $splittedContent[$intKey] = $this->doReplace($splittedContent[$intKey], $positionsArray);
            }
        }
        $parsedContent = implode('', $splittedContent);

        $controller->content = $parsedContent;
    }

    /**
     * Test, wether the parsing of the current page should be skipped
     *
     * @return bool $skip TRUE if the page should be skipped
     */
    protected function isPageToSkip() {
        $skip = true;
        $page_id = intval($GLOBALS['TSFE']->id);

        // check if this page shouldn't be parsed
        if($GLOBALS['TSFE']->page['tx_contagged_dont_parse'] == 1) {
            return true;
        }

        // get rootline of the current page
        $rootline = $GLOBALS['TSFE']->sys_page->getRootline($page_id);
        $pageUidsInRootline = array();

        // build an array of uids of pages in the rootline
        for($i = count($rootline) - 1; $i >= 0; $i--) {
            $pageUidsInRootline[] = $rootline[$i]['uid'];
        }

        // check if a desired root page is in the rootline of the current page
        foreach($this->settings['includeRootPages'] as $root_page_id) {
            if(ArrayUtility::inArray($pageUidsInRootline, $root_page_id)) {
                $skip = false;
            }
        }

        // check if a forbidden root page is in the rootline of the current page
        foreach($this->settings['excludeRootPages'] as $root_page_id) {
            if(ArrayUtility::inArray($pageUidsInRootline, $root_page_id)) {
                $skip = true;
            }
        }

        // check if current page is in the list of desired pages
        if(ArrayUtility::inArray($this->settings['includePages'], $page_id)) {
            $skip = false;
        }

        // check if current page is in the list of forbidden pages
        if(ArrayUtility::inArray($this->settings['excludePages'], $page_id)) {
            $skip = true;
        }

        // @todo test if there is a possibility to parse single content elements
        /*
        if(!empty($this->cObj)) {
            if($this->cObj->getFieldVal('tx_contagged_dont_parse') == 1) {
                $skip = true;
            }
        }
        */

        return $skip;
    }
}
