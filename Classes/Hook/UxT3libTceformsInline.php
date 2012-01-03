<?php
	/*********************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 * XCLASS for TCA fields of type "inline"
	 */
	class ux_t3lib_TCEforms_inline extends t3lib_TCEforms_inline {

		/**
		 * @var boolean
		 */
		protected $isLoaded = FALSE;


		/**
		 * Renders the HTML header for a foreign record, such as the title, toggle-function, drag'n'drop, etc.
		 * Later on the command-icons are inserted here.
		 *
		 * Note: This method extends the original one in parent class with thumbnails instead of sprite icons in header
		 *
		 * @param	string		$parentUid: The uid of the parent (embedding) record (uid or NEW...)
		 * @param	string		$foreign_table: The foreign_table we create a header for
		 * @param	array		$rec: The current record of that foreign_table
		 * @param	array		$config: content of $PA['fieldConf']['config']
		 * @param	boolean		$isVirtualRecord:
		 * @return	string		The HTML code of the header
		 */
		public function renderForeignRecordHeader($parentUid, $foreign_table, $rec, $config, $isVirtualRecord = FALSE) {
				// Init:
			$objectId = $this->inlineNames['object'] . self::Structure_Separator . $foreign_table . self::Structure_Separator . $rec['uid'];
			$expandSingle = $config['appearance']['expandSingle'] ? 1 : 0;
				// we need the returnUrl of the main script when loading the fields via AJAX-call (to correct wizard code, so include it as 3rd parameter)
			$onClick = "return inline.expandCollapseRecord('" . htmlspecialchars($objectId) . "', $expandSingle, '" . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')) . "')";

				// Pre-Processing:
			$isOnSymmetricSide = t3lib_loadDBGroup::isOnSymmetricSide($parentUid, $config, $rec);
			$hasForeignLabel = !$isOnSymmetricSide && $config['foreign_label'] ? TRUE : FALSE;
			$hasSymmetricLabel = $isOnSymmetricSide && $config['symmetric_label'] ? TRUE : FALSE;
				// Get the record title/label for a record:
				// render using a self-defined user function
			if ($GLOBALS['TCA'][$foreign_table]['ctrl']['label_userFunc']) {
				$params = array(
					'table' => $foreign_table,
					'row' => $rec,
					'title' => '',
					'isOnSymmetricSide' => $isOnSymmetricSide,
					'parent' => array(
						'uid' => $parentUid,
						'config' => $config,
					),
				);
				$null = NULL; // callUserFunction requires a third parameter, but we don't want to give $this as reference!
				t3lib_div::callUserFunction($GLOBALS['TCA'][$foreign_table]['ctrl']['label_userFunc'], $params, $null);
				$recTitle = $params['title'];
					// render the special alternative title
			} elseif ($hasForeignLabel || $hasSymmetricLabel) {
				$titleCol = $hasForeignLabel ? $config['foreign_label'] : $config['symmetric_label'];
				$foreignConfig = $this->getPossibleRecordsSelectorConfig($config, $titleCol);
					// Render title for everything else than group/db:
				if ($foreignConfig['type'] != 'groupdb') {
					$recTitle = t3lib_BEfunc::getProcessedValueExtra($foreign_table, $titleCol, $rec[$titleCol], 0, 0, FALSE);
						// Render title for group/db:
				} else {
						// $recTitle could be something like: "tx_table_123|...",
					$valueParts = t3lib_div::trimExplode('|', $rec[$titleCol]);
					$itemParts = t3lib_div::revExplode('_', $valueParts[0], 2);
					$recTemp = t3lib_befunc::getRecordWSOL($itemParts[0], $itemParts[1]);
					$recTitle = t3lib_BEfunc::getRecordTitle($itemParts[0], $recTemp, FALSE);
				}
				$recTitle = t3lib_BEfunc::getRecordTitlePrep($recTitle);
				if (!strcmp(trim($recTitle), '')) {
					$recTitle = t3lib_BEfunc::getNoRecordTitle(TRUE);
				}
					// render the standard
			} else {
				$recTitle = t3lib_BEfunc::getRecordTitle($foreign_table, $rec, TRUE);
			}

			$altText = t3lib_BEfunc::getRecordIconAltText($rec, $foreign_table);
			$iconImg = t3lib_iconWorks::getSpriteIconForRecord($foreign_table, $rec, array('title' => htmlspecialchars($altText), 'id' => $objectId . '_icon'));

				// Use thumbnail instead of sprite icon in header
				if (!empty($config['appearance']['renderItemImage']) && !empty($config['itemImage'])) {
					$iconImg = $this->getHeaderImage($foreign_table, $rec, $config['itemImage'], $altText);
				}

			$label = '<span id="' . $objectId . '_label">' . $recTitle . '</span>';
			if (!$isVirtualRecord) {
				$iconImg = $this->wrapWithAnchor($iconImg, '#', array('onclick' => $onClick));
				$label = $this->wrapWithAnchor($label, '#', array('onclick' => $onClick, 'style' => 'display: block;'));
			}

			$ctrl = $this->renderForeignRecordHeaderControl($parentUid, $foreign_table, $rec, $config, $isVirtualRecord);

				// @TODO: Check the table wrapping and the CSS definitions
			$header =
					'<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-right: ' . $this->inlineStyles['margin-right'] . 'px;"' .
					($this->fObj->borderStyle[2] ? ' background="' . htmlspecialchars($this->backPath . $this->fObj->borderStyle[2]) . '"' : '') .
					($this->fObj->borderStyle[3] ? ' class="' . htmlspecialchars($this->fObj->borderStyle[3]) . '"' : '') . '>' .
					'<tr class="class-main12"><td width="18" id="' . $objectId . '_iconcontainer">' . $iconImg . '</td><td align="left"><strong>' . $label . '</strong></td><td align="right">' . $ctrl . '</td></tr></table>';

			return $header;
		}


		/**
		 * Process an image file
		 *
		 * @param string $table Name of the table
		 * @param array $record The record data
		 * @param array $configuration Image configuration
		 * @param string $altText The alt text of the image
		 * @return HTML content of the image
		 */
		protected function getHeaderImage($table, array $record, array $configuration, $altText = '') {
			if (empty($configuration['foreign_field']) || empty($record[$configuration['foreign_field']])) {
				return '';
			}

			if (!$this->isLoaded) {
					// Load TypoScript settings
				$settings = Tx_SpGallery_Utility_TypoScript::getSetup('plugin.tx_spgallery.settings');
				$settings = Tx_SpGallery_Utility_TypoScript::parse($settings, FALSE);

					// Add stylesheet file
				if (!empty($settings['backend']['stylesheet'])) {
					$stylesheet = t3lib_div::getFileAbsFilename($settings['backend']['stylesheet']);
					$stylesheet = t3lib_div::resolveBackPath(str_replace(PATH_site, $this->backPath . '../', $stylesheet));
					$GLOBALS['SOBE']->doc->getPageRenderer()->addCssFile($stylesheet);
				}

				$this->isLoaded = TRUE;
			}

				// Get image filename
			$filename = $record[$configuration['foreign_field']];
			unset($configuration['foreign_field']);

				// Convert image
			$result = Tx_SpGallery_Utility_Image::convert(array($filename), $configuration, FALSE);
			$filename = reset($result);

			$filename = t3lib_div::resolveBackPath($this->backPath . '../' . $filename);
			return '<img src="' . htmlspecialchars($filename) . '" alt="' . htmlspecialchars($altText) . '" class="gallery-image" />';
		}

	}
?>