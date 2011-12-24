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
		 * @var string
		 */
		protected $currentDir;


		/**
		 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
			$this->contentObject = $configurationManager->getContentObject();
		}


		/**
		 * Generate images from given files
		 *
		 * @param array $files Image files
		 * @param array $settings TypoScript configuration
		 * @return array Resulting image files
		 */
		public function generateImageFiles(array $files, array $settings) {
			if (empty($files) || empty($settings)) {
				return array();
			}

			$imageSizes = array('teaser', 'thumb', 'small', 'large');
			$imageFiles = array();

			foreach ($imageSizes as $size) {
				if (empty($settings[$size . 'Image'])) {
					continue;
				}

					// Generate only defined count of files for teaser view
				if ($size === 'teaser' && !empty($settings['teaserImageCount'])) {
					$files = reset(array_chunk($files, (int) $settings['teaserImageCount']));
				}

					// Generate images in filesystem
				$result = $this->processImageFiles($files, $settings[$size . 'Image']);
				$imageFiles = array_merge($imageFiles, $result);
			}

			return $imageFiles;
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

				// Simulate working directory
			$this->simulateFrontendEnvironment();

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
			$this->resetFrontendEnvironment();

			return $files;
		}


		/**
		 * Crop image with given settings
		 *
		 * @param string $fileName File to crop
		 * @param array $x X position
		 * @param array $y Y position
		 * @param array $w Width
		 * @param array $h Height
		 * @return string Relative image path
		 */
		public function cropImageFile($fileName, $x, $y, $w, $h) {
			if (empty($fileName)) {
				return '';
			}

				// Check if converting is allowed for this file type
			$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
			$fileType = Tx_SpGallery_Utility_File::getFileType($fileName);
			if (!t3lib_div::inList($allowedTypes, $fileType)) {
				return $fileName;
			}

				// Simulate working directory
			$this->simulateFrontendEnvironment();

			$x = (int) $x;
			$y = (int) $y;
			$w = (int) $w;
			$h = (int) $h;

			$settings = array(
				'XY' =>  ($y + $w) . ',' . ($x + $h),
				'format' => $fileType,
				'quality' => 100,
				'10' => 'IMAGE',
				'10.' => array(
					'file' => $fileName,
				),
				'20' => 'CROP',
				'20.' => array(
					'crop' => $x . ',' . $y . ',' . $w . ',' . $h,
				),
			);

			$info = $this->contentObject->getImgResource('GIFBUILDER', $settings);
			if (!empty($info[3])) {
				$fileName = $info[3];
			}

			return $fileName;
		}


		/**
		 * Simulate working directory "htdocs"
		 *
		 * Required for file_exists check in t3lib_stdGraphic::getImageDimensions
		 *
		 * @return void
		 */
		protected function simulateFrontendEnvironment() {
			$this->currentDir = getcwd();
			chdir(PATH_site);
		}


		/**
		 * Reset working directory to "typo3"
		 *
		 * @return void
		 */
		protected function resetFrontendEnvironment() {
			if (!empty($this->currentDir)) {
				chdir($this->currentDir);
			}
		}

	}
?>