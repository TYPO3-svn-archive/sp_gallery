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
	 * Process gallery image directories
	 */
	class Tx_SpGallery_Task_DirectoryObserver extends tx_scheduler_Task {

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
		public $generateName = FALSE;

		/**
		 * @var array
		 */
		protected $settings = array();

		/**
		 * @var Tx_SpGallery_Domain_Repository_GalleryRepository
		 */
		protected $galleryRepository;

		/**
		 * @var Tx_SpGallery_Domain_Repository_ImageRepository
		 */
		protected $imageRepository;

		/**
		 * @var Tx_SpGallery_Service_GalleryImage
		 */
		protected $imageService;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;

		/**
		 * @var Tx_SpGallery_Persistence_Registry
		 */
		protected $registry;

		/**
		 * @var Tx_SpGallery_Object_ObjectBuilder
		 */
		protected $objectBuilder;


		/**
		 * Initialize the task
		 *
		 * @return void
		 */
		protected function initializeTask() {
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');

				// Load plugin settings
			$configuration  = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.tx_spgallery');
			$this->settings = Tx_SpGallery_Utility_TypoScript::parse($configuration['settings.'], FALSE);

				// Set new configuration for persistence handling
			if (empty($configuration['persistence.']['storagePid'])) {
				$configuration['persistence.']['storagePid'] = 1;
			}
			if (!empty($this->storagePid)) {
				$configuration['persistence.']['storagePid'] = (int) $this->storagePid;
			}
			$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
			$configurationManager->setConfiguration($configuration);

				// Load required objects
			$this->galleryRepository  = $objectManager->get('Tx_SpGallery_Domain_Repository_GalleryRepository');
			$this->imageRepository    = $objectManager->get('Tx_SpGallery_Domain_Repository_ImageRepository');
			$this->imageService       = $objectManager->get('Tx_SpGallery_Service_GalleryImage');
			$this->persistenceManager = $objectManager->get('Tx_Extbase_Persistence_Manager');
			$this->registry           = $objectManager->get('Tx_SpGallery_Persistence_Registry');
			$this->objectBuilder      = $objectManager->get('Tx_SpGallery_Object_ObjectBuilder');
		}


		/**
		 * Find gallaries and generate images by configuration
		 *
		 * @return boolean TRUE if success
		 */
		public function execute() {
				// Get offset and limit
			$limit  = (int) $this->elementsPerRun;
			$offset = (int) $this->registry->get('offset');

				// Process galleries
			$result = $this->processGalleries($offset, $limit);

				// Store new offset to registry
			$offset = (!empty($result) ? $offset + $limit : 0);
			$this->registry->add('offset', $offset);

				// Clear page cache
			if (!empty($result) && !empty($this->clearCachePages)) {
				$this->clearPageCache($this->clearCachePages);
			}

			return TRUE;
		}


		/**
		 * Process all galleries
		 *
		 * @param integer $offset Gallery to start with
		 * @param string $limit Limit of the galleries
		 * @return boolean TRUE if success
		 */
		protected function processGalleries($offset, $limit) {
				// Find all galleries
			$galleries = $this->galleryRepository->findAll($offset, $limit);
			if (!$galleries->count()) {
				return FALSE;
			}

			$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
			$modified = FALSE;

				// Find changes in directory and generate images
			foreach ($galleries as $gallery) {
				$directory = $gallery->getImageDirectory();
				$files = Tx_SpGallery_Utility_File::getFiles($directory, TRUE, $allowedTypes);
				$hash = md5(serialize($files));

				if ($hash !== $gallery->getImageDirectoryHash()) {
						// Get files
					$imageFiles = $this->imageService->generateImageFiles($files, $this->settings);
					$imageFiles = array_intersect_key($files, $imageFiles);
					$imageFiles = array_unique($imageFiles);

						// Remove images without file
					$this->removeDeletedImages($gallery, $imageFiles);

						// Create images from new files
					$this->createNewImages($gallery, $imageFiles);

						// Write new directory hash
					// $gallery->setImageDirectoryHash($hash);
					$modified = TRUE;
				}
			}

			if ($modified) {
				$this->persistenceManager->persistAll();
			}

			return $modified;
		}


		/**
		 * Remove image records without file
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * param array $files Image files
		 * @return void
		 */
		protected function removeDeletedImages(Tx_SpGallery_Domain_Model_Gallery $gallery, array $files) {
			if (empty($files)) {
				return;
			}

			$modified = FALSE;

				// Find records with deleted image file
			$images = $this->imageRepository->findByGallery($gallery);
			foreach ($images as $image) {
				$fileName = PATH_site . $image->getFileName();
				if (in_array($fileName, $files)) {
					continue;
				}

				$this->imageRepository->remove($image);
				$modified = TRUE;
			}

			if ($modified) {
				$this->persistenceManager->persistAll();
			}
		}


		/**
		 * Create image records from new files
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * @param array $files Image files
		 * @return void
		 */
		protected function createNewImages(Tx_SpGallery_Domain_Model_Gallery $gallery, array $files) {
			if (empty($files)) {
				return;
			}

			$modified = FALSE;

				// Generate image records
			foreach ($files as $key => $file) {
				$fileName = str_replace(PATH_site, '', $file);
				$result = $this->imageRepository->findOneByFileName($fileName);
				if (!empty($result)) {
					continue;
				}

					// Create image object
				$imageRow = array(
					'file_name' => $fileName,
					'gallery'   => $gallery->getUid(),
				);
				$image = $this->objectBuilder->create('Tx_SpGallery_Domain_Model_Image', $imageRow);
				$image->generateImageInformation();
				if ($this->generateName) {
					$image->generateImageName();
				}

				$image->setGallery($gallery);
				$this->imageRepository->add($image);
				$gallery->addImage($image);
				$modified = TRUE;
			}

			if ($modified) {
				$this->persistenceManager->persistAll();
			}
		}


		/**
		 * Clear cache of given pages
		 *
		 * @param string $pages List of page ids
		 * @return void
		 */
		protected function clearPageCache($pages) {
			if (!empty($pages)) {
				$pages = t3lib_div::intExplode(',', $pages, TRUE);
				Tx_Extbase_Utility_Cache::clearPageCache($pages);
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
			$disallowed = array(
				'settings',
				'galleryRepository',
				'imageRepository',
				'imageService',
				'persistenceManager',
				'registry',
				'objectBuilder',
			);
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