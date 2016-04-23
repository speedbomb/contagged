<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// PlugIn
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Speedbomb.' . $_EXTKEY,
	'Glossary',
    // cacheable actions
	array(
		'Glossary' => 'list,detail'
	),
	// non-cacheable actions
	array(
		'Glossary' => 'list,detail'
	)
);

// Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] = \Speedbomb\Contagged\Service\ParserService::class . '->pageParser';