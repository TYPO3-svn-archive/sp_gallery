<?php
namespace Speedprogs\SpGallery\Utility;

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
 * Utility to manage templates
 */
class TemplateUtility {

	/**
	 * Renders a template
	 *
	 * @param string $extensionKey The extension key
	 * @param string $templateFile Absolut path to template file
	 * @param array $variables The template variables
	 * @param string $layoutRootPath Path to layouts
	 * @param string $partialRootPath Path to partials
	 * @return string The rendered content
	 */
	static public function render($extensionKey, $templateFile, array $variables = array(), $layoutRootPath = NULL, $partialRootPath = NULL) {
		$content = self::renderRaw($extensionKey, $templateFile, $variables, $layoutRootPath, $partialRootPath);
		return preg_replace('/^[ \t]*[\r\n]+/m', '', $content);
	}

	/**
	 * Renders a template and returns RAW content
	 *
	 * @param string $extensionKey The extension key
	 * @param string $templateFile Absolut path to template file
	 * @param array $variables The template variables
	 * @param string $layoutRootPath Path to layouts
	 * @param string $partialRootPath Path to partials
	 * @return string Raw rendered content
	 */
	static public function renderRaw($extensionKey, $templateFile, array $variables = array(), $layoutRootPath = NULL, $partialRootPath = NULL) {
		if (empty($extensionKey) || empty($templateFile)) {
			return '';
		}
		// Get template file
		$templateFile = \TYPO3\CMS\Core\Utility\GenralUtility::getFileAbsFileName($templateFile);
		// Create Fluid view
		$view = \TYPO3\CMS\Core\Utility\GenralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setLayoutRootPath($layoutRootPath);
		$view->setPartialRootPath($partialRootPath);
		$view->setTemplatePathAndFilename($templateFile);
		$view->getRequest()->setControllerExtensionName($extensionKey);
		// Assign variables to template
		foreach ($variables as $key => $value) {
			$view->assign($key, $value);
		}
		// Render content
		return $view->render();
	}

}
?>