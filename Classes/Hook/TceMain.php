<?php
	/*********************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2012 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	class Tx_SpGallery_Hook_TceMain implements t3lib_Singleton {

		/**
		 * @var array
		 */
		protected $extensionConfiguration = array();

		/**
		 * @var Tx_SpGallery_Service_GalleryService
		 */
		protected $galleryService = NULL;


		/**
		 * Get extension configuration
		 *
		 * @return void
		 */
		public function __construct() {
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sp_gallery'])) {
				$this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sp_gallery']);
			}
		}


		/**
		 * Return an instance of the gallery service
		 *
		 * @return Tx_SpGallery_Service_GalleryService
		 */
		protected function getGalleryService() {
			if ($this->galleryService === NULL) {
				$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
				$configuration = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.tx_spgallery');
				$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
				$configurationManager->setConfiguration($configuration);
				$this->galleryService = $objectManager->get('Tx_SpGallery_Service_GalleryService');
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
			if (empty($this->extensionConfiguration['generateWhenSaving'])) {
				return;
			}

				// Check record type
			if ($table !== 'tx_spgallery_domain_model_gallery') {
				return;
			}

				// Return if image_directory has not been changed
			if (empty($fields['image_directory']) || empty($fields['tstamp'])) {
				return;
			}

				// Return if no valid directory is defined
			$fileName = t3lib_div::getFileAbsFileName($fields['image_directory']);
			if (!Tx_SpGallery_Utility_File::fileExists($fileName)) {
				return;
			}

				// Get record uid
			if ($status === 'new') {
				$uid = $parent->substNEWwithIDs[$uid];
			}

				// Set new storagePid for persistence handling
			$galleryService = $this->getGalleryService();
			$pid = $parent->getPID($table, $uid);
			if (!empty($pid)) {
				$galleryService->setStoragePid($pid);
			}

				// Get gallery by uid
			$gallery = $galleryService->getGallery($uid);
			if (empty($gallery)) {
				return;
			}

				// Remove all existing images records from old directory
			$galleryService->removeAllImages($gallery);

				// Generate image records from new directory
			$generateNames = !empty($this->extensionConfiguration['generateNameWhenSaving']);
			$galleryService->process($gallery, $generateNames);
		}

	}
?>