<?php
namespace Speedprogs\SpGallery\Service;

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
class TypoScriptService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected $frontend;

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
	 * @inject
	 */
	protected $typoScriptService;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * Initialize configuration manager and content object
	 *
	 * @return void
	 */
	protected function initialize() {
		// Get configuration manager
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
		// Simulate Frontend
		if (TYPO3_MODE != 'FE') {
			$this->simulateFrontend();
			$this->configurationManager->setContentObject($GLOBALS['TSFE']->cObj);
		}
		// Get content object
		$this->contentObject = $this->configurationManager->getContentObject();
		if (empty($this->contentObject)) {
			$this->contentObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'
			);
		}
	}

	/**
	 * Simulate a frontend environment
	 *
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj Instance of an content object
	 * @return void
	 */
	public function simulateFrontend(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj = NULL) {
		// Make backup of current frontend
		$this->frontend = (!empty($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL);
		// Create new frontend instance
		$GLOBALS['TSFE'] = new \stdClass();
		$GLOBALS['TSFE']->cObjectDepthCounter = 100;
		$GLOBALS['TSFE']->cObj = (!empty($cObj) ? $cObj : \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'
		));
		if (empty($GLOBALS['TSFE']->sys_page)) {
			$GLOBALS['TSFE']->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Frontend\\Page\\PageRepository'
			);
		}
		if (empty($GLOBALS['TT'])) {
			$GLOBALS['TT'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker'
			);
		}
		if (empty($GLOBALS['TSFE']->tmpl)) {
			$GLOBALS['TSFE']->tmpl = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Core\\TypoScript\\TemplateService'
			);
			$GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
			$GLOBALS['TSFE']->tmpl->init();
		}
		if (empty($GLOBALS['TSFE']->config)) {
			$GLOBALS['TSFE']->config = \TYPO3\CMS\Core\Utility\GeneralUtility::removeDotsFromTS($this->getSetup());
		}
	}

	/**
	 * Reset an existing frontend environment
	 *
	 * @param object $frontend Instance of a frontend environemnt
	 * @return void
	 */
	public function resetFrontend($frontend = NULL) {
		$frontend = (!empty($frontend) ? $frontend : $this->frontend);
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
	public function getSetup($typoScriptPath = '', array $setup = array()) {
		if (empty($this->configurationManager)) {
			$this->initialize();
		}
		if (empty($setup)) {
			$setup = $this->configurationManager->getConfiguration(
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
	public function getSetupForPid($pid, $typoScriptPath = '') {
		if (empty($pid)) {
			return $this->getSetup($typoScriptPath);
		}
		if (empty($this->configuration[$pid])) {
			if (empty($GLOBALS['TSFE']->sys_page)) {
				$this->initialize();
			}
			$rootLine = $GLOBALS['TSFE']->sys_page->getRootLine((int) $pid);
			$GLOBALS['TSFE']->tmpl->start($rootLine);
			$this->configuration[$pid] = $GLOBALS['TSFE']->tmpl->setup;
		}
		return $this->getSetup($typoScriptPath, $this->configuration[$pid]);
	}

	/**
	 * Parse given TypoScript configuration
	 *
	 * @param array $configuration TypoScript configuration
	 * @param boolean $isPlain Is a plain "Fluid like" configuration array
	 * @return array Parsed configuration
	 */
	public function parse(array $configuration, $isPlain = TRUE) {
		if (empty($this->contentObject)) {
			$this->initialize();
		}
		// Convert to classic TypoScript array
		if ($isPlain) {
			$configuration = $this->typoScriptService->convertPlainArrayToTypoScriptArray($configuration);
		}
		// Parse configuration
		return $this->parseTypoScriptArray($configuration);
	}

	/**
	 * Parse TypoScript array
	 *
	 * @param array $configuration TypoScript configuration array
	 * @return array Parsed configuration
	 * @api
	 */
	public function parseTypoScriptArray(array $configuration) {
		$typoScriptArray = array();
		if (is_array($configuration)) {
			foreach ($configuration as $key => $value) {
				$ident = rtrim($key, '.');
				if (is_array($value)) {
					if (!empty($configuration[$ident])) {
						$typoScriptArray[$ident] = $this->contentObject->cObjGetSingle($configuration[$ident], $value);
					} else {
						$typoScriptArray[$ident] = $this->parseTypoScriptArray($value);
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