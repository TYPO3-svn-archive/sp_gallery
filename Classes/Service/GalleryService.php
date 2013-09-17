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
	 * @var \Speedprogs\SpGallery\Domain\Repository\ImageRepository
	 * @inject
	 */
	protected $imageRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var \Speedprogs\SpGallery\Object\ObjectBuilder
	 * @inject
	 */
	protected $objectBuilder;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

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
		$setup = \TYPO3\CMS\Extbase\Service\TypoScriptService::convertPlainArrayToTypoScriptArray($setup);
		$setup['persistence.']['storagePid'] = (int) $storagePid;
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
		$gallery = $this->galleryRepository->findByUid($uid);
		if (empty($gallery)) {
			return FALSE;
		}
		return $this->process($gallery, $generateNames);
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
	 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
	 * @param boolean $generateNames Generate image names from file names
	 * @return boolean TRUE if gallery was modified
	 */
	public function process(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery, $generateNames = FALSE) {
		$directory = $gallery->getImageDirectory();
		$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
		$files = \Speedprogs\SpGallery\Utility\File::getFiles($directory, TRUE, $allowedTypes);
		$hash = $this->buildHash($files);
		if ($hash !== $gallery->getImageDirectoryHash()) {
			// Generate image files
			$imageFiles = \Speedprogs\SpGallery\Utility\Image::generate($files, $this->settings);
			$imageFiles = array_intersect_key($files, $imageFiles);
			$imageFiles = array_unique($imageFiles);
			// Remove old images
			$this->removeOldImages($gallery);
			// Remove images without file
			$this->removeDeletedImages($gallery, $imageFiles);
			// Create images from new files
			$this->createNewImages($gallery, $imageFiles, $generateNames);
			// Set new directory
			$gallery->setImageDirectoryHash($hash);
			$gallery->setLastImageDirectory($directory);
			$this->persistenceManager->persistAll();
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * Remove image records without file
	 *
	 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
	 * @param array $files Image files
	 * @return void
	 */
	public function removeDeletedImages(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery, array $files) {
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
	 * Remove all gallery images from old path
	 *
	 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
	 * @return void
	 */
	public function removeOldImages(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery) {
		$directory = $gallery->getLastImageDirectory();
		if (empty($directory)) {
			return;
		}
		// Directory has not changed
		if ($directory === $gallery->getImageDirectory()) {
			return;
		}
		$modified = FALSE;
		// Find records with old image directory
		$images = $this->imageRepository->findByGallery($gallery);
		foreach ($images as $image) {
			$fileName = $image->getFileName();
			if (strpos($fileName, $directory) === 0) {
				$this->imageRepository->remove($image);
				$modified = TRUE;
			}
		}
		if ($modified) {
			$this->persistenceManager->persistAll();
		}
	}

	/**
	 * Create image records from new files
	 *
	 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
	 * @param array $files Image files
	 * @param boolean $generateName Generate image name from file name
	 * @return void
	 */
	public function createNewImages(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery, array $files, $generateName = FALSE) {
		if (empty($files)) {
			return;
		}
		$modified = FALSE;
		// Generate image records
		foreach ($files as $key => $file) {
			$fileName = str_replace(PATH_site, '', $file);
			// Search for an existing image or create new
			$image = $this->imageRepository->findOneByGalleryAndFileName($gallery, $fileName);
			if (!empty($image)) {
				$image->setDeleted(FALSE);
			} else {
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

	/**
	 * Build the hash to recognize directory changes
	 *
	 * @param array $files All files in the directory
	 * @return string The hash
	 */
	protected function buildHash(array $files) {
		// Add file names
		$text = serialize($files);
		// Add image configuration
		$imageSizes = array('teaser', 'thumb', 'small', 'large');
		foreach ($imageSizes as $size) {
			if (!empty($this->settings[$size . 'Image'])) {
				$text .= serialize($this->settings[$size . 'Image']);
			}
		}
		// Add extension configuration
		$configuration = \Speedprogs\SpGallery\Utility\Backend::getExtensionConfiguration('sp_gallery');
		$text .= (!empty($configuration['generateWhenSaving']) ? 'true' : 'false');
		return md5($text);
	}

}
?>