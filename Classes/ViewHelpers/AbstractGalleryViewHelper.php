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
	 * Abstract gallery view helper
	 */
	class Tx_SpGallery_ViewHelpers_AbstractGalleryViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * @var Tx_Extbase_Object_ObjectManager
		 */
		protected $objectManager;

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var Tx_SpGallery_Service_GalleryImageService
		 */
		protected $imageService;


		/**
		 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
			$this->settings = $configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
			if (empty($this->settings)) {
				throw new Exception('Extension sp_gallery: No configuration found for gallery view helper', 1308305554);
			}
			$this->settings = Tx_SpGallery_Utility_TypoScript::parse($this->settings);
		}


		/**
		 * @param Tx_Extbase_Object_ObjectManager $objectManager
		 * @return void
		 */
		public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
			$this->objectManager = $objectManager;
		}


		/**
		 * Returns all images from gallery
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery Gallery to get images from
		 * @param array $settings Image configuration
		 * @param boolean $tag Returns images with complete tag
		 * @param integer $count Count of files to return
		 * @return array Relative image paths
		 */
		protected function getGalleryImages(Tx_SpGallery_Domain_Model_Gallery $gallery, array $settings = array(), $tag = FALSE, $count = 0) {
			if ($this->imageService === NULL) {
				$this->imageService = $this->objectManager->get('Tx_SpGallery_Service_GalleryImage');
			}
			$directory = $gallery->getImageDirectory();
			return $this->imageService->getGalleryImages($directory, $settings, $tag, $count);
		}


		/**
		 * Returns gallery theme file name
		 *
		 * @return string Relative file path
		 */
		protected function getThemeFile() {
			$themePath = 'EXT:sp_gallery/Resources/Public/Themes/';
			$themeName = 'classic';

			if (!empty($this->settings['themesPath'])) {
				$themePath = $this->settings['themesPath'];
			}

			if (!empty($this->settings['theme'])) {
				$themeName = $this->settings['theme'];
			}

			$themeFile = rtrim($themePath, '/') . '/' . $themeName . '/galleria.' . $themeName . '.min.js';
			$themeFile = t3lib_div::getFileAbsFileName($themeFile);

			return str_replace(PATH_site, '', $themeFile);
		}


		/**
		 * Returns required Javascript for Galleria plugin
		 *
		 * @param array $images Images to show
		 * @param string $themeFile Theme file name
		 * @param string $element ID of the HTML element to render gallery
		 * @return string Complete Javsscript content
		 */
		protected function getGalleryJs(array $images, $themeFile, $element) {
			if (empty($images) || empty($themeFile) || empty($element)) {
				return '';
			}

				// Get data source
			$dataSource = array();
			foreach ($images as $image) {
				$dataSource[] = "    {image: '/" . $image['small'] . "', thumb: '/" . $image['thumb'] . "', big:'/" . $image['large'] . "'}";
			}

				// Get Galleria options
			$options = array(
				"  dataSource: [ " . LF . implode(',' . LF, $dataSource) . LF . "  ]",
			);
			if (!empty($this->settings['galleria'])) {
				foreach ($this->settings['galleria'] as $option => $value) {
					if ($value !== '') {
						$value = $this->escapeJsValue($value);
						$options[] = $option . ": " . $value;
					}
				}
			}
			$options = implode(',' . LF . '  ', $options);

				// Load theme and initialize galleria
			$script = array(
				"Galleria.loadTheme('/" . $themeFile . "');",
				"$('#" . trim($element) . "').galleria({" . LF . $options . LF . "});"
			);

			return LF . implode(LF, $script) . LF;
		}


		/**
		 * Escapes given value by its type
		 *
		 * @param string $value Value to escape
		 * @return string Escaped value
		 */
		protected function escapeJsValue($value) {
			if ($value === 'null' || $value === 'true' || $value === 'false' || $value == 'undef') {
				return $value;
			}
			if (stripos($value, 'function') !== FALSE || stripos($value, '[') !== FALSE || stripos($value, '{') !== FALSE || stripos($value, 'this.') !== FALSE) {
				return $value;
			}
			if (preg_match('|^[0-9\.,]*$|', $value)) {
				return str_replace(',', '.', $value);
			}
			$value = trim((string) $value, '" \'');
			return '"' . $value . '"';
		}

	}
?>