<?php
/**
 * Collection of old code
 * @deprecated
 * @todo delete file
 */
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Parser service
 *
 * @package contagged
 */
class Temp implements SingletonInterface {

    /**
     * tags to be always ignored by parsing
     * @var array
     */
    public static $alwaysIgnoreParentTags = array(
        'a',
        'script',
    );

    /**
     * @var ContentObjectRenderer $cObj
     */
    protected $cObj;

    /**
     * Settings
     * @var array
     */
    protected $settings = array();

    /**
     * Basic collection of terms (db records)
     * @var \Speedbomb\Contagged\Domain\Model\Term[]
     */
    protected $terms = array();

    /**
     * The sorted term list
     * @var array
     */
    protected $termlist = array();

    /**
     * The TS config
     * @var array
     */
    protected $tsConfig = array();

    /**
     * Constructor
     *
     * Boots up:
     *  - objectManager to get class instances
     *  - configuration manager for ts settings
     *  - contentObjectRenderer for generating links etc.
     *  - termRepository to get the Terms
     *
     */
    public function __construct() {
        // Make instance of Object Manager
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        // Get Configuration Manager
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManager');

        // Inject Content Object Renderer
        $this->cObj = $objectManager->get('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

        // Get Query Settings
        /** @var QuerySettingsInterface $querySettings */
        $querySettings = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface');

        // Get termRepository
        /** @var TermRepository $termRepository */
        $termRepository = $objectManager->get('Speedbomb\Contagged\Domain\Repository\TermRepository');

        // Get Typoscript Configuration and reduce TS config to plugin
        $this->tsConfig = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->tsConfig = $this->tsConfig['plugin.']['tx_contagged.'];

        if($this->tsConfig !== null && count($this->tsConfig) > 0) {
            // Save extension settings without ts dots
            $this->settings = GeneralUtility::removeDotsFromTS($this->tsConfig['settings.']);

            // Set StoragePid in the query settings object
            $querySettings->setStoragePageIds(GeneralUtility::trimExplode(',',
                $this->tsConfig['persistence.']['storagePid']));

            // Set current language uid
            $querySettings->setLanguageUid($GLOBALS['TSFE']->sys_language_uid);

            // Assign query settings object to repository
            $termRepository->setDefaultQuerySettings($querySettings);

            // Find all terms
            //$terms = $termRepository->findByTermMainLength();
            $result = $termRepository->findAll();

            //Sort terms with an individual counter for max replacement per page
            /** @var Term $term */
            if($result->count() > 0) {
                $terms = $result->toArray();
                foreach($terms as $term) {
                    $this->terms[] = array(
                        'term' => $term,
                        'replacements' => (int) $this->settings['maxReplacementPerPage']
                    );
                }
            }

            unset($terms, $term, $result);
        }

        unset($termRepository, $querySettings, $configurationManager, $objectManager);
    }

    /**
     * Test, wether the parsing of a page should be skippen
     *
     * @param int $page_id Page ID
     * @return bool $skip TRUE if the page should be skipped
     */
    protected function isPageToSkip($page_id = 0) {
        $skip = true;
        $page_id = intval($page_id);
        if($page_id <= 0) {
            $page_id = $GLOBALS['TSFE']->id;
        }

        // get rootline of the current page
        $rootline = $GLOBALS['TSFE']->sys_page->getRootline($page_id);
        $pageUidsInRootline = array();

        // build an array of uids of pages in the rootline
        for($i = count($rootline) - 1; $i >= 0; $i--) {
            $pageUidsInRootline[] = $rootline[$i]['uid'];
        }

        // check if the root page is in the rootline of the current page
        $includeRootPagesUids = GeneralUtility::trimExplode(',', $this->settings['includeRootPages'], true);
        foreach($includeRootPagesUids as $includeRootPageUid) {
            if(ArrayUtility::inArray($pageUidsInRootline, $includeRootPageUid)) {
                $skip = false;
            }
        }

        $excludeRootPagesUids = GeneralUtility::trimExplode(',', $this->settings['excludeRootPages'], true);
        foreach($excludeRootPagesUids as $excludeRootPageUid) {
            if(ArrayUtility::inArray($pageUidsInRootline, $excludeRootPageUid)) {
                $skip = true;
            }
        }

        if(GeneralUtility::inList($this->settings['includePages'], $page_id)) {
            $skip = false;
        }

        if(GeneralUtility::inList($this->settings['excludePages'], $page_id)) {
            $skip = true;
        }

        if($GLOBALS['TSFE']->page['tx_contagged_dont_parse'] == 1) {
            $skip = true;
        }

        if(!empty($this->cObj)) {
            if($this->cObj->getFieldVal('tx_contagged_dont_parse') == 1) {
                $skip = true;
            }
        }

        return $skip;
    }

    /**
     * Main function called by hook 'contentPostProc-all'
     *
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController[] $pobjs ?
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $controller Controller
     * @return void
     */
    public function pageParser($pobjs, &$controller) {

        echo "pageParser!<br>";

        // Abort parser if no settings available or parser is disabled
        if(!is_array($this->settings) || count($this->settings) == 0 || $this->settings['disableParser'] == true) {
            return;
        }

        #debug($this->settings);

        // extract Pids which should be parsed
        $parsingPids = GeneralUtility::trimExplode(',', $this->settings['parsingPids'], true);
        // extract Pids which should NOT be parsed
        $excludePids = GeneralUtility::trimExplode(',', $this->settings['parsingExcludePidList'], true);

        // Abort parser...
        if(
            // Pagetype not 0
            $GLOBALS['TSFE']->type !== 0 ||
            // current page is the glossary detailpage
            $GLOBALS['TSFE']->id === (integer) $this->settings['detailPage'] ||
            // current page is the glossary listpage
            $GLOBALS['TSFE']->id === (integer) $this->settings['listPage'] ||
            // no terms have been found
            count($this->terms) === 0 ||
            // no config is given
            count($this->tsConfig) === 0 ||
            (
                // parsingPids doesn't contain 0 and...
                false === in_array('0', $parsingPids, true) &&
                (
                    // page is excluded
                    true === in_array($GLOBALS['TSFE']->id, $excludePids, false) ||
                    // page is not whitelisted
                    false === in_array($GLOBALS['TSFE']->id, $parsingPids, false)
                )
            )
        ) {
            return;
        }

        $content = $controller->content;

        // Tags which are not allowed as direct parent for a parsingTag
        $forbiddenParentTags = array_filter(GeneralUtility::trimExplode(',', $this->settings['forbiddenParentTags']));

        // Add "a" if unknowingly deleted to prevent errors
        if(false === in_array(self::$alwaysIgnoreParentTags, $forbiddenParentTags, true)) {
            $forbiddenParentTags = array_unique(
                array_merge($forbiddenParentTags, self::$alwaysIgnoreParentTags)
            );
        }

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

        // update the keywords (field "tx_contagged_keywords" in table "page")
        /*
        if ($this->conf['updateKeywords'] > 0) {
            $this->updatePageKeywords();
        }
        */

        $controller->content = $parsedContent;

    }

    /**
     * Some content tagged by configured tags could be prevented from beeing parsed.
     * This function collects all the tags which should be considered.
     *
     * @return    string        Comma separated list of tags
     */
    function getTagsToOmitt() {
        $tagArray = array();

        // if there are tags to exclude: add them to the list
        if ($this->settings['excludeTags']) {
            $tagArray = GeneralUtility::trimExplode(',', $this->settings['excludeTags'], true);
        }

        // if configured: add tags used by the term definitions
        if ($this->settings['autoExcludeTags'] > 0) {
            foreach ($this->tsConfig['types.'] as $key => $type) {
                if (!empty($type['tag']) && !in_array($type['tag'], $tagArray)) {
                    $tagArray[] = $type['tag'];
                }
            }
        }

        return implode(',', $tagArray);
    }

    /**
     * Renders the wrapped term using the plugin settings
     *
     * @param Term $term
     * @return string
     */
    protected function termWrapper(Term $term) {
        // get content object type
        $contentObjectType = $this->tsConfig['settings.']['termWraps'];
        // get term wrapping settings
        $wrapSettings = $this->tsConfig['settings.']['termWraps.'];
        // pass term data to the cObject pseudo constructor
        $this->cObj->start($term->toArray());

        // return the wrapped term
        return $this->cObj->cObjGetSingle($contentObjectType, $wrapSettings);
    }

    /**
     * Parse the extracted html for terms with a regex
     *
     * @param string $text
     * @return string
     */
    public function textParser($text) {
        $text = preg_replace('#\x{00a0}#iu', '&nbsp;', $text);
        // Iterate over terms and search matches for each of them
        foreach($this->terms as $term) {
            /** @var Term $termObject */
            $termObject = $term['term'];
            $replacements = &$term['replacements'];

            //Check replacement counter
            if(0 !== $term['replacements']) {
                /*
                 * Regex Explanation:
                 * Group 1: (^|[\s\>[:punct:]])
                 *  ^         = can be begin of the string
                 *  \G        = can match an other matchs end
                 *  \s        = can have space before term
                 *  \>        = can have a > before term (end of some tag)
                 *  [:punct:] = can have punctuation characters like .,?!& etc. before term
                 *
                 * Group 2: (' . preg_quote($term->getName()) . ')
                 *  The term to find, preg_quote() escapes special chars
                 *
                 * Group 3: ($|[\s\<[:punct:]])
                 *  Same as Group 1 but with end of string and < (start of some tag)
                 *
                 * Group 4: (?![^<]*>|[^<>]*<\/)
                 *  This Group protects any children element of the tag which should be parsed
                 *  ?!        = negative lookahead
                 *  [^<]*>    = match is between < & > and some other character
                 *              avoids parsing terms in self closing tags
                 *              example: <TERM> will work <TERM > not
                 *  [^<>]*<\/ = match is between some tag and tag ending
                 *              example: < or >TERM</>
                 *
                 * Flags:
                 * i = ignores camel case
                 */
                $regex = '#' .
                    '(^|\G|[\s\>[:punct:]])' .
                    '(' . preg_quote($termObject->getName()) . ')' .
                    '($|[\s\<[:punct:]])' .
                    '(?![^<]*>|[^<>]*<\/)' .
                    '#i';

                // replace callback
                $callback = function ($match) use ($termObject, &$replacements) {
                    //decrease replacement counter
                    if(0 < $replacements) {
                        $replacements--;
                    }
                    // Use term match to keep original camel case
                    $termObject->setName($match[2]);

                    // Wrap replacement with original chars
                    return $match[1] . $this->termWrapper($termObject) . $match[3];
                };

                // Use callback to keep allowed chars around the term and his camel case
                $text = preg_replace_callback($regex, $callback, $text, $replacements);
            }
        }

        return $text;
    }

    /**
     * Expand the contents of $this->terms, sort and save to $this->termlist.
     */
    protected function setTermlist($term_records) {
        $this->termlist = array();
        foreach($this->terms as $term) {
            $excludeTerms = explode(',', $this->conf['excludeTerms']);
            $sortedTerms = array();
            foreach ($this->termsArray as $termKey => $termArray) {
                if ($this->conf['autoExcludeTerms'] == 1 && in_array($termArray['term_main'], $excludeTerms)) {
                    continue;
                }
                $sortedTerms[] = array('term' => $termArray['term_main'], 'key' => $termKey);
                if (is_array($termArray['term_alt'])) {
                    foreach ($termArray['term_alt'] as $term) {
                        if ($this->conf['autoExcludeTerms'] == 1 && in_array($term, $excludeTerms)) {
                            continue;
                        }
                        $sortedTerms[] = array('term' => $term, 'key' => $termKey);
                    }
                }
            }

            // sort the array descending by length of the value, so the longest term will match
            usort($sortedTerms, array($this, 'sortTermsByDescendingLength'));
        }
    }
}
