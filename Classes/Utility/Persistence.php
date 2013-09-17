<?php
namespace Speedprogs\SpGallery\Utility;
	/*********************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2012 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 * Utility to manage persistence
	 */
	class Persistence {

		/**
		 * @var string
		 */
		static protected $allowedOrderings = 'name,tstamp,crdate,sorting,imageDirectory,fileName';


		/**
		 * Returns ordering of record list
		 *
		 * @param array $settings TypoScript setup
		 * @return array Ordering
		 */
		static public function getOrdering(array $settings) {
			// Get order direction
			$desc = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;
			$asc  = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;
			$direction = (!empty($settings['orderDirection']) ? $settings['orderDirection'] : 'asc');
			$direction = ($direction === 'asc' ? $asc : $desc);

			// Get order field
			$orderBy = (!empty($settings['orderBy']) ? $settings['orderBy'] : 'crdate');
			$orderBy = ($orderBy === 'directory' ? 'imageDirectory' : $orderBy);
			$orderBy = (strtolower($orderBy) === 'filename' ? 'fileName' : $orderBy);
			if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList(self::$allowedOrderings, $orderBy)) {
				$orderBy = 'crdate';
			}

			return array($orderBy => $direction);
		}


		/**
		 * Order gallery objects by the sorting of the gallery selector in plugin
		 *
		 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $galleries
		 * @return array
		 */
		static public function sortBySelector(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $objects, array $uids, $direction = 'asc') {
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
			);// pages_123
			$pages = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $pages);
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
		 * @param string $extensionKey Extension key
		 * @param string $modelName Name of the model
		 * @return TRUE if a storage page was found
		 */
		static public function hasStoragePage($extensionKey, $modelName) {
			$extensionName  = str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionKey)));
			$modelClassName = 'Tx_' . $extensionName . '_Domain_Model_' . ucfirst($modelName);
			$extensionSetup = \Speedprogs\SpGallery\Utility\TypoScript::getSetup('plugin.tx_' . strtolower($extensionName) . '.persistence');
			$extbaseSetup   = \Speedprogs\SpGallery\Utility\TypoScript::getSetup('config.tx_extbase.persistence');
			$mergedSetup    = \TYPO3\CMS\Extbase\Utility\ArrayUtility::arrayMergeRecursiveOverrule($extbaseSetup, $extensionSetup, FALSE, FALSE);

			if (!empty($mergedSetup['storagePid'])) {
				return TRUE;
			}

			if (!empty($mergedSetup['classes.'][$modelClassName . '.']['newRecordStoragePid'])) {
				return TRUE;
			}

			return FALSE;
		}

	}
?>