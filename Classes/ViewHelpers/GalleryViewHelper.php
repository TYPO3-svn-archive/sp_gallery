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
	 * Gallery view helper
	 */
	class Tx_SpGallery_ViewHelpers_GalleryViewHelper extends Tx_SpGallery_ViewHelpers_AbstractGalleryViewHelper {

		/**
		 * @var string
		 */
		protected $themePath = 'EXT:sp_gallery/Resources/Public/Themes/';

		/**
		 * @var string
		 */
		protected $layoutRootPath = 'EXT:sp_gallery/Resources/Private/Layouts/';

		/**
		 * @var string
		 */
		protected $templateRootPath = 'EXT:sp_gallery/Resources/Private/Templates/';

		/**
		 * @var string
		 */
		protected $patialRootPath = 'EXT:sp_gallery/Resources/Private/Partials/';

		/**
		 * @var string
		 */
		protected $templateFile = 'Gallery/Javascript.html';


		/**
		 * Initializes the view helper before invoking the render method
		 *
		 * @return void
		 */
		public function initialize() {
			$viewSettings = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.tx_spgallery.view');
			$viewSettings = Tx_SpGallery_Utility_TypoScript::parse($viewSettings);
			if (!empty($viewSettings['layoutRootPath'])) {
				$this->layoutRootPath = $viewSettings['layoutRootPath'];
			}
			if (!empty($viewSettings['templateRootPath'])) {
				$this->templateRootPath = $viewSettings['templateRootPath'];
			}
			if (!empty($viewSettings['partialRootPath'])) {
				$this->patialRootPath = $viewSettings['patialRootPath'];
			}
		}


		/**
		 * Renders the jQuery gallery
		 *
		 * @param mixed $gallery The gallery to render
		 * @param string $elementId ID of the HTML element to render gallery
		 * @param string $infoId ID of the HTML element to render detailed gallery info
		 * @param integer $show UID of the image to show after loading
		 * @return string Rendered gallery
		 */
		public function render($gallery = NULL, $elementId = NULL, $infoId = NULL, $show = NULL) {
			if ($gallery === NULL) {
				$gallery = $this->renderChildren();
			}

			if (!$gallery instanceof Tx_SpGallery_Domain_Model_Gallery) {
				throw new Exception('No valid gallery given to render', 1308305558);
			}

				// Check container id
			$elementId = trim($elementId);
			if (empty($elementId)) {
				throw new Exception('No valid HTML element ID given to render gallery', 1308305552);
			}

				// Escape options
			$options = array();
			if (!empty($this->settings['galleria'])) {
				foreach ($this->settings['galleria'] as $key => $option) {
					if ($option['value'] !== '') {
						$options[$key] = $this->escapeValue($option['value'], $option['type']);
					}
				}
			}

				// Get image files
			$images = $this->getGalleryImages($gallery);

				// Add index of the image to show after loading
			if ($show !== NULL && !empty($images[$show])) {
				$tempImages = array_values($images);
				$options['show'] = (int) array_search($images[$show], $tempImages);
			}

			return $this->renderGalleryTemplate($elementId, $infoId, $images, $options, $show);
		}


		/**
		 * Renders the Javascript template as described in TypoScript
		 *
		 * @param string $elementId The id of the DIV container in HTML template
		 * @param string $infoId ID of the HTML element to render detailed gallery info
		 * @param array $images The image files grouped by size
		 * @param array $options Javascript options for the galleria plugin
		 * @param integer $show UID of the image to show after loading
		 * @return string The rendered content
		 */
		protected function renderGalleryTemplate($elementId, $infoId, array $images, array $options) {
				// Get settings
			$extensionKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
			$themeFile    = $this->getThemeFile();
			$templateFile = $this->getTemplatePathAndFilename();

				// Assign variables to template
			$variables = array(
				'themeFile' => $themeFile,
				'elementId' => $elementId,
				'infoId'    => $infoId,
				'images'    => $images,
				'options'   => $options,
				'settings'  => $this->settings,
			);

				// Render template
			$content = Tx_SpGallery_Utility_Template::render($extensionKey, $templateFile, $variables, $this->layoutRootPath, $this->patialRootPath);

				// Remove whitepases and empty newlines
			return preg_replace('/^[ \t]*[\r\n]+/m', '', $content);
		}


		/**
		 * Returns gallery theme file name
		 *
		 * @return string URL to file
		 */
		protected function getThemeFile() {
			$themePath = $this->themePath;
			$themeName = 'classic';

			if (!empty($this->settings['themesPath'])) {
				$themePath = $this->settings['themesPath'];
			}

			if (!empty($this->settings['theme'])) {
				$themeName = $this->settings['theme'];
			}

			$themeFile = rtrim($themePath, '/') . '/' . $themeName . '/galleria.' . $themeName . '.min.js';
			$themeFile = $GLOBALS['TSFE']->tmpl->getFileName($themeFile);

			return t3lib_div::locationHeaderUrl($themeFile);
		}


		/**
		 * Returns the javascript template file
		 *
		 * @return string Relative file path
		 */
		protected function getTemplatePathAndFilename() {
			$fileName = rtrim($this->templateRootPath, '/') . '/' . $this->templateFile;
			return $GLOBALS['TSFE']->tmpl->getFileName($fileName);
		}


		/**
		 * Escapes given value by its type
		 *
		 * @param string $value Value to escape
		 * @param string $types List of variable types
		 * @return string Escaped value
		 */
		protected function escapeValue($value, $types) {
			if (empty($types)) {
				return 'null';
			}

			$types = t3lib_div::trimExplode(',', $types, TRUE);

				// Null
			if (in_array('null', $types) && $value === 'null') {
				return $value;
			}

				// Boolean
			if (in_array('boolean', $types) && is_numeric($value)) {
				return (!empty($value) ? 'true' : 'false');
			}

				// Integer / double
			if (in_array('number', $types) && is_numeric($value)) {
				return $value;
			}

				// Array
			if (in_array('array', $types) && strpos($value, '[') !== FALSE) {
				return $value;
			}

				// String
			if (in_array('string', $types)) {
				return "'" . $value . "'";
			}

			return 'null';
		}

	}
?>