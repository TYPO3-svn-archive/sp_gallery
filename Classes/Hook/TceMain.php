<?php
namespace Speedprogs\SpGallery\Hook;

/*********************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 *           Maik Hagenbruch <maik@hagenbru.ch>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published
 *  by the Free Software Foundation; either version 3 of the License,
 *  or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ********************************************************************/

/**
 * Hooks for t3lib_tcemain.php
 */
class TceMain implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var \Speedprogs\SpGallery\Service\GalleryService
	 */
	protected $galleryService = NULL;

	/**
	 * Get extension configuration
	 *
	 * @return void
	 */
	public function __construct() {
		$this->configuration = \Speedprogs\SpGallery\Utility\BackendUtility::getExtensionConfiguration('sp_gallery');
	}

	/**
	 * Return an instance of the gallery service
	 *
	 * @param integer $pid Current page id
	 * @return \Speedprogs\SpGallery\Service\GalleryService
	 */
	protected function getGalleryService($pid) {
		if ($this->galleryService === NULL) {
			$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			$typoScriptService = $objectManager->get('Speedprogs\\SpGallery\\Service\\TypoScriptService');
			$setup = $typoScriptService->getSetupForPid($pid, 'plugin.tx_spgallery');
			$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
			$configurationManager->setConfiguration($setup);
			$this->galleryService = $objectManager->get('Speedprogs\\SpGallery\\Service\\GalleryService');
			$this->galleryService->setStoragePid($pid);
		}
		return $this->galleryService;
	}

	/**
	 * Generate images when saving a gallery
	 *
	 * @param string $status Status of the current operation
	 * @param string $table The table currently processing data for
	 * @param string $uid The record uid currently processing data for
	 * @param array $fields The field array of the record
	 * @param t3lib_TCEmain $parent Reference to calling object
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $uid, &$fields, &$parent) {
		if (empty($this->configuration['generateWhenSaving'])) {
			return;
		}
		// Check record type
		if ($table !== 'tx_spgallery_domain_model_gallery') {
			return;
		}
		// Return if no valid directory was found
		if (!empty($fields['file_storage'])) {

			// TODO

		}
		// Get record uid
		if ($status === 'new') {
			$uid = $parent->substNEWwithIDs[$uid];
		} else if ($status === 'copy') {
			$uid = $parent->copyMappingArray[$table][$uid];
		}
		$galleryService = $this->getGalleryService((int) $parent->getPID($table, $uid));
		// Process gallery by uid
		$generateNames = !empty($this->configuration['generateNameWhenSaving']);
		$galleryService->processByUid($uid, $generateNames);
	}

}
?>