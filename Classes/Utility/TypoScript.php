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
 * Utility to manage and convert Typoscript Code
 */
class TypoScript {

	/**
	 * @var object
	 */
	static protected $frontend;

	/**
	 * @var tslib_cObj
	 */
	static protected $contentObject;

	/**
	 * @var \TYPO3\CMS\Extbase\ConfigurationManager
	 */
	static protected $configurationManager;

	/**
	 * @var array
	 */
	static protected $configuration;

	/**
	 * Initialize configuration manager and content object
	 *
	 * @return void
	 */
	static protected function initialize() {
		// Get configuration manager
		$objectManager = \TYPO3\CMS\Core\Utility\GenralUtility::makeInstance('Tx_Extbase_Object_ObjectManager');
		self::$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
		// Simulate Frontend
		if (TYPO3_MODE != 'FE') {
			self::simulateFrontend();
			self::$configurationManager->setContentObject($GLOBALS['TSFE']->cObj);
		}
		// Get content object
		self::$contentObject = self::$configurationManager->getContentObject();
		if (empty(self::$contentObject)) {
			self::$contentObject = \TYPO3\CMS\Core\Utility\GenralUtility::makeInstance('tslib_cObj');
		}
	}

	/**
	 * Simulate a frontend environment
	 *
	 * @param tslib_cObj $cObj Instance of an content object
	 * @return void
	 */
	static public function simulateFrontend(tslib_cObj $cObj = NULL) {
		// Make backup of current frontend
		self::$frontend = (!empty($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL);
		// Create new frontend instance
		$GLOBALS['TSFE'] = new stdClass();
		$GLOBALS['TSFE']->cObjectDepthCounter = 100;
		$GLOBALS['TSFE']->cObj = (!empty($cObj) ? $cObj: \TYPO3\CMS\Core\Utility\GenralUtility::makeInstance('tslib_cObj'));
		if (empty($GLOBALS['TSFE']->sys_page)) {
			$GLOBALS['TSFE']->sys_page = \TYPO3\CMS\Core\Utility\GenralUtility::makeInstance('t3lib_pageSelect');
		}
		if (empty($GLOBALS['TT'])) {
			$GLOBALS['TT'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TimeTrackNull');
		}
		if (empty($GLOBALS['TSFE']->tmpl)) {
			$GLOBALS['TSFE']->tmpl = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TStemplate');
			$GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
			$GLOBALS['TSFE']->tmpl->init();
		}
		if (empty($GLOBALS['TSFE']->config)) {
			$GLOBALS['TSFE']->config = \TYPO3\CMS\Core\Utility\GeneralUtility::removeDotsFromTS(self::getSetup());
		}
	}

	/**
	 * Reset an existing frontend environment
	 *
	 * @param object $frontend Instance of a frontend environemnt
	 * @return void
	 */
	static public function resetFrontend($frontend = NULL) {
		$frontend = (!empty($frontend) ? $frontend : self::$frontend);
		if (!empty($frontend)) {
			$GLOBALS['TSFE'] = $frontend;
		}
	}

	/**
	 * Returns unparsed TypoScript setup
	 *
	 * @param string $typoScriptPath TypoScript path
	 * @param array $$setup Optional TypoScript setup array
	 * @return array TypoScript setup
	 */
	static public function getSetup($typoScriptPath = '', array $setup = array()) {
		if (empty(self::$configurationManager)) {
			self::initialize();
		}
		if (empty($setup)) {
			$setup = self::$configurationManager->getConfiguration(
				\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
			);
		}
		if (empty($typoScriptPath)) {
			return $setup;
		}
		$path = explode('.', $typoScriptPath);
		foreach ($path as $segment) {
			if (empty($setup[$segment . '.'])) {
				return array();
			}
			$setup = $setup[$segment . '.'];
		}
		return $setup;
	}

	/**
	 * Returns unparsed TypoScript setup collected from the rootline
	 *
	 * @param integer $pid The pid to start with
	 * @param string $typoScriptPath TypoScript path
	 * @return array TypoScript setup
	 */
	static public function getSetupForPid($pid, $typoScriptPath = '') {
		if (empty($pid)) {
			return self::getSetup($typoScriptPath);
		}
		if (empty(self::$configuration[$pid])) {
			if (empty($GLOBALS['TSFE']->sys_page)) {
				self::initialize();
			}
			$rootLine = $GLOBALS['TSFE']->sys_page->getRootLine((int) $pid);
			$GLOBALS['TSFE']->tmpl->start($rootLine);
			self::$configuration[$pid] = $GLOBALS['TSFE']->tmpl->setup;
		}
		return self::getSetup($typoScriptPath, self::$configuration[$pid]);
	}

	/**
	 * Parse given TypoScript configuration
	 *
	 * @param array $configuration TypoScript configuration
	 * @param boolean $isPlain Is a plain "Fluid like" configuration array
	 * @return array Parsed configuration
	 */
	static public function parse(array $configuration, $isPlain = TRUE) {
		if (empty(self::$contentObject)) {
			self::initialize();
		}
		// Convert to classic TypoScript array
		if ($isPlain) {
			$configuration = \TYPO3\CMS\Extbase\Service\TypoScriptService::convertPlainArrayToTypoScriptArray($configuration);
		}
		// Parse configuration
		return self::parseTypoScriptArray($configuration);
	}

	/**
	 * Parse TypoScript array
	 *
	 * @param array $configuration TypoScript configuration array
	 * @return array Parsed configuration
	 * @api
	 */
	static public function parseTypoScriptArray(array $configuration) {
		$typoScriptArray = array();
		if (is_array($configuration)) {
			foreach ($configuration as $key => $value) {
				$ident = rtrim($key, '.');
				if (is_array($value)) {
					if (!empty($configuration[$ident])) {
						$typoScriptArray[$ident] = self::$contentObject->cObjGetSingle($configuration[$ident], $value);
					} else {
						$typoScriptArray[$ident] = self::parseTypoScriptArray($value);
					}
					unset($configuration[$key]);
				} else if (is_string($value) && $key === $ident) {
					$typoScriptArray[$key] = $value;
				}
			}
		}
		return $typoScriptArray;
	}

}
?>