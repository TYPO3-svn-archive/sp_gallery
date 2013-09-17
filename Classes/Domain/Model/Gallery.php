<?php
namespace Speedprogs\SpGallery\Domain\Model;

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
 * Gallery
 */
class Gallery extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Name of the gallery
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $name;

	/**
	 * Description of the gallery
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Image directory in filesystem
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $imageDirectory;

	/**
	 * Image directory content hash
	 *
	 * @var string
	 */
	protected $imageDirectoryHash;

	/**
	 * Last image directory
	 *
	 * @var string
	 */
	protected $lastImageDirectory;

	/**
	 * Images
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Speedprogs\SpGallery\Domain\Model\Image>
	 * @lazy
	 */
	protected $images;

	/**
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $imageDirectory
	 * @return void
	 */
	public function setImageDirectory($imageDirectory) {
		$this->imageDirectory = $imageDirectory;
		if (empty($this->lastImageDirectory)) {
			$this->setLastImageDirectory($imageDirectory);
		}
	}

	/**
	 * @return string
	 */
	public function getImageDirectory() {
		return $this->imageDirectory;
	}

	/**
	 * @param string $imageDirectoryHash
	 * @return void
	 */
	public function setImageDirectoryHash($imageDirectoryHash) {
		$this->imageDirectoryHash = $imageDirectoryHash;
	}

	/**
	 * @return string
	 */
	public function getImageDirectoryHash() {
		return $this->imageDirectoryHash;
	}

	/**
	 * @param string $lastImageDirectory
	 * @return void
	 */
	public function setLastImageDirectory($lastImageDirectory) {
		$this->lastImageDirectory = $lastImageDirectory;
	}

	/**
	 * @return string
	 */
	public function getLastImageDirectory() {
		return $this->lastImageDirectory;
	}

	/**
	 * @param array $images
	 * @return void
	 */
	public function setImages(array $images) {
		foreach ($images as $image) {
			$this->images->attach($image);
		}
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Speedprogs\SpGallery\Domain\Model\Image>
	 */
	public function getImages() {
		return $this->images;
	}

	/**
	 * @param \Speedprogs\SpGallery\Domain\Model\Image $image
	 * @return void
	 */
	public function addImage(\Speedprogs\SpGallery\Domain\Model\Image $image) {
		$this->images->attach($image);
	}

	/**
	 * @param \Speedprogs\SpGallery\Domain\Model\Image $tag
	 * @return void
	 */
	public function removeImage(\Speedprogs\SpGallery\Domain\Model\Image $image) {
		$this->images->detach($image);
	}

	/**
	 * Generate directory hash
	 *
	 * @return void
	 */
	public function generateDirectoryHash() {
		$directory = $this->getImageDirectory();
		if (!empty($directory)) {
			$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
			$files = \Speedprogs\SpGallery\Utility\File::getFiles($directory, TRUE, $allowedTypes);
			$hash = md5(serialize($files));
			$this->setImageDirectoryHash($hash);
		}
	}

	/**
	 * Return human-readable identifier of the object
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}

}
?>