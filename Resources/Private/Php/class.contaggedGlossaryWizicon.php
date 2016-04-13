<?php

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

/**
 * BE plugin wizard icon.
 *
 * @author Marco Schrepfer <typo3@speedbomb.de>
 * @package contagged
 */
class contaggedGlossaryWizicon {

	/**
	 * Processing the wizard items array
	 *
	 * @param array $wizardItems The wizard items
	 * @return array Modified array with wizard items
	 */
	function proc($wizardItems)     {
		$wizardItems['plugins_tx_contagged_glossary'] = array(
			'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('contagged') . 'Resources/Public/Icons/plugin.png',
			'title' => $GLOBALS['LANG']->sL('LLL:EXT:contagged/Resources/Private/Language/locallang.xlf:contagged.wizard_title'),
			'description' => $GLOBALS['LANG']->sL('LLL:EXT:contagged/Resources/Private/Language/locallang.xlf:contagged.wizard_description'),
			'params' => '&defVals[tt_content][CType]=list&&defVals[tt_content][list_type]=contagged_glossary'
		);

		return $wizardItems;
	}
}
