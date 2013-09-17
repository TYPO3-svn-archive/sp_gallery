<?php
namespace Speedprogs\SpGallery\Utility;

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
 * Utility to manage images
 */
class Image {

	/**
	 * @var tslib_cObj
	 */
	static protected $contentObject = NULL;

	/**
	 * @var t3lib_stdGraphic
	 */
	static protected $graphicLibrary = NULL;

	/**
	 * @var string
	 */
	static protected $currentDir;

	/**
	 * Return content object
	 *
	 * @return tslib_cObj
	 */
	static protected function getContentObject() {
		if (self::$contentObject === NULL) {
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
			self::$contentObject = $configurationManager->getContentObject();
		}
		return self::$contentObject;
	}

	/**
	 * Return graphic library
	 *
	 * @return t3lib_stdGraphic
	 */
	static protected function getGraphicLibrary() {
		if (self::$graphicLibrary === NULL) {
			self::$graphicLibrary = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_stdGraphic');
			self::$graphicLibrary->init();
		}
		return self::$graphicLibrary;
	}

	/**
	 * Generate images from given files
	 *
	 * @param array $files Image files
	 * @param array $settings TypoScript configuration
	 * @return array Resulting image files
	 */
	static public function generate(array $files, array $settings) {
		if (empty($files) || empty($settings)) {
			return array();
		}
		$imageSizes = array('teaser', 'thumb', 'small', 'large');
		$imageFiles = array();
		foreach ($imageSizes as $size) {
			if (empty($settings[$size . 'Image'])) {
				continue;
			}
			// Generate images in filesystem
			$result = self::convert($files, $settings[$size . 'Image']);
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
	static public function convert(array $files, array $settings = array(), $tag = FALSE) {
		if (empty($files)) {
			return array();
		}
		if (empty($settings)) {
			return $files;
		}
		// Simulate working directory
		self::simulateFrontendEnvironment();
		// Process images
		$contentObject = self::getContentObject();
		foreach ($files as $key => $fileName) {
			// Check if converting is allowed for this file type
			if (!self::isValidImageType($fileName)) {
				unset($files[$key]);
				continue;
			}
			// Get relative path
			$fileName = str_replace(PATH_site, '', $fileName);
			// Modify image
			if (!empty($settings) && !$tag) {
				$info = $contentObject->getImgResource($fileName, $settings);
				$result = (!empty($info[3]) ? $info[3] : $fileName);
			} else if ($tag) {
				$result = $contentObject->cImage($fileName, array('file.' => $settings));
			}
			$files[$key] = $result;
		}
		// Revert working directory
		self::resetFrontendEnvironment();
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
	static public function crop($fileName, $x, $y, $w, $h) {
		if (empty($fileName)) {
			return '';
		}
		$x = (int) $x;
		$y = (int) $y;
		$w = (int) $w;
		$h = (int) $h;
		// Check if converting is allowed for this file type
		if (!self::isValidImageType($fileName)) {
			return $fileName;
		}
		// Simulate working directory
		self::simulateFrontendEnvironment();
		// Crop image
		$graphicLibrary = self::getGraphicLibrary();
		$image = $graphicLibrary->imageCreateFromFile($fileName);
		$crop = imagecreatetruecolor($w, $h);
		$graphicLibrary->imagecopyresized($crop, $image, 0, 0, $x, $y, $w, $h, $w, $h);
		ImageDestroy($image);
		// Write to temporary directory
		$fileType = \Speedprogs\SpGallery\Utility\File::getFileType($fileName);
		$tempName = $graphicLibrary->randomName() . '.' . $fileType;
		$graphicLibrary->ImageWrite($crop, $tempName);
		ImageDestroy($crop);
		// Revert working directory
		self::resetFrontendEnvironment();
		return $tempName;
	}

	/**
	 * Check image type
	 *
	 * @param string $fileName The image file
	 * @return boolean TRUE if file type is valid
	 */
	static public function isValidImageType($fileName) {
		if (empty($fileName)) {
			return FALSE;
		}
		$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
		$fileType = \Speedprogs\SpGallery\Utility\File::getFileType($fileName);
		return \TYPO3\CMS\Core\Utility\GeneralUtility::inList($allowedTypes, $fileType);
	}

	/**
	 * Simulate working directory "htdocs"
	 *
	 * Required for file_exists check in t3lib_stdGraphic::getImageDimensions
	 *
	 * @return void
	 */
	static protected function simulateFrontendEnvironment() {
		self::$currentDir = getcwd();
		chdir(PATH_site);
	}

	/**
	 * Reset working directory to "typo3"
	 *
	 * @return void
	 */
	static protected function resetFrontendEnvironment() {
		if (!empty(self::$currentDir)) {
			chdir(self::$currentDir);
		}
	}

}
?>