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
	 * Utilities to manage repositories
	 */
	class Tx_SpGallery_Utility_Repository {

		/**
		 * Returns ordering of record list
		 *
		 * @param array $settings TypoScript setup
		 * @return array Ordering
		 */
		static public function getOrdering(array $settings) {
				// Get order direction
			$desc = Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING;
			$asc  = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
			$direction = (!empty($settings['orderDirection']) ? $settings['orderDirection'] : 'asc');
			$direction = ($direction === 'asc' ? $asc : $desc);

				// Get order field
			$orderBy = (!empty($settings['orderBy']) ? $settings['orderBy'] : 'crdate');
			$orderBy = ($orderBy === 'directory' ? 'imageDirectory' : $orderBy);
			if (!in_array($orderBy, array('name', 'tstamp', 'crdate', 'sorting'))) {
				$orderBy = 'crdate';
			}

			return array($orderBy => $direction);
		}

	}
?>