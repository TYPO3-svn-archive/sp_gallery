<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

		// Make plugin available in frontend
	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Gallery',
		array(
			'Gallery' => 'show, list, teaser, teaserList',
		),
		array(
			'Gallery' => '',
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
	$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['t3lib/class.t3lib_tceforms_inline.php'] = t3lib_extMgm::extPath('sp_gallery', 'Classes/Hook/UxT3libTceformsInline.php');

?>