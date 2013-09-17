<?php
namespace Speedprogs\SpGallery\ViewHelpers;

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
 * Abstract gallery view helper
 */
abstract class AbstractGalleryViewHelper extends AbstractTemplateBasedViewHelper {

	/**
	 * @var \Speedprogs\SpGallery\Domain\Repository\ImageRepository
	 * @inject
	 */
	protected $imageRepository;

	/**
	 * Returns all gallery images
	 *
	 * @param mixed $gallery The Gallery
	 * @param string $formats Image formats to render
	 * @param boolean $tag Returns images with complete tag
	 * @param integer $count Image count
	 * @return array Image arrays
	 */
	protected function getGalleryImages($gallery, $formats = 'thumb, small, large', $tag = FALSE, $count = 0) {
		if (empty($gallery) || empty($formats)) {
			return array();
		}
		// Load images from persistence
		$offset   = (isset($this->settings['images']['offset']) ? (int) $this->settings['images']['offset'] : 0);
		$limit    = (isset($this->settings['images']['limit'])  ? (int) $this->settings['images']['limit']  : 10);
		$limit    = (!empty($count) ? (int) $count : $limit);
		$ordering = \Speedprogs\SpGallery\Utility\Persistence::getOrdering($this->settings['images']);
		$images   = $this->imageRepository->findByGallery($gallery, $offset, $limit, $ordering);
		// Get attributes
		$allowedTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
		$settings = \Speedprogs\SpGallery\Utility\TypoScript::getSetup('plugin.tx_spgallery.settings');
		$formats = array_unique(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $formats, TRUE));
		$checkedFiles = array();
		$imageFiles = array();
		$result = array();
		foreach ($formats as $format) {
			if (empty($settings[$format . 'Image.'])) {
				continue;
			}
			foreach ($images as $image) {
				$fileName = $image->getFileName();
				$uid = $image->getUid();
				// Check if file exists
				if (!isset($checkedFiles[$uid])) {
					$checkedFiles[$uid] = (bool) @file_exists(PATH_site . $fileName);
				}
				if ($checkedFiles[$uid] === FALSE) {
					continue;
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
			$processedFiles = \Speedprogs\SpGallery\Utility\Image::convert($imageFiles, $settings[$format . 'Image.'], $tag);
			foreach ($processedFiles as $uid => $file) {
				$result[$uid]['converted'][$format] = $file;
			}
		}
		return $result;
	}

}
?>