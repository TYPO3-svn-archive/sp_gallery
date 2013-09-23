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
class Gallery extends \TYPO3\CMS\Core\Resource\Collection\FolderBasedFileCollection {

	/**
	 * Images
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Core\Resource\File>
	 */
	protected $images;

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $images
	 * @return void
	 */
	public function setImages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $images) {
		$this->images->addAll($image);
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getImages() {
		return $this->images;
	}

	/**
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $image
	 * @return void
	 */
	public function addImage(\TYPO3\CMS\Core\Resource\File $image) {
		$this->images->attach($image);
	}

	/**
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $image
	 * @return void
	 */
	public function removeImage(\TYPO3\CMS\Core\Resource\File $image) {
		$this->images->detach($image);
	}

}
?>