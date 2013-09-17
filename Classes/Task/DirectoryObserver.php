<?php
namespace Speedprogs\SpGallery\Task;

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
 * Process gallery image directories
 */
class DirectoryObserver extends \TYPO3\CMS\Scheduler\Task {

	/**
	 * @var integer
	 */
	public $elementsPerRun = 3;

	/**
	 * @var string
	 */
	public $clearCachePages = 0;

	/**
	 * @var integer
	 */
	public $storagePid = 0;

	/**
	 * @var boolean
	 */
	public $generateNames = FALSE;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var \Speedprogs\SpGallery\Persistence\Registry
	 */
	protected $registry;

	/**
	 * @var \Speedprogs\SpGallery\Service\GalleryService
	 */
	protected $galleryService;

	/**
	 * Initialize the task
	 *
	 * @return void
	 */
	protected function initializeTask() {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		// Load plugin settings
		$typoScriptService = $objectManager->get('Speedprogs\\SpGallery\\Service\\TypoScriptService');
		$setup = $typoScriptService->getSetup('plugin.tx_spgallery');
		$this->settings = $typoScriptService->parse($setup['settings.'], FALSE);
		$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
		$configurationManager->setConfiguration($setup);
		// Load required objects
		$this->registry = $objectManager->get('Tx_SpGallery_Persistence_Registry');
		$this->galleryService = $objectManager->get('Tx_SpGallery_Service_GalleryService');
		// Set new storagePid for persistence handling
		if (!empty($this->storagePid)) {
			$this->galleryService->setStoragePid($this->storagePid);
		}
	}

	/**
	 * Find gallaries and generate images by configuration
	 *
	 * @return boolean TRUE if success
	 */
	public function execute() {
		// Get attributes
		$limit  = (int) $this->elementsPerRun;
		$offset = (int) $this->registry->get('offset');
		$names  = (bool) $this->generateNames;
		// Process galleries
		$modified = $this->galleryService->processAll($names, $offset, $limit);
		// Store new offset to registry
		$offset = ($modified ? $offset + $limit : 0);
		$this->registry->add('offset', $offset);
		// Clear page cache
		if ($modified && !empty($this->clearCachePages)) {
			$this->clearPageCache($this->clearCachePages);
		}
		return TRUE;
	}

	/**
	 * Clear cache of given pages
	 *
	 * @param string $pages List of page ids
	 * @return void
	 */
	protected function clearPageCache($pages) {
		if (!empty($pages)) {
			$pages = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $pages, TRUE);
			\TYPO3\CMS\Extbase\Service\CacheService::clearPageCache($pages);
		}
	}

	/**
	 * Returns additional information
	 *
	 * @return string
	 */
	public function getAdditionalInformation() {
		// Load offset and limit
		$offset = (int) $this->registry->get('offset');
		$limit = (int) $this->elementsPerRun;
		$pid = (int) $this->storagePid;
		return ' Limit: ' . $limit . ', Offset: ' . $offset . ', Storage: ' . $pid . ' ';
	}

	/**
	 * Remove disallowed attributes before serializing this object
	 *
	 * @return array Allowed class attributes
	 */
	public function __sleep() {
		$attributes = get_object_vars($this);
		$disallowed = array('settings', 'registry', 'galleryService');
		return array_keys(array_diff_key($attributes, array_flip($disallowed)));
	}

	/**
	 * Initialize the object after unserializing
	 *
	 * @return void
	 */
	public function __wakeup() {
		$this->initializeTask();
	}

}
?>