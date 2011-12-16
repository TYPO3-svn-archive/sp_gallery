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
	 * Service for gallery images
	 */
	class Tx_SpGallery_Service_GalleryImage implements t3lib_Singleton {

		/**
		 * @var tslib_cObj
		 */
		protected $contentObject;


		/**
		 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
			$this->contentObject = $configurationManager->getContentObject();
		}


		/**
		 * Converts all images with given settings
		 *
		 * @param array $files All files to process
		 * @param array $settings Image configuration
		 * @param boolean $tag Returns images with complete tag
		 * @return array Relative image paths
		 */
		public function processImageFiles(array $files, array $settings = array(), $tag = FALSE) {
			if (empty($files)) {
				return array();
			}

			if (empty($settings)) {
				return $files;
			}

				// Get allowed file types
			$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];

				// Simulate working directory "htdocs", required for file_exists check
				// in t3lib_stdGraphic::getImageDimensions
			$currentDir = getcwd();
			chdir(PATH_site);

				// Process images
			foreach ($files as $key => $file) {
					// Check if converting is allowed for this file type
				$fileType = Tx_SpGallery_Utility_File::getFileType($file);
				if (!t3lib_div::inList($allowedTypes, $fileType)) {
					unset($files[$key]);
					continue;
				}

					// Get relative path
				$file = str_replace(PATH_site, '', $file);

					// Modify image
				if (!empty($settings) && !$tag) {
					$info = $this->contentObject->getImgResource($file, $settings);
					$file = (!empty($info[3]) ? $info[3] : $file);
				} else if ($tag) {
					$file = $this->contentObject->cImage($file, array('file.' => $settings));
				}

				$files[$key] = $file;
			}

				// Revert working directory
			chdir($currentDir);

			return $files;
		}

	}
?>