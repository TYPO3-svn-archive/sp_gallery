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
 * Teaser images view helper
 */
class TeaserImagesViewHelper extends AbstractGalleryViewHelper {

	/**
	 * Renders the teaser images of a gallery
	 *
	 * @param mixed $gallery Gallery to render
	 * @param string $index The name of the template variable for the index
	 * @param string $element The name of the template variable for the image element
	 * @return string Rendered output
	 */
	public function render($gallery, $index = 'uid', $element = 'image') {
		// Get images
		$imageCount = 5;
		if (!empty($this->settings['teaserImageCount'])) {
			$imageCount = (int) $this->settings['teaserImageCount'];
		}
		if (!empty($this->settings['images']['limit'])) {
			$imageCount = (int) $this->settings['images']['limit'];
		}
		$images = $this->getGalleryImages($gallery, 'teaser', FALSE, $imageCount);
		// Render content
		$content = '';
		foreach ($images as $uid => $image) {
			$this->templateVariableContainer->add($index, $uid);
			$this->templateVariableContainer->add($element, $image['converted']['teaser']);
			$content .= $this->renderChildren();
			$this->templateVariableContainer->remove($index);
			$this->templateVariableContainer->remove($element);
		}
		return $content;
	}

}
?>