<?php
namespace Speedprogs\SpGallery\Hook;

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
 * TCA helper class
 */
class Tca implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Render message for emtpy images tab
	 *
	 * @param array $setup TCA configuration
	 * @param object $parent Reference to calling instance
	 * @return string
	 */
	public function renderEmptyImagesMessage(array $setup, $parent) {
		if (TYPO3_MODE != 'BE') {
			return '';
		}
		$configuration = \Speedprogs\SpGallery\Utility\BackendUtility::getExtensionConfiguration('sp_gallery');
		$message = $setup['fieldConf']['config']['labels']['message_disabled'];
		// Check if scheduler task is running
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('scheduler')) {
			$table = 'tx_scheduler_task';
			$where = 'disable=0 AND classname="Tx_SpGallery_Task_DirectoryObserver" AND lastexecution_time > 0';
			if ($GLOBALS['TYPO3_DB']->exec_SELECTcountRows('1', $table, $where)) {
				$message = $setup['fieldConf']['config']['labels']['message_scheduler'];
			}
		}
		// Check if generate by saving is active
		if (!empty($configuration['generateWhenSaving'])) {
			$message = $setup['fieldConf']['config']['labels']['message_saving'];
		}
		$content = '<div style="padding: 0 0 18px 0;">' . $GLOBALS['LANG']->sL($message) . '</div>';
		// Hook to modify the content
		$this->callHook('renderEmptyImagesMessage', array(
			'parent'   => &$parent,
			'content'  => &$content,
			'setup'    => $setup,
			'extConf'  => $configuration,
		));
		return $content;
	}

	/**
	 * Render image information
	 *
	 * @param array $setup TCA configuration
	 * @param object $parent Reference to calling instance
	 * @return string
	 */
	public function renderImageInformation(array $setup, $parent) {
		if (TYPO3_MODE != 'BE' || empty($setup['fieldConf']['config']['labels'])) {
			return '';
		}
		// Get labels
		$labels = array();
		foreach ($setup['fieldConf']['config']['labels'] as $field => $label) {
			$labels[$field] = $GLOBALS['LANG']->sL($label);
		}
		// Get content
		$fileName = basename($setup['row']['file_name']);
		$fileSize = \TYPO3\CMS\Core\Utility\GeneralUtility::formatSize($setup['row']['file_size'], 'B|KB|MB|GB');
		$fileType = strtoupper($setup['row']['file_type']);
		$content = '
			<table>
				<tr><th>' . $labels['file_name']    . '&nbsp;&nbsp;</th><td>' . htmlspecialchars($fileName)         . '</td></tr>
				<tr><th>' . $labels['file_size']    . '&nbsp;&nbsp;</th><td>' . htmlspecialchars($fileSize)         . '</td></tr>
				<tr><th>' . $labels['file_type']    . '&nbsp;&nbsp;</th><td>' . htmlspecialchars($fileType)         . '</td></tr>
				<tr><th>' . $labels['image_width']  . '&nbsp;&nbsp;</th><td>' . (int) $setup['row']['image_width']  . 'px</td></tr>
				<tr><th>' . $labels['image_height'] . '&nbsp;&nbsp;</th><td>' . (int) $setup['row']['image_height'] . 'px</td></tr>
			</table>
		';
		// Hook to modify the content
		$this->callHook('renderImageInformation', array(
			'parent'  => &$parent,
			'content' => &$content,
			'labels'  => $labels,
			'setup'   => $setup,
		));
		return $content;
	}

	/**
	 * Checks the SC_OPTIONS for valid hooks
	 *
	 * @param string $method The method name
	 * @param array $parameters The parameters for the hook
	 * @return void
	 */
	protected function callHook($method, array $parameters) {
		$hookClasses = NULL;
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_SpGallery_Hook_Tca'][$method])) {
			$hookClasses = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_SpGallery_Hook_Tca'][$method];
		}
		if (is_array($hookClasses)) {
			foreach ($hookClasses as $class) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($class, $parameters, $this);
			}
		}
	}

}
?>