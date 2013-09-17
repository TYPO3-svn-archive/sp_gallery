<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$GLOBALS['TCA']['tx_spgallery_domain_model_image'] = array(
		'ctrl'      => $GLOBALS['TCA']['tx_spgallery_domain_model_image']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, description, information',
		),
		'types' => array(
			'1'     => array(
				'showitem' => 'hidden, name, description, information',
			),
		),
		'palettes' => array(
			'1'        => array(
				'showitem'  => '',
			),
		),
		'columns' => array(
			'sys_language_uid' => array(
				'config'          => array(
					'type'            => 'passthrough',
				),
			),
			'l10n_parent' => array(
				'config'      => array(
					'type'        => 'passthrough',
				),
			),
			'l10n_diffsource' => array(
				'config'          => array(
					'type'            => 'passthrough',
				),
			),
			't3ver_label' => array(
				'config'      => array(
					'type'        => 'passthrough',
				),
			),
			'hidden' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
				'config'  => array(
					'type'    => 'check',
				),
			),
			'name' => array(
				'exclude' => 0,
				'label'   => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_image.name',
				'config'  => array(
					'type'    => 'input',
					'size'    => 30,
					'eval'    => 'trim',
					'localizeReferencesAtParentLocalization' => 1,
					'localizeReferences'                     => 1,
				),
			),
			'description' => array(
				'exclude'     => 1,
				'label'       => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_image.description',
				'config'      => array(
					'type'        => 'text',
					'cols'        => 32,
					'rows'        => 5,
					'localizeReferencesAtParentLocalization' => 1,
					'localizeReferences'                     => 1,
				),
			),
			'file_name' => array(
				'config'    => array(
					'type'      => 'passthrough',
				),
			),
			'file_size' => array(
				'config'    => array(
					'type'      => 'passthrough',
				),
			),
			'file_type' => array(
				'config'    => array(
					'type'      => 'passthrough',
				),
			),
			'image_height' => array(
				'config'       => array(
					'type'         => 'passthrough',
				),
			),
			'image_width' => array(
				'config'      => array(
					'type'        => 'passthrough',
				),
			),
			'gallery' => array(
				'config'  => array(
					'type'    => 'passthrough',
				),
			),
			'information' => array(
				'exclude'      => 1,
				'l10n_mode'    => 'exclude',
				'l10n_display' => 'defaultAsReadonly',
				'label'        => '',
				'displayCond'  => 'FIELD:file_name:REQ:true', // Show if file name is not empty
				'config'       => array(
					'type'         => 'user',
					//'userFunc'     => 'Tx_SpGallery_Hook_Tca->renderImageInformation',
					'userFunc' 	   => '\\Speedprogs\\SpGallery\\Hook\\Tca->renderImageInformation',
					'labels'       => array(
						'file_name'    => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_image.file_name',
						'file_size'    => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_image.file_size',
						'file_type'    => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_image.file_type',
						'image_width'  => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_image.image_width',
						'image_height' => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_image.image_height',
					),
				),
			),
		),
	);
?>