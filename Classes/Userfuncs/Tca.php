<?php
namespace Speedbomb\Contagged\Userfuncs;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class containing user functions for use in TCA config
 *
 * @package contagged
 */
class Tca implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * @param array $field Current field config
     */
    public function addTermTypes(&$field) {
        \Speedbomb\Contagged\Userfuncs\Tca

        $template = GeneralUtility::makeInstance('TYPO3\CMS\Core\TypoScript\TemplateService');
        $template->tt_track = 0;
        $template->init();
        $sysPage = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');
        $rootline = $sysPage->getRootLine($this->getCurrentPageId());
        $rootlineIndex = 0;
        foreach($rootline as $index => $rootlinePart) {
            if($rootlinePart['is_siteroot'] == 1) {
                $rootlineIndex = $index;
                break;
            }
        }
        $template->runThroughTemplates($rootline, $rootlineIndex);
        $template->generateConfig();
        $conf = $template->setup['plugin.']['tx_contagged.'];

        // make localized labels
        $LOCAL_LANG_ARRAY = array();
        if(!empty($conf['types.'])) {
            foreach($conf['types.'] as $typeName => $typeConfigArray) {
                unset($LOCAL_LANG_ARRAY);
                if(!$typeConfigArray['hideSelection'] > 0 && !$typeConfigArray['dataSource']) {
                    if(is_array($typeConfigArray['label.'])) {
                        foreach($typeConfigArray['label.'] as $langKey => $labelText) {
                            $LOCAL_LANG_ARRAY[$langKey]['label'] = $labelText;
                        }
                    }
                    $LOCAL_LANG_ARRAY['default']['label'] = $typeConfigArray['label'] ? $typeConfigArray['label'] : $typeConfigArray['label.']['default'];
                    $field['items'][] = array(

                        /** @var \TYPO3\CMS\Lang\LanguageService $GLOBALS['LANG'] */
                        $GLOBALS['LANG']->getLLL('label', $LOCAL_LANG_ARRAY),
                        substr($typeName, 0, -1)
                    );
                }
            }
        }
    }

    /**
     * @return int
     */
    protected function getCurrentPageId() {
        $pageId = (integer) GeneralUtility::_GP('id');
        if($pageId > 0) {
            return $pageId;
        }

        preg_match('/(?<=id=)[0-9]a/', urldecode(GeneralUtility::_GET('returnUrl')), $matches);
        if(count($matches) > 0) {
            return $matches[0];
        }

        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $GLOBALS['TYPO3_DB'] */
        $rootTemplates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pid', 'sys_template',
            'deleted=0 AND hidden=0 AND root=1', '', '', '1');

        if(count($rootTemplates) > 0) {
            return $rootTemplates[0]['pid'];
        }

        $rootPages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', 'pages',
            'deleted=0 AND hidden=0 AND is_siteroot=1', '', '', '1');

        if(count($rootPages) > 0) {
            return $rootPages[0]['uid'];
        }

        // take pid 1 as fallback
        return 1;
    }

}