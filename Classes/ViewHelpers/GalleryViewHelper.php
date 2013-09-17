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
 * Gallery view helper
 */
class GalleryViewHelper extends AbstractGalleryViewHelper {

	/**
	 * @var string
	 */
	protected $themePath = 'EXT:sp_gallery/Resources/Public/Themes/';

	/**
	 * @var string
	 */
	protected $templateFile = 'Javascript/Gallery.js';

	/**
	 * @var string
	 */
	protected $tag = '<script type="text/javascript">|</script>';

	/**
	 * Renders the jQuery gallery
	 *
	 * @param mixed $gallery The gallery to render
	 * @param string $elementId ID of the HTML element to render gallery
	 * @param string $infoId ID of the HTML element to render detailed gallery info
	 * @param integer $show UID of the image to show after loading
	 * @return string Rendered content
	 */
	public function render($gallery = NULL, $elementId = NULL, $infoId = NULL, $show = NULL) {
		if ($gallery === NULL) {
			$gallery = $this->renderChildren();
		}
		if (!$gallery instanceof \Speedprogs\SpGallery\Domain\Model\Gallery) {
			throw new Exception('No valid gallery given to render');
		}
		// Check container id
		$elementId = trim($elementId);
		if (empty($elementId)) {
			throw new Exception('No valid HTML element ID given to render gallery');
		}
		// Escape options
		$options = array();
		if (!empty($this->settings['galleria'])) {
			foreach ($this->settings['galleria'] as $key => $option) {
				if ($option['value'] !== '') {
					$options[$key] = $this->escapeValue($option['value'], $option['type']);
				}
			}
		}
		// Get image files
		$images = $this->getGalleryImages($gallery);
		// Add index of the image to show after loading
		if ($show !== NULL && !empty($images[$show])) {
			$tempImages = array_values($images);
			$options['show'] = (int) array_search($images[$show], $tempImages);
		}
		return $this->renderTemplate($elementId, $infoId, $images, $options);
	}

	/**
	 * Renders the Javascript template as described in TypoScript
	 *
	 * @param string $elementId The id of the DIV container in HTML template
	 * @param string $infoId ID of the HTML element to render detailed gallery info
	 * @param array $images The image files grouped by size
	 * @param array $options Javascript options for the galleria plugin
	 * @param integer $show UID of the image to show after loading
	 * @return string Rendered content
	 */
	protected function renderTemplate($elementId, $infoId, array $images, array $options) {
		// Get settings
		$extensionKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
		$themeFile    = $this->getThemeFile();
		$templateFile = $this->getTemplatePathAndFilename();
		// Assign variables to template
		$variables = array(
			'themeFile' => $themeFile,
			'elementId' => $elementId,
			'infoId'    => $infoId,
			'images'    => $images,
			'options'   => $options,
			'settings'  => $this->settings,
		);
		// Render template
		$content = \Speedprogs\SpGallery\Utility\TemplateUtility::render($extensionKey, $templateFile, $variables, $this->layoutRootPath, $this->patialRootPath);
		return str_replace('|', $content, $this->tag);
	}

	/**
	 * Returns gallery theme file name
	 *
	 * @return string URL to file
	 */
	protected function getThemeFile() {
		$themePath = $this->themePath;
		$themeName = 'classic';
		if (!empty($this->settings['themesPath'])) {
			$themePath = $this->settings['themesPath'];
		}
		if (!empty($this->settings['theme'])) {
			$themeName = $this->settings['theme'];
		}
		$themeFile = rtrim($themePath, '/') . '/' . $themeName . '/galleria.' . $themeName . '.min.js';
		$themeFile = $GLOBALS['TSFE']->tmpl->getFileName($themeFile);
		return \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($themeFile);
	}

}
?>