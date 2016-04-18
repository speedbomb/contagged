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
$TCA["tx_contagged_terms"] = array(
	"ctrl" => array(
        'title'	=> 'LLL:EXT:contagged/Resources/Private/Language/locallang.xlf:tx_contagged_terms',
		'label' => 'term_main',
		'label_alt' => 'term_alt,term_replace',
		'label_alt_force' => true,
		'searchFields' => 'term_main,term_alt,desc_short,desc_long,term_replace',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => 2,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		#'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY term_main ASC',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'useColumnsForDefaultValues' => 'term_type',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Term.php',
		'iconfile' => 'EXT:contagged/Resources/Public/Icons/Term.gif',
	),
	"feInterface" => array(
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, term_main, term_alt, term_type, term_lang, term_replace, desc_short, desc_long, image, dam_images, imagecaption, imagealt, imagetitle, related, link, exclude",
	)
);

// Add a field  "exclude this page from parsing" to the table "pages" and "tt_content"
$tempColumns = Array(
	"tx_contagged_dont_parse" => Array(
		"exclude" => 1,
		"label" => "LLL:EXT:contagged/locallang_db.xml:pages.tx_contagged_dont_parse",
		"config" => Array(
			"type" => "check",
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("pages", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("pages", "tx_contagged_dont_parse;;;;1-1-1");

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_content", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("tt_content", "tx_contagged_dont_parse;;;;1-1-1");

?>