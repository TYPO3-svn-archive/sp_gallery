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
 * Crop image view helper
 */
class CropImageViewHelper extends \Speedprogs\SpGallery\ViewHelpers\AbstractTemplateBasedViewHelper {

	/**
	 * @var string
	 */
	protected $templateFile = 'Javascript/Crop.js';

	/**
	 * @var string
	 */
	protected $tag = '<script type="text/javascript">|</script>';

	/**
	 * Renders the Jcrop plugin
	 *
	 * @param mixed $image The image to render
	 * @param string $elementId ID of the HTML element to render Jcrop
	 * @param string $formId ID of the form to set coordinates
	 * @return string Rendered content
	 */
	public function render($image = NULL, $elementId = NULL, $formId = NULL) {
		if ($image === NULL) {
			$image = $this->renderChildren();
		}
		if (!$image instanceof \Speedprogs\SpGallery\Domain\Model\Image) {
			throw new Exception('No valid image given to render');
		}
		// Escape options
		$options = array();
		if (!empty($this->settings['jcrop'])) {
			foreach ($this->settings['jcrop'] as $key => $option) {
				if ($option['value'] !== '') {
					$options[$key] = $this->escapeValue($option['value'], $option['type']);
				}
			}
		}
		return $this->renderTemplate($image, $elementId, $formId, $options);
	}

	/**
	 * Renders the Javascript template as described in TypoScript
	 *
	 * @param \Speedprogs\SpGallery\Domain\Model\Image $image The image
	 * @param string $elementId The id of the DIV container in HTML template
	 * @param string $formId ID of the form to set coordinates
	 * @param array $options Javascript options for the jcrop plugin
	 * @return string Rendered content
	 */
	protected function renderTemplate(\Speedprogs\SpGallery\Domain\Model\Image $image, $elementId, $formId, array $options) {
		// Get settings
		$extensionKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
		$templateFile = $this->getTemplatePathAndFilename();
		// Assign variables to template
		$variables = array(
			'elementId' => $elementId,
			'formId'    => $formId,
			'image'     => $image,
			'options'   => $options,
			'settings'  => $this->settings,
		);
		// Render template
		$content = \Speedprogs\SpGallery\Utility\Template::render($extensionKey, $templateFile, $variables, $this->layoutRootPath, $this->patialRootPath);
		return str_replace('|', $content, $this->tag);
	}

}
?>