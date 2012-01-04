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
	 * Service for image galleries
	 */
	class Tx_SpGallery_Service_GalleryService implements t3lib_Singleton {

		/**
		 * @var Tx_SpGallery_Domain_Repository_GalleryRepository
		 */
		protected $galleryRepository;

		/**
		 * @var Tx_SpGallery_Domain_Repository_ImageRepository
		 */
		protected $imageRepository;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;

		/**
		 * @var Tx_SpGallery_Object_ObjectBuilder
		 */
		protected $objectBuilder;

		/**
		 * @var Tx_Extbase_Configuration_ConfigurationManager
		 */
		protected $configurationManager;

		/**
		 * @var array
		 */
		protected $settings = array();


		/**
		 * @param Tx_SpGallery_Domain_Repository_GalleryRepository $galleryRepository
		 * @return void
		 */
		public function injectGalleryRepository(Tx_SpGallery_Domain_Repository_GalleryRepository $galleryRepository) {
			$this->galleryRepository = $galleryRepository;
		}


		/**
		 * @param Tx_SpGallery_Domain_Repository_ImageRepository $imageRepository
		 * @return void
		 */
		public function injectImageRepository(Tx_SpGallery_Domain_Repository_ImageRepository $imageRepository) {
			$this->imageRepository = $imageRepository;
		}


		/**
		 * @param Tx_Extbase_Persistence_Manager $persistenceManager
		 * @return void
		 */
		public function injectPersistenceManager(Tx_Extbase_Persistence_Manager $persistenceManager) {
			$this->persistenceManager = $persistenceManager;
		}


		/**
		 * @param Tx_SpGallery_Object_ObjectBuilder $objectBuilder
		 * @return void
		 */
		public function injectObjectBuilder(Tx_SpGallery_Object_ObjectBuilder $objectBuilder) {
			$this->objectBuilder = $objectBuilder;
		}


		/**
		 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
			$this->configurationManager = $configurationManager;
			$settings = $this->configurationManager->getConfiguration(
				Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_SETTINGS
			);
			if (!is_array($settings)) {
				throw new Exception('Can not load settings for current plugin');
			}
			$this->settings = Tx_SpGallery_Utility_TypoScript::parse($settings);
		}


		/**
		 * Set storagePid for persisting objects
		 *
		 * @param integer $storagePid New storagePid
		 * @return void
		 */
		public function setStoragePid($storagePid) {
			$configuration = $this->configurationManager->getConfiguration(
				Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK
			);
			$configuration = Tx_Extbase_Utility_TypoScript::convertPlainArrayToTypoScriptArray($configuration);
			$configuration['persistence.']['storagePid'] = (int) $storagePid;
			$this->configurationManager->setConfiguration($configuration);
		}


		/**
		 * Return one gallery by uid
		 *
		 * @param integer $uid UID of the gallery
		 * @return Tx_SpGallery_Domain_Model_Gallery
		 */
		public function getGallery($uid) {
			if (empty($uid) || !is_numeric($uid)) {
				return NULL;
			}
			return $this->galleryRepository->findByUid($uid);
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
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * @param boolean $generateNames Generate image names from file names
		 * @return boolean TRUE if gallery was modified
		 */
		public function process(Tx_SpGallery_Domain_Model_Gallery $gallery, $generateNames = FALSE) {
			$directory = $gallery->getImageDirectory();
			$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
			$files = Tx_SpGallery_Utility_File::getFiles($directory, TRUE, $allowedTypes);
			$hash = md5(serialize($files));

			if ($hash !== $gallery->getImageDirectoryHash()) {
					// Generate image files
				$imageFiles = Tx_SpGallery_Utility_Image::generate($files, $this->settings);
				$imageFiles = array_intersect_key($files, $imageFiles);
				$imageFiles = array_unique($imageFiles);

					// Remove images without file
				$this->removeDeletedImages($gallery, $imageFiles);

					// Create images from new files
				$this->createNewImages($gallery, $imageFiles, $generateNames);

					// Write new directory hash
				$gallery->setImageDirectoryHash($hash);
				$this->persistenceManager->persistAll();

				return TRUE;
			}

			return FALSE;
		}


		/**
		 * Remove image records without file
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * @param array $files Image files
		 * @return void
		 */
		public function removeDeletedImages(Tx_SpGallery_Domain_Model_Gallery $gallery, array $files) {
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
		 * Remove all gallery images
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * @return void
		 */
		public function removeAllImages(Tx_SpGallery_Domain_Model_Gallery $gallery) {
			$images = $this->imageRepository->findByGallery($gallery);
			foreach ($images as $image) {
				$this->imageRepository->remove($image);
			}
			$this->persistenceManager->persistAll();
		}


		/**
		 * Create image records from new files
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * @param array $files Image files
		 * @param boolean $generateName Generate image name from file name
		 * @return void
		 */
		public function createNewImages(Tx_SpGallery_Domain_Model_Gallery $gallery, array $files, $generateName = FALSE) {
			if (empty($files)) {
				return;
			}

			$modified = FALSE;

				// Generate image records
			foreach ($files as $key => $file) {
					// Search for an existing image
				$fileName = str_replace(PATH_site, '', $file);
				$image = $this->imageRepository->findOneByGalleryAndFileName($gallery, $fileName);

					// Create new image object
				if (empty($image)) {
					$imageRow = array(
						'file_name' => $fileName,
						'gallery'   => $gallery,
					);
					$image = $this->objectBuilder->create('Tx_SpGallery_Domain_Model_Image', $imageRow);
				}

					// Generate file information
				$image->generateImageInformation();
				if ($generateName) {
					$image->generateImageName();
				}

					// Complete gallery
				$this->imageRepository->add($image);
				$gallery->addImage($image);
				$modified = TRUE;
			}

			if ($modified) {
				$this->persistenceManager->persistAll();
			}
		}

	}
?>