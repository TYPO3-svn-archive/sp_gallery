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
	 * Abstract view helper
	 */
	abstract class Tx_SpGallery_ViewHelpers_AbstractTemplateBasedViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * @var Tx_Extbase_Object_ObjectManager
		 */
		protected $objectManager;

		/**
		 * @var array
		 */
		protected $settings;

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
		protected $templateFile = '';


		/**
		 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
			$this->settings = $configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
			if (empty($this->settings)) {
				throw new Exception('No configuration found for gallery view helper');
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
		 * Returns the template file
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