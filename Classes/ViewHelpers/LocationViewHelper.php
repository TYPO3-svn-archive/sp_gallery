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
 * Location header url view helper
 */
class LocationViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Disable the escaping interceptor
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Renders location header url
	 *
	 * @param string $content Content
	 * @return string Raw content
	 */
	public function render($content = NULL) {
		if ($content === NULL) {
			$content = $this->renderChildren();
		}
		return \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($content);
	}

}
?>