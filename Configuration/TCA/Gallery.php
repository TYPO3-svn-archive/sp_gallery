<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$GLOBALS['TCA']['tx_spgallery_domain_model_gallery'] = array(
		'ctrl'      => $GLOBALS['TCA']['tx_spgallery_domain_model_gallery']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, description, file_storage, system_message, images',
		),
		'types' => array(
			'1'     => array(
				'showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, description;;2;richtext:rte_transform[flag=rte_enabled|mode=ts];4-4-4, file_storage, --div--;LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tab.images, system_message, images, --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime',
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
			'file_storage' => array(
				'exclude'      => 1,
				'l10n_mode'    => 'exclude',
				'l10n_display' => 'defaultAsReadonly',
				'label'        => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery.file_storage',
				'config'       => array(
					'type'                => 'select',
					'foreign_table'       => 'sys_file_storage',
					'foreign_table_where' => 'AND sys_file_storage.deleted = 0 AND sys_file_storage.hidden = 0',
					'size'                => 1,
					'minitems'            => 1,
					'maxitems'            => 1,
				),
			),
			'images' => array(
				'exclude'     => 1,
				'label'       => '',
				'displayCond' => 'FIELD:images:REQ:true', // Hide if empty
				'config'  => array(
					'type'           => 'inline',
					'foreign_table'  => 'tx_spgallery_domain_model_image',
					'foreign_field'  => 'gallery',
					'foreign_sortby' => 'sorting',
					'foreign_label'  => 'name',
					'maxitems'       => 9999,
					'appearance'     => array(
						'expandSingle'                    => TRUE,
						'levelLinksPosition'              => 'none',
						'showSynchronizationLink'         => FALSE,
						'showPossibleLocalizationRecords' => FALSE,
						'showAllLocalizationLink'         => FALSE,
						'useSortable'                     => TRUE,
						'renderItemImage'                 => TRUE,
						'enabledControls'                 => array(
							'info'                            => FALSE,
							'new'                             => FALSE,
							'dragdrop'                        => TRUE,
							'sort'                            => TRUE,
							'hide'                            => FALSE,
							'delete'                          => FALSE,
							'localize'                        => FALSE,
						),
					),
					'behaviour'      => array(
						'localizationMode'                     => 'select',
						'localizeChildrenAtParentLocalization' => 1,
					),
					'itemImage'      => array(
						'foreign_field'  => 'file_name',
						'height'         => '70c',
						'width'          => '100c',
					),
				),
			),
			'system_message' => array(
				'exclude'      => 1,
				'l10n_mode'    => 'exclude',
				'l10n_display' => 'hideDiff',
				'label'        => '',
				'displayCond'  => 'FIELD:images:REQ:false', // Show if images field is empty
				'config'       => array(
					'type'         => 'user',
					'userFunc' => 'Speedprogs\\SpGallery\\Hook\\Tca->renderEmptyImagesMessage',
					'labels'       => array(
						'message_disabled'  => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery.message_disabled',
						'message_scheduler' => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery.message_scheduler',
						'message_saving'    => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery.message_saving',
					),
				),
			),
		),
	);
?>