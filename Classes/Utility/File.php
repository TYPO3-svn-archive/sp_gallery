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
	 * Utilities to manage files
	 */
	class Tx_SpGallery_Utility_File {

		/**
		 * Get a list of all files in a directory
		 *
		 * @param string $directory Path to the directory
		 * @param boolean $recursive Get subfolder content too
		 * @param string $fileTypes Types of the files to find
		 * @param integer $fileCount Count of files to return
		 * @return array All contained files
		 */
		static public function getFiles($directory, $recursive = FALSE, $fileTypes = '', $fileCount = 0) {
			$directory = t3lib_div::getFileAbsFileName($directory);
			if (!(@is_dir($directory))) {
				return array();
			}

			$fileTypes = t3lib_div::trimExplode(',', $fileTypes, TRUE);
			$result    = array();

			if ($recursive) {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
			} else {
				$files = new DirectoryIterator($directory);
			}

			foreach ($files as $file) {
				if ($file->isFile()) {
					$fileName = $file->getPathname();

						// Check file type
					if (!empty($fileTypes)) {
						$currentType = self::getFileType($fileName);
						if (!in_array($currentType, $fileTypes)) {
							continue;
						}
					}

					$result[] = $fileName;

						// Check file count
					if (!empty($fileCount) && count($result) === $fileCount) {
						break;
					}
				}
			}

			natsort($result);
			return $result;
		}


		/**
		 * Get file type
		 *
		 * @param string $fileName Path to the file
		 * @return string File type
		 */
		public static function getFileType($fileName) {
			$dot = strrpos($fileName, '.');
			if ($dot === FALSE) {
				return '';
			}
			return strtolower(substr($fileName, $dot + 1));
		}

	}
?>