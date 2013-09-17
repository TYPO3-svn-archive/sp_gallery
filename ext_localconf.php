<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

	// Make plugin available in frontend
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Speedprogs.' . $_EXTKEY,
		'Gallery',
		array(
			'Gallery' => 'show, list, teaser, teaserList, new, create, edit, update',
		),
		array(
			'Gallery' => 'new, create, edit, update',
		)
	);

	// Add scheduler task for image processing
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Speedprogs\\SpGallery\\Task\\DirectoryObserver'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_spgallery_task_directoryobserver.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_spgallery_task_directoryobserver.description',
		'additionalFields' => 'Speedprogs\\SpGallery\\Task\\DirectoryObserverAdditionalFieldProvider',
	);

	// Hook implementation to generate images when saving a gallery
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
		'Speedprogs\\SpGallery\\Hook\\TceMain';

?>