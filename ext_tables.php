<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// PlugIn
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Glossary',
	'Contagged Glossary'
);

// PlugIn Wizard Icon in BE
if(TYPO3_MODE == 'BE') {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['contaggedGlossaryWizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/Php/class.contaggedGlossaryWizicon.php';
}

// TypoScript Config
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Contagged Glossary');

// Terms table definition
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_contagged_terms');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_contagged_terms');

// Add a field  "exclude this page from parsing" to the table "pages" and "tt_content"
$tempColumns = Array(
	"tx_contagged_dont_parse" => Array(
		"exclude" => 1,
		"label" => "LLL:EXT:contagged/Resources/Private/Language/locallang.xlf:tx_contagged_dont_parse",
		"config" => Array(
			"type" => "check",
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("pages", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("pages", "tx_contagged_dont_parse;;;;1-1-1");

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_content", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("tt_content", "tx_contagged_dont_parse;;;;1-1-1");