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
 * Service for image galleries
 */
class GalleryService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \Speedprogs\SpGallery\Domain\Repository\GalleryRepository
	 * @inject
	 */
	protected $galleryRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

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
	protected $settings = array();

	/**
	 * Set storagePid for persisting objects
	 *
	 * @param integer $storagePid New storagePid
	 * @return void
	 */
	public function setStoragePid($storagePid) {
		$setup = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK
		);
		$this->settings = $setup['settings'];
		$setup = $this->typoScriptService->convertPlainArrayToTypoScriptArray($setup);
		//$setup['persistence.']['storagePid'] = (int) $storagePid;
		$this->configurationManager->setConfiguration($setup);
	}

	/**
	 * Process one gallery by uid
	 *
	 * @param integer $uid UID of the gallery
	 * @param boolean $generateNames Generate image names from file names
	 * @return boolean TRUE if gallery was modified
	 */
	public function processByUid($uid, $generateNames = FALSE) {
		if (empty($uid) || !is_numeric($uid)) {
			return FALSE;
		}
		try {
			$fileCollection = $this->galleryRepository->findByUid($uid);

			\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($fileCollection);die();

		} catch(\Exception $exception) {}
		if (empty($fileCollection)) {
			return FALSE;
		}
		return $this->process($fileCollection, $generateNames);

	}

	/**
	 * Process all galleries in repository
	 *
	 * @param boolean $generateNames Generate image names from file names
	 * @param string $offset Offset to start with
	 * @param string $limit Limit of the galleries
	 * @return boolean TRUE if a gallery was modified
	 */
	public function processAll($generateNames = FALSE, $offset = NULL, $limit = NULL) {
		$modified = FALSE;
		// Process all galleries
		$galleries = $this->galleryRepository->findAll($offset, $limit);
		foreach ($galleries as $gallery) {
			$result = $this->process($gallery, $generateNames);
			if ($result) {
				$modified = TRUE;
			}
		}
		return $modified;
	}

	/**
	 * Find changes in gallery and generate images
	 *
	 * @param \TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection $fileCollection The collection
	 * @param boolean $generateNames Generate image names from file names
	 * @return boolean TRUE if gallery was modified
	 */
	public function process(\TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection $fileCollection, $generateNames = FALSE) {
		$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
		$fileCollection->loadContents();
		$files = $fileCollection->getItems();
		$imageFiles = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		foreach($files as $file) {
			if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList($allowedTypes, $fileType)){
				$imageFiles->attach($file);
			}
		}
		if (count($imageFiles)) {
			$fileCollection->setImages($imageFiles);
			$this->persistenceManager->persistAll();
			\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($fileCollection);die();
			return TRUE;
		}
		return FALSE;
	}

}
?>