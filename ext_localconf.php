<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

	// Make plugin available in frontend
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Speedprogs.'.$_EXTKEY,
		'Gallery',
		array(
			'Gallery' => 'show, list, teaser, teaserList, new, create, edit, update',
		),
		array(
			'Gallery' => 'new, create, edit, update',
		)
	);

	// Add scheduler task for image processing
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_SpGallery_Task_DirectoryObserver'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_spgallery_task_directoryobserver.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_spgallery_task_directoryobserver.description',
		'additionalFields' => 'tx_spgallery_task_directoryobserveradditionalfieldprovider',
	);

	// Add XCLASS for TCA fields of type "inline"
	$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['t3lib/class.t3lib_tceforms_inline.php'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sp_gallery', 'Classes/Hook/UxT3libTceformsInline.php');

	// Hook implementation to generate images when saving a gallery
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:sp_gallery/Classes/Hook/TceMain.php:&Tx_SpGallery_Hook_TceMain';

?>