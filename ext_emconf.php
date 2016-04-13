<?php

/***************************************************************
* Extension Manager/Repository config file for ext "contagged".
*
* Manual updates:
* Only the data in the array - everything else is removed by next
* writing. "version" and "dependencies" must not be touched!
***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Content parser and tagger (Glossary)',
	'description' => 'This extension parses your content to tag, replace and link specific terms. It is useful to auto-generate a glossary - but not only.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '2.0.0-dev',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_contagged/rte/',
	'modify_tables' => 'tt_content,pages',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Marco Schrepfer, Jochen Rau',
	'author_email' => 'typo3@speedbomb.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.6.0-7.6.99',
			'php' => '5.4.0',
			'extbase' => '7.6.0',
      		'fluid' => '7.6.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);