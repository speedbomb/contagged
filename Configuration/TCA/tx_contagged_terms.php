<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$langFile = 'LLL:EXT:contagged/Resources/Private/Language/locallang.xlf:';

return array(
    'ctrl' => array(
        'title' => $langFile . 'tx_contagged_terms',
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
        'iconfile' => 'EXT:contagged/Resources/Public/Icons/Term.gif',
    ),
    'interface' => array(
        'showRecordFieldList' => 'sys_language_uid, starttime, endtime, term_main, term_alt, term_type, term_lang, replacement, desc_short, desc_long, reference, pronunciation, image, dam_images,imagecaption, imagealt, imagetitle, multimedia, related, link, exclude'
    ),
    'feInterface' => array(
        'fe_admin_fieldList' => 'sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, term_main, term_alt, term_type, term_lang, term_replace, desc_short, desc_long, image, dam_images, imagecaption, imagealt, imagetitle, related, link, exclude'
    ),
	'columns' => array(
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			)
		),
        'sys_language_uid' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => array(
                    array(
                        'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ),
                ),
                'default' => 0,
                'showIconTable' => 1,
            )
        ),
        'l18n_parent' => array(
            'exclude' => 1,
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array('', 0)
                ),
                'foreign_table' => 'tx_contagged_terms',
                'foreign_table_where' => 'AND tx_contagged_terms.pid=###CURRENT_PID### AND tx_contagged_terms.sys_language_uid IN (-1,0)',
                'default' => 0
            )
        ),
		'l18n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough'
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
        'starttime' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => array(
                'type' => 'input',
                'size' => '13',
                'eval' => 'datetime',
                'default' => 0
            ),
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ),
        'endtime' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => array(
                'type' => 'input',
                'size' => '13',
                'eval' => 'datetime',
                'default' => 0,
                'range' => array(
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                )
            ),
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ),
        'fe_group' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.fe_group',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 5,
                'maxitems' => 20,
                'items' => array(
                    array(
                        'LLL:EXT:lang/locallang_general.xlf:LGL.hide_at_login',
                        -1
                    ),
                    array(
                        'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
                        -2
                    ),
                    array(
                        'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
                        '--div--'
                    )
                ),
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title'
            )
        ),
		'term_main' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.term_main',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'term_alt' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.term_alt',
			'config' => Array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'term_type' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.term_type',
			'config' => Array(
				'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Speedbomb\Contagged\Userfuncs\Tca::class . '->addTermTypes',
    		),
		),
		'term_lang' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.term_lang',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'showIconTable' => 1,
                'default' => 0,
            )
		),
		'term_replace' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.term_replace',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'desc_short' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.desc_short',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'desc_long' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.desc_long',
            'defaultExtras' => 'richtext[]',
			'config' => Array(
				'type' => 'text',
				'cols' => '80',
				'rows' => '10',
                'wizards' => array(
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly' => 1,
                        'type' => 'script',
                        'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext.W.RTE',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif',
                        'module' => array(
                            'name' => 'wizard_rte'
                        )
                    ),
                ),

			)
		),
		'reference' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.reference',
			'config' => Array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '2',
			)
		),
		'pronunciation' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.pronunciation',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'multimedia' => Array(
			'label' => $langFile . 'tx_contagged_terms.multimedia',
			'config' => Array(
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'swf,swa,dcr,wav,avi,au,mov,asf,mpg,wmv,mp3,mp4,m4v',
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/media',
				'size' => '2',
				'maxitems' => '1',
				'minitems' => '0'
			)
		),
		'related' => Array(
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => $langFile . 'tx_contagged_terms.related',
			'config' => Array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_contagged_terms',
				'MM' => 'tx_contagged_related_mm',
				'show_thumbs' => 1,
				'size' => 3,
				'autoSizeMax' => 10,
				'maxitems' => 9999,
				'minitems' => 0,
                'wizards' => array(
                    'suggest' => array(
                        'type' => 'suggest',
                        'default' => array(
                            'additionalSearchFields' => 'desc_short',
                            'searchWholePhrase' => 0,
                        )
                    ),
                ),
			)
		),
		'link' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.link',
			'config' => Array(
				'type' => 'input',
				'size' => '28',
				'max' => '511',
				'checkbox' => '',
				'eval' => 'trim',
				'wizards' => array(
					'link' => array(
						'type' => 'popup',
						'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
						'icon' => 'link_popup.gif',
						'module' => array(
							'name' => 'wizard_element_browser',
							'urlParameters' => array(
								'mode' => 'wizard'
							)
						),
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				),
				'softref' => 'typolink'
			)
		),
		'exclude' => Array(
			'exclude' => 1,
			'label' => $langFile . 'tx_contagged_terms.exclude',
			'config' => Array(
				'type' => 'check',
			)
		),
		'image' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.images',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('image', array(
				'appearance' => array(
					'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
				),
				'foreign_types' => array(
					'0' => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					)
				),
			), $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'])
		),
	),
	'types' => array(
		//'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, term_main, term_alt, term_type, term_lang, term_replace, desc_short, desc_long;;;richtext[*]:rte_transform[mode=ts_css|imgpath=uploads/tx_contagged/rte/], reference, pronunciation, image, dam_images, imagecaption, imagealt, imagetitle, multimedia, related, link, exclude')

		'1' => array(
            'showitem' => '
                  term_main, term_alt, term_type, term_replace, desc_short, desc_long,
                --div--;' . $langFile . 'tab.details,
                  reference, link, related, pronunciation,
                  --palette--;' . $langFile . 'palette.language;language,
                --div--;' . $langFile . 'tab.details,
                  image,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
                  --palette--;' . $langFile . 'palette.access;access,
                  fe_group
            '
        )
	),
	'palettes' => array(
        'language' => array(
            'showitem' => '
                sys_language_uid, term_lang,
                --linebreak--,
                l18n_parent, l18n_diffsource
            ',
        ),
        'access' => array(
            'showitem' => '
                hidden, exclude,
                --linebreak--,
                starttime, endtime
            ',
        ),
	)
);

/*
$extConfArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['contagged']);
if ($extConfArray['getImagesFromDAM'] > 0 && t3lib_extMgm::isLoaded('dam')) {
	$TCA['tx_contagged_terms']['columns'] = array_merge_recursive($TCA['tx_contagged_terms']['columns'], array(
			'dam_images' => txdam_getMediaTCA('image_field', 'dam_images')
		)
	);
} else {
	$TCA['tx_contagged_terms']['columns'] = array_merge_recursive($TCA['tx_contagged_terms']['columns'], array(
			'image' => Array(
				'exclude' => 1,
				'l10n_mode' => $l10n_mode_image,
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.images',
				'config' => Array(
					'type' => 'group',
					'internal_type' => 'file',
					'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
					'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
					'uploadfolder' => 'uploads/pics',
					'show_thumbs' => '1',
					'size' => 3,
					'autoSizeMax' => 15,
					'maxitems' => '99',
					'minitems' => '0'
				)
			),
			'imagecaption' => Array(
				'exclude' => 1,
				'label' => $langFile . 'tx_contagged_terms.imagecaption',
				'l10n_mode' => $l10n_mode,
				'config' => Array(
					'type' => 'text',
					'cols' => '30',
					'rows' => '3'
				)
			),
			'imagealt' => Array(
				'exclude' => 1,
				'label' => $langFile . 'tx_contagged_terms.imagealt',
				'l10n_mode' => $l10n_mode,
				'config' => Array(
					'type' => 'text',
					'cols' => '20',
					'rows' => '3'
				)
			),
			'imagetitle' => Array(
				'exclude' => 1,
				'label' => $langFile . 'tx_contagged_terms.imagetitle',
				'l10n_mode' => $l10n_mode,
				'config' => Array(
					'type' => 'text',
					'cols' => '20',
					'rows' => '3'
				)
			),
		)
	);
}
*/