<?php
	/*********************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 * Process gallery image directories
	 */
	class Tx_SpGallery_Task_DirectoryObserver extends tx_scheduler_Task {

		/**
		 * Find unprocessed image directories and generate images by configuration
		 *
		 * @return boolean TRUE if success
		 */
		public function execute() {
				// Find all directories
			$table  = 'tx_spgallery_domain_model_gallery';
			$where  = 'image_directory IS NOT NULL AND image_directory != ""';
			$where .= t3lib_BEfunc::BEenableFields($table);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,image_directory,image_directory_hash', $table, $where);
			if (empty($result)) {
				return TRUE;
			}

				// Get changed directories
			$changedDirectories = $this->getChangedDirectories($result);
			if (empty($changedDirectories)) {
				return TRUE;
			}

				// Generate images for changed directories
			$processedDirectories = $this->generateImages($changedDirectories);
			if (empty($processedDirectories)) {
				return TRUE;
			}

				// Save directory hashes
			foreach ($processedDirectories as $directory) {
				$fields = array('image_directory_hash' => $directory['image_directory_hash']);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid=' . (int)$directory['uid'], $fields);
			}

			return TRUE;
		}


		/**
		 * Look up directories for changed content
		 *
		 * @param array $directories Directory rows from table
		 * @return array Changed directories and their files
		 */
		protected function getChangedDirectories(array $directories) {
			$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
			$changedDirectories = array();

			foreach ($directories as $directory) {
					// Find recursive all image files
				$path  = $directory['image_directory'];
				$files = Tx_SpGallery_Utility_File::getFiles($path, TRUE, $allowedTypes);
				$hash  = md5(serialize($files));

				if ($hash !== $directory['image_directory_hash']) {
					$directory['files'] = $files;
					$directory['image_directory_hash'] = $hash;
					$changedDirectories[] = $directory;
				}
			}

			return $changedDirectories;
		}


		/**
		 * Generate images from given directories
		 *
		 * @param array $directories Image directories
		 * @return array Processed directories
		 */
		protected function generateImages(array $directories) {
			$processedDirectories = array();

				// Load plugin settings
			$settings = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.tx_spgallery.settings');
			$settings = Tx_SpGallery_Utility_TypoScript::parse($settings, FALSE);

				// Create required instances
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			$imageService  = $objectManager->get('Tx_SpGallery_Service_GalleryImage');

				// Define image sizes
			$imageSizes = array('teaser', 'thumb', 'small', 'large');

				// Simulate working directory "htdocs", required for file_exists check
				// in t3lib_stdGraphic::getImageDimensions
			$currentDir = getcwd();
			chdir(PATH_site);

				// Generate images for each directory
			foreach ($directories as $directory) {
				if (empty($directory['files'])) {
					continue;
				}
				foreach ($imageSizes as $size) {
					if (empty($settings[$size . 'Image'])) {
						continue;
					}

					$files = $directory['files'];

						// Generate only defined count of files for teaser view
					if ($size === 'teaser' && !empty($settings['teaserImageCount'])) {
						$files = reset(array_chunk($files, (int)$settings['teaserImageCount']));
					}

					$imageService->processImageFiles($files, $settings[$size . 'Image']);
				}
				$processedDirectories[] = $directory;
			}

				// Revert working directory
			chdir($currentDir);

			return $processedDirectories;
		}

	}
?>