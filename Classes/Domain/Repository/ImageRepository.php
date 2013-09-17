<?php
namespace Speedprogs\SpGallery\Domain\Repository;
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
	 * Repository for Speedprogs\SpGallery\Domain\Model\Image
	 */
	class ImageRepository extends \Speedprogs\SpGallery\Domain\Repository\AbstractRepository {

		/**
		 * Find all images of a gallery
		 *
		 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
		 * @param string $offset Offset to start with
		 * @param string $limit Limit of the results
		 * @param array $ordering Ordering <-> Direction
		 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
		 */
		public function findByGallery(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery, $offset = NULL, $limit = NULL, array $ordering = NULL) {
			$query = $this->createQuery($offset, $limit, $ordering);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->matching($query->equals('gallery', $gallery->getUid()));
			return $query->execute();
		}


		/**
		 * Find an image by gallery and filename
		 *
		 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
		 * @param string $fileName The filename
		 * @param boolean $enableFields Respect enable fields
		 * @return \Speedprogs\SpGallery\Domain\Model\Image
		 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
		 */
		public function findOneByGalleryAndFileName(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery, $fileName, $enableFields = FALSE) {
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectEnableFields($enableFields);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->matching(
				$query->logicalAnd(
					$query->equals('gallery', $gallery),
					$query->equals('fileName', $fileName)
				)
			);
			$query->setLimit(1);
			return $query->execute()->getFirst();
		}

	}
?>