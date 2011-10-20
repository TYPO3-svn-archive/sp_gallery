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
	 * Gallery view helper
	 */
	class Tx_SpGallery_ViewHelpers_GalleryViewHelper extends Tx_SpGallery_ViewHelpers_AbstractGalleryViewHelper {

		/**
		 * @var string
		 */
		protected $wrap = '<script type="text/javascript">|</script>';


		/**
		 * Renders the jQuery gallery
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery Gallery to render
		 * @param string $element ID of the HTML element to render gallery
		 * @return string Rendered gallery
		 */
		public function render($gallery = NULL, $element = NULL) {
			if ($gallery === NULL) {
				$gallery = $this->renderChildren();
			}

			$element = trim($element);
			if (empty($element)) {
				throw new Exception('No valid HTML element ID given to render gallery', 1308305552);
			}

			if (!$gallery instanceof Tx_SpGallery_Domain_Model_Gallery) {
				throw new Exception('No valid gallery given to render', 1308305553);
			}

				// Get images
			$images = array();
			foreach (array('thumb', 'small', 'large') as $name) {
				if (empty($this->settings[$name . 'Image'])) {
					continue;
				}
				$imageFiles = $this->getGalleryImages($gallery, $this->settings[$name . 'Image']);
				foreach ($imageFiles as $key => $imageFile) {
					$images[$key][$name] = $imageFile;
				}
			}

				// Get theme file
			$themeFile = $this->getThemeFile();

				// Build JS
			$result = $this->getGalleryJs($images, $themeFile, $element);
			$result = str_replace('|', $result, $this->wrap);

			return $result;
		}

	}
?>