<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

		// Add plugin to list
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		'Speedprogs.' . $_EXTKEY,
		'Gallery',
		'Gallery'
	);

		// Add flexform to field list of the backend form
	$identifier = str_replace('_', '', $_EXTKEY) . '_gallery';
	$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$identifier] = 'layout,select_key,recursive,pages';
	$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$identifier] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($identifier, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Gallery.xml');

		// Add static TypoScript files
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Gallery Configuration');

		// Load extension configuration
	$configuration = \Speedprogs\SpGallery\Utility\BackendUtiliy::getExtensionConfiguration($_EXTKEY);

	foreach (array('gallery', 'image') as $model) {
			// Add help text to the backend form
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_spgallery_domain_model_' . $model, 'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_csh_' . $model . '.xml');

			// Allow datasets on standard pages
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_spgallery_domain_model_' . $model);

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
				'dynamicConfigFile'        => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/' . ucfirst($model) . '.php',
				'iconfile'                 => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Images/' . ucfirst($model) . '.gif',
				'hideTable'                => (int) ($model === 'image'),
			),
		);
	}

		// Add plugin to new content element wizard
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig("
		mod.wizards.newContentElement.wizardItems.special {\n
			elements." . $identifier . " {\n
				icon        = " . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . "Resources/Public/Images/Wizard.gif\n
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

		// Add sprite icons
	$iconFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY)  . 'ext_icons.php';
	if (file_exists($iconFile)) {
		\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(require($iconFile), str_replace('_', '', $_EXTKEY));
	}

?>