<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
	 *
	 *  All rights reserved
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as
	 *  published by the Free Software Foundation; either version 2 of
	 *  the License, or (at your option) any later version.
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
	 ******************************************************************/

	/**
	 * Utilities to manage the backend
	 */
	class Tx_SpGallery_Utility_Backend {

		/**
		 * Get current page ID within backend
		 *
		 * @return integer UID of current page
		 */
		static public function getPageId() {
			if (isset($GLOBALS['SOBE'])) {
				return (int) $GLOBALS['SOBE']->viewId;
			}

				// Find UID in "id"
			$pageId = t3lib_div::_GP('id');
			if ($pageId) {
				return (int) $pageId;
			}

				// Find UID in "returnUrl"
			$url = urldecode(t3lib_div::_GP('returnUrl'));
			if ($url) {
				preg_match('/id=([0-9]*)/', $url, $parts);
				if (!empty($parts[1])) {
					return (int) $parts[1];
				}
			}

			return 0;
		}


		/**
		 * Returns the upload directory for a column in TCA
		 *
		 * @param string $table Name of the table in TCA
		 * @param string $field Name of the field
		 * @return string The upload directory
		 */
		static public function getUploadfolder($table, $field) {
			global $TCA;
			t3lib_div::loadTCA($table);
			if (!empty($TCA[$table]['columns'][$field]['config']['uploadfolder'])) {
				return $TCA[$table]['columns'][$field]['config']['uploadfolder'];
			}
			return '';
		}

	}
?>