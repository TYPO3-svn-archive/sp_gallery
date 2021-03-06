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
 * Image
 */
class Image extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Deleted state
	 *
	 * @var boolean
	 */
	protected $deleted;

	/**
	 * Hidden state
	 *
	 * @var boolean
	 */
	protected $hidden;

	/**
	 * Name of the image
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Description of the image
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Image path in filesystem
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * File size
	 *
	 * @var int
	 */
	protected $fileSize;

	/**
	 * File type
	 *
	 * @var string
	 */
	protected $fileType;

	/**
	 * Image height
	 *
	 * @var int
	 */
	protected $imageHeight;

	/**
	 * Image width
	 *
	 * @var int
	 */
	protected $imageWidth;

	/**
	 * The gallary
	 *
	 * @var \Speedprogs\SpGallery\Domain\Model\Gallery
	 */
	protected $gallery;

	/**
	 * @param boolean $deleted
	 * @return void
	 */
	public function setDeleted($deleted) {
		$this->deleted = (bool) $deleted;
	}

	/**
	 * @return boolean
	 */
	public function getDeleted() {
		return (bool) $this->deleted;
	}

	/**
	 * @param boolean $hidden
	 * @return void
	 */
	public function setHidden($hidden) {
		$this->hidden = (bool) $hidden;
	}

	/**
	 * @return boolean
	 */
	public function getHidden() {
		return (bool) $this->hidden;
	}

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
	 * @param string $fileName
	 * @return void
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * @param integer $fileSize
	 * @return void
	 */
	public function setFileSize($fileSize) {
		$this->fileSize = (int) $fileSize;
	}

	/**
	 * @return integer
	 */
	public function getFileSize() {
		return $this->fileSize;
	}

	/**
	 * @param string $fileType
	 * @return void
	 */
	public function setFileType($fileType) {
		$this->fileType = $fileType;
	}

	/**
	 * @return string
	 */
	public function getFileType() {
		return $this->fileType;
	}

	/**
	 * @param integer $imageHeight
	 * @return void
	 */
	public function setImageHeight($imageHeight) {
		$this->imageHeight = (int) $imageHeight;
	}

	/**
	 * @return integer
	 */
	public function getImageHeight() {
		return $this->imageHeight;
	}

	/**
	 * @param integer $imageWidth
	 * @return void
	 */
	public function setImageWidth($imageWidth) {
		$this->imageWidth = (int) $imageWidth;
	}

	/**
	 * @return integer
	 */
	public function getImageWidth() {
		return $this->imageWidth;
	}

	/**
	 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery
	 * @return void
	 */
	public function setGallery(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery) {
		$this->gallery = $gallery;
	}

	/**
	 * @return \Speedprogs\SpGallery\Domain\Model\Gallery
	 */
	public function getGallery() {
		return $this->gallery;
	}

	/**
	 * Generate image information from filename
	 *
	 * @return void
	 */
	public function generateImageInformation() {
		$fileName = $this->getFileName();
		if (!empty($fileName)) {
			$imageInfo = \Speedprogs\SpGallery\Utility\FileUtility::getImageInfo($fileName);
			$this->setFileSize($imageInfo['size']);
			$this->setFileType($imageInfo['type']);
			$this->setImageHeight($imageInfo['height']);
			$this->setImageWidth($imageInfo['width']);
		}
	}

	/**
	 * Generate image name from filename
	 *
	 * @return void
	 */
	public function generateImageName() {
		$fileName = $this->getFileName();
		if (!empty($fileName)) {
			$name = basename($fileName);
			$name = str_replace('_', ' ',substr($name, 0, strpos($name, '.')));
			$name = ucwords(strtolower($name));
			$this->setName($name);
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