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
		 * Returns the MD5 hash of a file
		 *
		 * @param string $fileName Path to the file
		 * @return string Generated hash or an empty string if file not found
		 */
		static public function getFileHash($fileName) {
			// Get md5 from local file
			if (self::isLocalUrl($fileName)) {
				$fileName = self::getLocalUrlPath($fileName);
				return md5_file($fileName);
			}

			// Get md5 from external file
			$contents = t3lib_div::getURL($fileName);
			if (!empty($contents)) {
				return md5($contents);
			}

			return '';
		}


		/**
		 * Get last modification time of a file or directory
		 *
		 * Note: Works only on unix systems
		 *
		 * @param string $fileName Path to the file
		 * @return integer Timestamp of the modification time
		 */
		static public function getModificationTime($fileName) {
			// clearstatcache();
			return (int) @filemtime($fileName);
		}


		/**
		 * Get file type
		 *
		 * @param string $fileName Path to the file
		 * @return string File type
		 */
		static public function getFileType($fileName) {
			$dot = strrpos($fileName, '.');
			if ($dot === FALSE) {
				return '';
			}
			return substr($fileName, $dot + 1);
		}


		/**
		 * Copy a file
		 *
		 * @param string $fromFileName Existing file
		 * @param string $toFileName File name of the new file
		 * @param boolean $overwrite Existing A file with new name will be overwritten if set
		 * @return boolean TRUE if success
		 */
		static public function copyFile($fromFileName, $toFileName, $overwrite = FALSE) {
			if (empty($fromFileName) || empty($toFileName)) {
				return FALSE;
			}

				// Check if file already exists
			$toFileExists = self::fileExists($toFileName);
			if ($toFileExists && !$overwrite) {
				return FALSE;
			}

				// Check if target directory exists
			if (!self::fileExists(dirname($toFileName))) {
				return FALSE;
			}

				// Get local url
			if (self::isLocalUrl($fromFileName)) {
				$fromFileName = self::getAbsolutePathFromUrl($fromFileName);
			}

				// Get file content
			$fromFile = t3lib_div::getURL($fromFileName);
			if ($fromFile === FALSE) {
				return FALSE;
			}

				// Remove existing when successfully fetched new file
			if ($toFileExists) {
				unlink($toFileName);
			}

				// Copy file to new name
			$result = t3lib_div::writeFile($toFileName, $fromFile);
			return ($result !== FALSE);
		}


		/**
		 * Returns information about uploaded file
		 *
		 * @param string $field Path to field (e.g. tx_myext_pi1.myObject.myAttribute)
		 * @return array File information
		 */
		static public function getFileInfo($field) {
			$arrayKeys = t3lib_div::trimExplode('.', $field, TRUE);

				// No information found
			if (empty($_FILES[$arrayKeys[0]]['tmp_name'])) {
				return array();
			}

				// Single file structure
			if (is_string($_FILES[$arrayKeys[0]]['tmp_name'])) {
				return $_FILES[$arrayKeys[0]];
			}

				// Multi file structure
			if (is_array($_FILES[$arrayKeys[0]]['tmp_name'])) {
				$fileInfo = array();
				$fileArray = $_FILES[$arrayKeys[0]];
				array_shift($arrayKeys);

				foreach ($fileArray as $key => $values) {
					$info = $values;
					foreach ($arrayKeys as $arrayKey) {
						if (isset($info[$arrayKey])) {
							$info = $info[$arrayKey];
						}
					}
					$fileInfo[$key] = (!is_array($info) ? $info : NULL);
				}

				return $fileInfo;
			}

			return array();
		}


		/**
		 * Returns an array with image information
		 *
		 * @param string $fileName The file name
		 * @return array Image information
		 */
		static public function getImageInfo($fileName) {
			$result = array(
				'file'   => '',
				'size'   => 0,
				'type'   => '',
				'height' => 0,
				'width'  => 0,
			);

			if (empty($fileName)) {
				return $result;
			}

			$fileName = t3lib_div::getFileAbsFileName($fileName);
			if (!self::fileExists($fileName)) {
				return $result;
			}

				// Get basic information
			$result['file'] = $fileName;
			$result['size'] = (int) filesize($fileName);

				// Get image information
			$imageInfo = getimagesize($fileName);
			$result['height'] = (!empty($imageInfo[1]) ? (int) $imageInfo[1] : 0);
			$result['width']  = (!empty($imageInfo[0]) ? (int) $imageInfo[0] : 0);

				// Get file type
			$result['type'] = self::getFileType($fileName);
			if (!empty($imageInfo['mime']) && strpos($imageInfo['mime'], 'application') === FALSE) {
				$result['type'] = str_replace('image/', '', $imageInfo['mime']);
			}

			return $result;
		}


		/**
		 * Move a file or folder
		 *
		 * @param string $fromFileName Existing file
		 * @param string $toFileName File name of the new file
		 * @param boolean $overwrite Existing A file with new name will be overwritten if set
		 * @return boolean TRUE if success
		 */
		static public function moveFile($fromFileName, $toFileName, $overwrite = FALSE) {
			$result = self::copyFile($fromFileName, $toFileName, $overwrite);
			if ($result && self::isAbsolutePath($fromFileName)) {
				unlink($fromFileName);
			}
			return $result;
		}


		/**
		 * Check if a URL is located to current server
		 *
		 * @param string $urlToFile URL of the file
		 * @return boolean TRUE if given file is local
		 */
		static public function isLocalUrl($urlToFile) {
			return t3lib_div::isOnCurrentHost($urlToFile);
		}


		/**
		 * Check if a filename is an absolute path in local file system
		 *
		 * @param string $path Path to file
		 * @return boolean TRUE if given path is absolute
		 */
		static public function isAbsolutePath($path) {
			return (strpos($path, PATH_site) === 0);
		}


		/**
		 * Returns absolute path on local file system from an url
		 *
		 * @param string $url Url to file
		 * @return string Absolute path to file
		 */
		static public function getAbsolutePathFromUrl($url) {
			$hostUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
			return PATH_site . str_ireplace($hostUrl, '', $url);
		}


		/**
		 * Returns url from an absolute path on local file system
		 *
		 * @param string $path Absolute path to file
		 * @return string Url to file
		 */
		static public function getUrlFromAbsolutePath($path) {
			$hostUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
			return $hostUrl . str_replace(PATH_site, '', $path);
		}


		/**
		 * Returns local file name from URL if located to current server
		 *
		 * DEPRECATED, please use getAbsolutePathFromUrl !!!
		 *
		 * @param string $urlToFile URL of the file
		 * @return string Absolute path to file
		 */
		static public function getLocalUrlPath($urlToFile) {
			return self::getAbsolutePathFromUrl($urlToFile);
		}


		/**
		 * Returns absolute path to given directory
		 *
		 * @param string $path Path to the directory
		 * @return boolean TRUE if success
		 */
		static public function createDirectory($path) {
			if (empty($path)) {
				return FALSE;
			}

			$result = TRUE;
			if (!self::fileExists(PATH_site . $path)) {
				$result = t3lib_div::mkdir_deep(PATH_site, $path);
			}

			return !is_string($result);
		}


		/**
		 * Returns absolute path to given directory
		 *
		 * @param string $path Path to the file / directory
		 * @return string Relative path
		 */
		static public function getRelativeDirectory($path) {
			if (empty($path)) {
				return '';
			}

			$path = t3lib_div::getFileAbsFileName($path);
			$path = str_replace(PATH_site, '', $path);
			return rtrim($path, '/') . '/';
		}


		/**
		 * Returns absolute path to given directory
		 *
		 * @param string $path Path to the file / directory
		 * @param boolean $create Create if not exists
		 * @return string Absolute path
		 */
		static public function getAbsoluteDirectory($path, $create = TRUE) {
			if (empty($path)) {
				return '';
			}

			if (self::isAbsolutePath($path)) {
				return $path;
			}

			if ($create && self::createDirectory($path)) {
				$path = t3lib_div::getFileAbsFileName($path);
				return rtrim($path, '/') . '/';
			}

			return '';
		}


		/**
		 * Check if a file, URL or directory exists
		 *
		 * @param string $fileName Path to the file
		 * @return boolean TRUE if file exists
		 */
		static public function fileExists($fileName) {
			if (empty($fileName)) {
				return FALSE;
			}

			if (is_dir($fileName)) {
				return (bool) file_exists($fileName);
			}

			$result = @fopen($fileName, 'r');
			return ($result !== FALSE);
		}


		/**
		 * Move uploaded file to given directory
		 *
		 * @param string $tempname Temporary file name
		 * @param string $filename Original file name
		 * @param string $directory Directory path
		 * @return New file name
		 */
		static public function moveUploadedFile($tempname, $filename, $directory = 'uploads/') {
			$basicFileFunctions = t3lib_div::makeInstance('t3lib_basicFileFunctions');
			$newFilename = $basicFileFunctions->getUniqueName($filename, self::getAbsoluteDirectory($directory));
			if (t3lib_div::upload_copy_move($tempname, $newFilename)) {
				return basename($newFilename);
			}

			return '';
		}

	}
?>