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
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_SpGallery_Task_DirectoryListener'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:Tx_SpGallery_Task_DirectoryListener.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:Tx_SpGallery_Task_DirectoryListener.description',
		'additionalFields' => '',
	);
?>