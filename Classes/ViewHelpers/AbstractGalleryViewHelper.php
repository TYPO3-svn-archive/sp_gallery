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
	 * Abstract gallery view helper
	 */
	class Tx_SpGallery_ViewHelpers_AbstractGalleryViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * @var Tx_Extbase_Object_ObjectManager
		 */
		protected $objectManager;

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var Tx_SpGallery_Service_GalleryImageService
		 */
		protected $imageService;

		/**
		 * @var Tx_SpGallery_Domain_Repository_ImageRepository
		 */
		protected $imageRepository;


		/**
		 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
			$this->settings = $configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
			if (empty($this->settings)) {
				throw new Exception('No configuration found for gallery view helper', 1308305554);
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
		 * @param Tx_SpGallery_Domain_Repository_ImageRepository $imageRepository
		 * @return void
		 */
		public function injectImageRepository(Tx_SpGallery_Domain_Repository_ImageRepository $imageRepository) {
			$this->imageRepository = $imageRepository;
		}


		/**
		 * Returns all gallery images
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The Gallery
		 * @param string $formats Image formats to render
		 * @param boolean $tag Returns images with complete tag
		 * @param integer $count Image count
		 * @return array Image arrays
		 */
		protected function getGalleryImages(Tx_SpGallery_Domain_Model_Gallery $gallery, $formats = 'thumb, small, large', $tag = FALSE, $count = 0) {
			if (empty($gallery) || empty($formats)) {
				return array();
			}

				// Load images from persistence
			$offset   = (isset($this->settings['images']['offset']) ? (int) $this->settings['images']['offset'] : 0);
			$limit    = (isset($this->settings['images']['limit'])  ? (int) $this->settings['images']['limit']  : 10);
			$limit    = (!empty($count) ? (int) $count : $limit);
			$ordering = Tx_SpGallery_Utility_Repository::getOrdering($this->settings['images']);
			$images   = $this->imageRepository->findByGallery($gallery, $offset, $limit, $ordering);

				// Load image service
			if ($this->imageService === NULL) {
				$this->imageService = $this->objectManager->get('Tx_SpGallery_Service_GalleryImage');
			}

			$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
			$settings = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.tx_spgallery.settings');
			$formats = array_unique(t3lib_div::trimExplode(',', $formats, TRUE));
			$imageFiles = array();
			$result = array();

			foreach ($formats as $format) {
				if (empty($settings[$format . 'Image.'])) {
					continue;
				}

				foreach ($images as $image) {
						// Add file for current format
					if ($image instanceof Tx_SpGallery_Domain_Model_Image) {
						$fileName = $image->getFileName();
						$uid = $image->getUid();
					} elseif (is_array($image)) {
						$fileName = $image['file_name'];
						$uid = $image['uid'];
					}
					$imageFiles[$uid] = $fileName;

						// Prepare result array
					if (empty($result[$uid])) {
						$result[$uid] = array(
							'original'  => $image,
							'converted' => array(),
						);
					}
				}

					// Convert images
				$processedFiles = $this->imageService->processImageFiles($imageFiles, $settings[$format . 'Image.'], $tag);
				foreach ($processedFiles as $uid => $file) {
					$result[$uid]['converted'][$format] = $file;
				}

			}

			return $result;
		}

	}
?>