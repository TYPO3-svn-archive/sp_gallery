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
		protected $templateFile = 'EXT:sp_gallery/Resources/Private/Templates/Gallery/Javascript.html';


		/**
		 * Initializes the view helper before invoking the render method
		 *
		 * @return void
		 */
		public function initialize() {

		}


		/**
		 * Renders the jQuery gallery
		 *
		 * @param mixed $images Images to render
		 * @param string $elementId ID of the HTML element to render gallery
		 * @return string Rendered gallery
		 */
		public function render($images = NULL, $elementId = NULL) {
			if ($images === NULL) {
				$images = $this->renderChildren();
			}

				// Check container id
			$elementId = trim($elementId);
			if (empty($elementId)) {
				throw new Exception('Extension sp_gallery: No valid HTML element ID given to render gallery', 1308305552);
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
			$images = $this->getGalleryImages($images);

			return $this->renderGalleryTemplate($elementId, $images, $options);
		}


		/**
		 * Renders the Javascript template as described in TypoScript
		 *
		 * @param string $elementId The id of the DIV container in HTML template
		 * @param array $images The image files grouped by size
		 * @param array $options Javascript options for the galleria plugin
		 * @return string The rendered content
		 */
		protected function renderGalleryTemplate($elementId, array $images, array $options) {
				// Get settings
			$extensionKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
			$themeFile    = $this->getThemeFile();
			$templateFile = $this->getTemplateFile();

				// Create Fluid view
			$view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
			$view->setTemplatePathAndFilename($templateFile);
			$view->getRequest()->setControllerExtensionName($extensionKey);

				// Assign variables to template
			$view->assign('themeFile',   $themeFile);
			$view->assign('elementId',   $elementId);
			$view->assign('images',      $images);
			$view->assign('options',     $options);

				// Render template
			$content = $view->render();

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
		protected function getTemplateFile() {
			$templateFile = $this->templateFile;

			if (!empty($this->settings['templateFile'])) {
				$templateFile = $this->settings['templateFile'];
			}

			return $GLOBALS['TSFE']->tmpl->getFileName($templateFile);
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