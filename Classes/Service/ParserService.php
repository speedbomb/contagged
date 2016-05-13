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
     *
     * @var ContentObjectRenderer
     */
    protected $cObj;

    /**
     * Terms that should not be rendered
     *
     * @var array
     */
    protected $excludeTerms;

    /**
     * Get params
     * @var array
     */
    protected $params = array();

    /**
     * Contagged settings
     *
     * @var array
     */
    protected $settings = array();

    /**
     * Term repository
     *
     * @var TermRepository
     */
    protected $termRepository = null;

    /**
     * The terms
     *
     * @var \Speedbomb\Contagged\Domain\Model\Term[]
     */
    protected $terms;

    /**
     * The terms, sorted by length
     *
     * @var \Speedbomb\Contagged\Domain\Model\Term[]
     */
    protected $termsSorted;

    /**
     * The TS config
     *
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
            $querySettings->setStoragePageIds(GeneralUtility::trimExplode(',',
                $this->tsConfig['persistence.']['storagePid']));

            // Set current language uid
            $querySettings->setLanguageUid($GLOBALS['TSFE']->sys_language_uid);

            // Assign query settings object to repository
            $this->termRepository->setDefaultQuerySettings($querySettings);
        }

        // get params
        $get = GeneralUtility::_GET();
        if(isset($get['tx_contagged_glossary'])) {
            foreach($get['tx_contagged_glossary'] as $get_key => $get_value) {
                $this->params[$get_key] = $get_value;
            }
        }

        unset($querySettings, $configurationManager, $objectManager, $get);
    }

    /**
     * Fetches the terms and sorts them
     */
    protected function fetchTerms() {
        // fetch term from repository
        $this->terms = $this->termRepository->findTerms();

        // test if current page is the detail page
        $this->excludeTerms = array();
        if($GLOBALS['TSFE']->id == $this->settings['detailPage']) {
            if($this->params['controller'] == 'Glossary' && $this->params['action'] == 'detail') {
                /** @var \Speedbomb\Contagged\Domain\Model\Term $current_term */
                $current_term = $this->termRepository->findByUid(intval($this->params['term']));
                if($current_term != null) {
                    // save all term variations of the current term
                    $this->excludeTerms = array_merge(array($current_term->getTermMain()), GeneralUtility::trimExplode("\n", $current_term->getTermAlt(), true));
                }
                unset($current_term);
            }
        }

        // sort terms
        $this->termsSorted = array();
return;
        // @todo refactor for use as array of objects
        foreach($this->terms as $term_key => $term_array) {
            if($this->settings['autoExcludeTerms'] && in_array($term_array['term_main'], $this->excludeTerms)) {
                continue;
            }

            $this->termsSorted[] = array('term' => $term_array['term_main'], 'key' => $term_key);
            if(is_array($term_array['term_alt'])) {
                foreach($term_array['term_alt'] as $term) {
                    if($this->settings['autoExcludeTerms'] && in_array($term, $this->excludeTerms)) {
                        continue;
                    }
                    $this->termsSorted[] = array('term' => $term, 'key' => $term_key);
                }
            }
        }

        // sort the array descending by length of the value, so the longest term will match
        usort($this->termsSorted, array($this, 'sortTermsByDescendingLength'));
    }

    /**
     * Some content tagged by configured tags could be prevented from beeing parsed.
     * This function collects all the tags which should be considered.
     *
     * @return string Comma separated list of tags
     */
    protected function getTagsToOmitt() {
        $tagArray = $this->settings['excludeTags'];

        // if configured: add tags used by the term definitions
        // @todo span with class add 'span'... test it
        if($this->settings['autoExcludeTags'] > 0) {
            foreach($this->tsConfig['types.'] as $key => $type) {
                if(!empty($type['tag']) && !in_array($type['tag'], $tagArray)) {
                    $tagArray[] = $type['tag'];
                }
            }
        }

        return implode(',', $tagArray);
    }

    /**
     * Initialize the TS settings
     */
    protected function initSettings() {
        // Save extension settings without ts dots
        $this->settings = GeneralUtility::removeDotsFromTS($this->tsConfig['settings.']);

        // type conversion: boolean
        foreach(array('disableParser', 'autoExcludeTags', 'useSearchTags', 'parseSingleView') as $setting) {
            $this->settings[$setting] = $this->settings[$setting] == true;
        }

        // type conversion: integer
        foreach(array('detailPage', 'listPage') as $setting) {
            $this->settings[$setting] = intval($this->settings[$setting]);
        }

        // type conversion: list to array
        foreach(array(
            'includeRootPages',
            'excludeRootPages',
            'includePages',
            'excludePages',
            'excludeTags'
        ) as $setting) {
            $this->settings[$setting] = GeneralUtility::trimExplode(',', $this->settings[$setting], true);
        }
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
            // current page is the glossary detailpage and single page parsing is not allowed
            ($GLOBALS['TSFE']->id == $this->settings['detailPage'] && $this->settings['parseSingleView'] != true) ||
            // current page is the glossary listpage
            $GLOBALS['TSFE']->id == $this->settings['listPage'] ||
            // no config is given
            count($this->tsConfig) == 0 ||
            // page skip test
            $this->isPageToSkip()
        ) {
            return;
        }

        // fetch the terms
        $this->fetchTerms();

        // get the content
        #$content = $controller->content; // complete html
        $content = $GLOBALS['TSFE']->getPageRenderer()->getBodyContent(); // body content

        // if set, the contents could be limited to content within the TYPO3SEARCH tags
        if($this->settings['useSearchTags']) {
            /** @var \TYPO3\CMS\IndexedSearch\Indexer $indexer */
            $indexer = GeneralUtility::makeInstance('TYPO3\CMS\IndexedSearch\Indexer');
            $indexer->typoSearchTags($content);
            unset($indexer);
        }

        $htmlParser = GeneralUtility::makeInstance('TYPO3\CMS\Core\Html\HtmlParser');
        $splittedContent = $htmlParser->splitIntoBlock($this->getTagsToOmitt(), $content);

        return;

        foreach((array) $splittedContent as $intKey => $HTMLvalue) {
            if(!($intKey % 2)) {
                $positionsArray = array();
                foreach($sortedTerms as $termAndKey) {
                    if(empty($termAndKey['term'])) {
                        continue;
                    }
                    $this->getPositions($splittedContent[$intKey], $positionsArray, $termAndKey['term'],
                        $termAndKey['key']);
                }
                ksort($positionsArray);
                $splittedContent[$intKey] = $this->doReplace($splittedContent[$intKey], $positionsArray);
            }
        }
        $parsedContent = implode('', $splittedContent);

        $controller->content = $parsedContent;
    }

    /**
     * Utility method to sort array items according to the (string) length of their 'term' item.
     *
     * Note: the sorting is descending
     *
     * @param array $a Array with key 'term' containing a term
     * @param array $b Array with key 'term' containing a term
     * @return integer +1 if term from a is shorter than b, -1 for the contrary, 0 in case of equality
     */
    public static function sortTermsByDescendingLength($a, $b) {
        // Calculate length correctly by relying on \TYPO3\CMS\Core\Charset\CharsetConverter
        $aTermLength = $GLOBALS['TSFE']->csConvObj->strlen($GLOBALS['TSFE']->renderCharset, $a['term']);
        $bTermLength = $GLOBALS['TSFE']->csConvObj->strlen($GLOBALS['TSFE']->renderCharset, $b['term']);
        if($aTermLength == $bTermLength) {
            return 0;
        } else {
            return ($aTermLength < $bTermLength) ? +1 : -1;
        }
    }
}
