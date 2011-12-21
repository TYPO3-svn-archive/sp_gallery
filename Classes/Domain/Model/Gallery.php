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
	 * Gallery
	 */
	class Tx_SpGallery_Domain_Model_Gallery extends Tx_Extbase_DomainObject_AbstractEntity {

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
		 * Images
		 *
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_SpGallery_Domain_Model_Image>
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
		 * @param array $images
		 * @return void
		 */
		public function setImages(array $images) {
			foreach ($images as $image) {
				$this->images->attach($image);
			}
		}


		/**
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_SpGallery_Domain_Model_Image>
		 */
		public function getImages() {
			return $this->images;
		}


		/**
		 * @param Tx_SpGallery_Domain_Model_Image $image
		 * @return void
		 */
		public function addImage(Tx_SpGallery_Domain_Model_Image $image) {
			$this->images->attach($image);
		}


		/**
		 * @param Tx_SpGallery_Domain_Model_Image $tag
		 * @return void
		 */
		public function removeImage(Tx_SpGallery_Domain_Model_Image $image) {
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
				$files = Tx_SpGallery_Utility_File::getFiles($directory, TRUE, $allowedTypes);
				$hash = md5(serialize($files));
				$this->setImageDirectoryHash($hash);
			}
		}

	}
?>