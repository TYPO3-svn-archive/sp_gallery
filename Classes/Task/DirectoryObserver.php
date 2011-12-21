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
					$imageFiles = $this->generateImageFiles($files);
					$imageFiles = array_intersect_key($files, $imageFiles);
					$images = $this->generateImageRecords($gallery, $imageFiles);
					$gallery->setImages($images);
					$gallery->setImageDirectoryHash($hash);
					$modified = TRUE;
				}
			}

			if ($modified) {
				$this->persistenceManager->persistAll();
			}

			return $modified;
		}


		/**
		 * Generate images from given directories
		 *
		 * @param array $files Image files
		 * @return array Resulting image files
		 */
		protected function generateImageFiles(array $files) {
			if (empty($files)) {
				return array();
			}

			$imageSizes = array('teaser', 'thumb', 'small', 'large');
			$imageFiles = array();

			foreach ($imageSizes as $size) {
				if (empty($this->settings[$size . 'Image'])) {
					continue;
				}

					// Generate only defined count of files for teaser view
				if ($size === 'teaser' && !empty($this->settings['teaserImageCount'])) {
					$files = reset(array_chunk($files, (int) $this->settings['teaserImageCount']));
				}

					// Generate images in filesystem
				$result = $this->imageService->processImageFiles($files, $this->settings[$size . 'Image']);
				$imageFiles = array_merge($imageFiles, $result);
			}

			return $imageFiles;
		}


		/**
		 * Generate image records from given files
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery Parent gallery
		 * @param array $files Image files
		 * @return array Image objects
		 */
		protected function generateImageRecords(Tx_SpGallery_Domain_Model_Gallery $gallery, array $files) {
			if (empty($files)) {
				return array();
			}

			$modified = FALSE;
			$images = array();

				// Generate image records
			$files = array_unique($files);
			foreach ($files as $file) {
				$fileName = str_replace(PATH_site, '', $file);
				$result = $this->imageRepository->findOneByFileName($fileName);
				if (!empty($result)) {
					$images[] = $result;
					continue;
				}

					// Get image information
				$imageInfo = Tx_SpGallery_Utility_File::getImageInfo($file);
				$imageName = ($this->generateName ? $imageInfo['name'] : '');

					// Collect image attributes
				$imageRow = array(
					'name'         => $imageName,
					'description'  => '',
					'file_name'    => $fileName,
					'file_size'    => $imageInfo['size'],
					'file_type'    => $imageInfo['type'],
					'image_height' => $imageInfo['height'],
					'image_width'  => $imageInfo['width'],
					'gallery'      => $gallery->getUid(),
				);

					// Create image object
				$images[] = $this->objectBuilder->create('Tx_SpGallery_Domain_Model_Image', $imageRow);
				$modified = TRUE;
			}

			if ($modified) {
				$this->persistenceManager->persistAll();
			}

			return $images;
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

			return ' Limit: ' . $limit . ', Offset: ' . $offset . ' ';
		}


		/**
		 * Remove disallowed attributes before serializing this object
		 *
		 * @return array Allowed class attributes
		 */
		public function __sleep() {
			$attributes = get_object_vars($this);
			$disallowed = array('settings', 'galleryRepository', 'imageRepository', 'imageService', 'persistenceManager', 'registry', 'objectBuilder');
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