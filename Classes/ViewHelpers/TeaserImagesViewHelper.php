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
	 * Teaser images view helper
	 */
	class Tx_SpGallery_ViewHelpers_TeaserImagesViewHelper extends Tx_SpGallery_ViewHelpers_AbstractGalleryViewHelper {

		/**
		 * Renders the teaser images of a gallery
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery Gallery to render
		 * @return string Rendered gallery
		 */
		public function render($gallery = NULL) {
			if ($gallery === NULL) {
				$gallery = $this->renderChildren();
			}

			if (!$gallery instanceof Tx_SpGallery_Domain_Model_Gallery) {
				throw new Exception('No valid gallery given to render', 1308305558);
			}

				// Get images
			$imageCount    = (!empty($this->settings['teaserImageCount']) ? (int)$this->settings['teaserImageCount'] : 5);
			$imageSettings = (!empty($this->settings['teaserImage']) ? $this->settings['teaserImage'] : array());
			$images        = $this->getGalleryImages($gallery, $imageSettings, TRUE, $imageCount);

			return implode(LF, $images);
		}

	}
?>