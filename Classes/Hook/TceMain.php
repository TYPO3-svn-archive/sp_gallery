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
		 * @var Tx_SpGallery_Service_GalleryService
		 */
		protected $galleryService;


		/**
		 * Initialize the environment
		 * 
		 * @return void
		 */
		protected function initialize() {
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			$configuration = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.tx_spgallery');
			$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
			$configurationManager->setConfiguration($configuration);
			$this->galleryService = $objectManager->get('Tx_SpGallery_Service_GalleryService');
		}


		/**
		 * Generate images when saving a gallery
		 *
		 * @param string $status Status of the current operation, 'new' or 'update
		 * @param string $table The table currently processing data for
		 * @param string $uid The record uid currently processing data for
		 * @param array $fields The field array of the record
		 * @param t3lib_TCEmain $parent Reference to calling object
		 * @return void
		 */
		public function processDatamap_afterDatabaseOperations($status, $table, $uid, &$fields, &$parent) {
			if ($table !== 'tx_spgallery_domain_model_gallery') {
				return;
			}

				// Get whole record row
			if (!empty($parent->datamap[$table][$uid])) {
				$fields = $parent->datamap[$table][$uid];
			}

				// Return if no valid directory is defined
			if (empty($fields['image_directory'])) {
				return;
			}
			$fileName = t3lib_div::getFileAbsFileName($fields['image_directory']);
			if (!Tx_SpGallery_Utility_File::fileExists($fileName)) {
				return;
			}

				// Get record uid
			if ($status === 'new') {
				$uid = $parent->substNEWwithIDs[$uid];
			}

				// Initialize the environment
			$this->initialize();

				// Set new storagePid for persistence handling
			$pid = $parent->getPID($table, $uid);
			if (!empty($pid)) {
				$this->galleryService->setStoragePid($pid);
			}

				// Generate images
			$this->galleryService->processByUid($uid);
		}

	}
?>