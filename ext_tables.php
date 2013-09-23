<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * Add plugin to list
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Speedprogs.' . $_EXTKEY,
	'Gallery',
	'Gallery'
);

/**
 * Add new columns to the file collection table
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_collection', array(
	'description' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xlf:tx_spgallery_domain_model_gallery.description',
		'config' => array(
			'type' => 'text',
			'cols' => 40,
			'rows' => 15,
			'eval' => 'trim',
			'wizards' => array(
				'RTE' => array(
					'icon' => 'wizard_rte2.gif',
					'notNewRecords' => 1,
					'RTEonly' => 1,
					'script' => 'wizard_rte.php',
					'title' => 'LLL:EXT:cms/locallang_ttc.xlf:bodytext.W.RTE',
					'type' => 'script'
				)
			)
		),
		'defaultExtras' => 'richtext:rte_transform[flag=rte_enabled|mode=ts]',
	),
	'images' => array(
		'exclude'      => 1,
		'l10n_mode'    => 'exclude',
		'l10n_display' => 'hideDiff',
		'label'        => 'LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xlf:tx_spgallery_domain_model_gallery.images',
		'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
			'images',
			array(
				'apperance' => array(
					'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
				),
			), $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
		),
	),
));

/**
 * Add new fields to file collection backend mask
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_file_collection',
	'description',
	'',
	'after:title'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_file_collection',
	'--div--;LLL:EXT:sp_gallery/Resources/Private/Language/locallang_db.xlf:tab.images, images'
);

/**
 * Use tabs in file collection backend mask
 */
$GLOBALS['TCA']['sys_file_collection']['ctrl']['dividers2tabs'] = TRUE;

/**
 * Add FlexForm to plugin mask
 */
$identifier = str_replace('_', '', $_EXTKEY) . '_gallery';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$identifier] = 'layout,select_key,recursive,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$identifier] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($identifier, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Gallery.xml');

/**
 * Load extension configuration
 */
$configuration = \Speedprogs\SpGallery\Utility\BackendUtility::getExtensionConfiguration($_EXTKEY);

/**
 * Add plugin to new content element wizard
 */
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

/**
 * Add sprite icons
 */
$iconFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY)  . 'ext_icons.php';
if (file_exists($iconFile)) {
	\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(require($iconFile), str_replace('_', '', $_EXTKEY));
}

?>