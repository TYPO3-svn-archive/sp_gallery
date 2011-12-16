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
	$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$identifier] = 'layout,select_key,recursive,pages';
	$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$identifier] = 'pi_flexform';
	t3lib_extMgm::addPiFlexFormValue($identifier, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Gallery.xml');

		// Add static TypoScript files
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Gallery Configuration');

		// Load extension configuration
	$configuration = array();
	if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY])) {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
	}

	foreach (array('gallery', 'image') as $model) {
			// Add help text to the backend form
		t3lib_extMgm::addLLrefForTCAdescr('tx_spgallery_domain_model_' . $model, 'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_csh_' . $model . '.xml');

			// Allow datasets on standard pages
		t3lib_extMgm::allowTableOnStandardPages('tx_spgallery_domain_model_' . $model);

			// Add table configuration
		$GLOBALS['TCA']['tx_spgallery_domain_model_' . $model] = array(
			'ctrl' => array(
				'title'                    => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xml:tx_spgallery_domain_model_' . $model,
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
				'default_sortby'           => 'ORDER BY crdate',
				'sortby'                   => ($model === 'gallery' && $configuration['gallerySortingType'] === 'plugin' ? '' : 'sorting'),
				'enablecolumns'            => array(
					'disabled'                 => 'hidden',
					'starttime'                => 'starttime',
					'endtime'                  => 'endtime',
				),
				'dynamicConfigFile'        => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/' . ucfirst($model) . '.php',
				'iconfile'                 => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Images/' . ucfirst($model) . '.gif',
				'hideTable'                => (int) ($model === 'image'),
			),
		);
	}

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