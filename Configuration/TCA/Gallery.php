<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$TCA['tx_spgallery_domain_model_gallery'] = array(
		'ctrl'      => $TCA['tx_spgallery_domain_model_gallery']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, description, image_directory, image_directory_hash',
		),
		'types' => array(
			'1'     => array(
				'showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, description;;2;richtext:rte_transform[flag=rte_enabled|mode=ts];4-4-4, image_directory,--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime',
			),
		),
		'palettes' => array(
			'1'        => array(
				'showitem' => '',
			),
		),
		'columns' => array(
			'sys_language_uid' => array(
				'exclude'          => 1,
				'label'            => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
				'config'           => array(
					'type'                => 'select',
					'foreign_table'       => 'sys_language',
					'foreign_table_where' => 'ORDER BY sys_language.title',
					'items'               => array(
						array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
						array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0),
					),
				),
			),
			'l10n_parent' => array(
				'displayCond' => 'FIELD:sys_language_uid:>:0',
				'exclude'     => 1,
				'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
				'config'      => array(
					'type'        => 'select',
					'foreign_table'       => 'tx_spgallery_domain_model_gallery',
					'foreign_table_where' => 'AND tx_spgallery_domain_model_gallery.pid=###CURRENT_PID### AND tx_spgallery_domain_model_gallery.sys_language_uid IN (-1,0)',
					'items'               => array(
						array('', 0),
					),
				),
			),
			'l10n_diffsource' => array(
				'config'          => array(
					'type'            => 'passthrough',
				),
			),
			't3ver_label' => array(
				'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
				'config'      => array(
					'type'        => 'input',
					'size'        => 30,
					'max'         => 255,
				)
			),
			'hidden' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
				'config'  => array(
					'type'    => 'check',
				),
			),
			'starttime' => array(
				'exclude'   => 1,
				'l10n_mode' => 'mergeIfNotBlank',
				'label'     => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
				'config'    => array(
					'type'      => 'input',
					'size'      => 13,
					'max'       => 20,
					'eval'      => 'datetime',
					'checkbox'  => 0,
					'default'   => 0,
					'range'     => array(
						'lower'     => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
					),
				),
			),
			'endtime' => array(
				'exclude'   => 1,
				'l10n_mode' => 'mergeIfNotBlank',
				'label'     => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
				'config'    => array(
					'type'      => 'input',
					'size'      => 13,
					'max'       => 20,
					'eval'      => 'datetime',
					'checkbox'  => 0,
					'default'   => 0,
					'range'     => array(
						'lower'     => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
					),
				),
			),
			'name' => array(
				'exclude' => 0,
				'label'   => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery.name',
				'config'  => array(
					'type'    => 'input',
					'size'    => 30,
					'eval'    => 'trim,required',
				),
			),
			'description' => array(
				'exclude'     => 1,
				'label'       => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery.description',
				'config'      => array(
					'type'        => 'text',
					'cols'        => 32,
					'rows'        => 5,
				),
			),
			'image_directory' => array(
				'exclude' => 0,
				'label'   => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery.image_directory',
				'config'  => array(
					'type'     => 'input',
					'size'     => 30,
					'eval'     => 'trim,required',
					'wizards'  => array(
						'_PADDING' => 2,
						'link'     => array(
							'type'   => 'popup',
							'icon'   => 'link_popup.gif',
							'script' => 'browse_links.php?mode=wizard&amp;act=file',
							'params' => array(
								'blindLinkOptions' => 'mail,page,spec,url,file',
							),
							'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
						),
					),
				),
			),
			'image_directory_hash' => array(
				'config' => array(
					'type'   => 'passthrough',
				),
			),
		),
	);
?>