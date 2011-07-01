<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

		// Add plugin to list
	Tx_Extbase_Utility_Extension::registerPlugin(
		$_EXTKEY,
		'Gallery',
		'Gallery'
	);

		// Add flexform to field list of the backend form
	$identifier = str_replace('_', '', $_EXTKEY) . '_gallery';
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$identifier] = 'layout,select_key,recursive,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$identifier] = 'pi_flexform';
	t3lib_extMgm::addPiFlexFormValue($identifier, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Gallery.xml');

		// Add static TypoScript files
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Gallery Configuration');

		// Add help text to the backend form
	t3lib_extMgm::addLLrefForTCAdescr('tx_spgallery_domain_model_gallery', 'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_csh_tx_spgallery_domain_model_gallery.xml');

		// Allow datasets on standard pages
	t3lib_extMgm::allowTableOnStandardPages('tx_spgallery_domain_model_gallery');

		// Add table configuration
	$TCA['tx_spgallery_domain_model_gallery'] = array(
		'ctrl' => array(
			'title'                    => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_gallery',
			'label'                    => 'name',
			'tstamp'                   => 'tstamp',
			'crdate'                   => 'crdate',
			'cruser_id'                => 'cruser_id',
			'dividers2tabs'            => TRUE,
			'versioningWS'             => 2,
			'versioning_followPages'   => TRUE,
			'origUid'                  => 't3_origuid',
			'languageField'            => 'sys_language_uid',
			'transOrigPointerField'    => 'l10n_parent',
			'transOrigDiffSourceField' => 'l10n_diffsource',
			'delete'                   => 'deleted',
			'enablecolumns'            => array(
				'disabled'                 => 'hidden',
				'starttime'                => 'starttime',
				'endtime'                  => 'endtime',
			),
			'dynamicConfigFile'        => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Gallery.php',
			'iconfile'                 => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
		),
	);

		// Add plugin to new content element wizard
	t3lib_extMgm::addPageTSConfig("
		mod.wizards.newContentElement.wizardItems.special {\n
			elements." . $identifier . " {\n
				icon        = " . t3lib_extMgm::extRelPath($_EXTKEY) . "Resources/Public/Images/Wizard.gif\n
				title       = LLL:EXT:" . $_EXTKEY . "/Resources/Private/Language/locallang.xml:newContentElement.wizardItem.title\n
				description = LLL:EXT:" . $_EXTKEY . "/Resources/Private/Language/locallang.xml:newContentElement.wizardItem.description\n\n
				tt_content_defValues {\n
					CType = list\n
					list_type = " . $identifier . "\n
				}\n
			}\n\n
			show := addToList(" . $identifier . ")\n
		}
	");
?>