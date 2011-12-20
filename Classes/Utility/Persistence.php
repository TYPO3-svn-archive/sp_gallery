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
	 * Utilities to manage persistence
	 */
	class Tx_SpGallery_Utility_Persistence {

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


		/**
		 * Order gallery objects by the sorting of the gallery selector in plugin
		 *
		 * @param Tx_Extbase_Persistence_QueryResult $galleries
		 * @return array
		 */
		static public function sortBySelector(Tx_Extbase_Persistence_QueryResult $objects, array $uids, $direction = 'asc') {
				// Get order of the selected objects, skip folders
			if (empty($uids)) {
				return (array) $objects;
			}

				// Order uids by configured direction
			if ($direction !== 'asc') {
				$uids = array_reverse($uids);
			}

				// Sort galleries by the order of uids in select field
			$result = array_flip($uids);
			foreach ($objects as $object) {
				$result[$object->getUid()] = $object;
			}

			return $result;
		}


		/**
		 * Returns an array of PIDs and UIDs
		 *
		 * @param string $pages List of pages
		 * @return array UIDs and PIDs
		 */
		static public function getIds($pages) {
			$ids = array(
				'uids' => array(),
				'pids' => array(),
			);

			$pages = t3lib_div::trimExplode(',', $pages);
			foreach($pages as $page) {
				$id  = substr($page, strrpos($page, '_') + 1);
				$key = 'uids';
				if (strpos($page, 'pages') !== FALSE) {
					$key = 'pids';
				}
				$ids[$key][] = (int) $id;
			}

			return $ids;
		}


		/**
		 * Check whether a storage page is configured or not
		 *
		 * @param string $modelName Name of the model
		 * @param string $pluginName Name of the plugin
		 * @return TRUE if a storage page was found
		 */
		static public function isStoragePageConfigured($modelName, $pluginName) {
			$extensionSetup = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.' . $pluginName . '.persistence');
			$extbaseSetup   = Tx_SpGallery_Utility_TypoScript::getSetup('config.tx_extbase.persistence');
			$mergedSetup    = Tx_Extbase_Utility_Arrays::arrayMergeRecursiveOverrule($extbaseSetup, $extensionSetup, FALSE, FALSE);

			if (!empty($mergedSetup['storagePid'])) {
				return TRUE;
			}

			if (!empty($mergedSetup['classes.'][$modelName . '.']['newRecordStoragePid'])) {
				return TRUE;
			}

			return FALSE;
		}

	}
?>