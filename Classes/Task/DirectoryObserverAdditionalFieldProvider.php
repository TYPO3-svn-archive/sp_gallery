<?php
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
	 * Additional field provider for the Directory Observer
	 */
	class Tx_SpGallery_Task_DirectoryObserverAdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {

		/**
		 * @var array
		 */
		protected $values = array();

		/**
		 * @var boolean
		 */
		protected $editMode = FALSE;

		/**
		 * @var array
		 */
		protected $structure = array();

		/**
		 * @var string
		 */
		protected $languageFile = 'EXT:sp_gallery/Resources/Private/Language/locallang.xml';

		/**
		 * @var string
		 */
		protected $labelPrefix = 'tx_spgallery_task_directoryobserver.';


		/**
		 * Add some input fields to configure the task
		 *
		 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
		 * @param object $task When editing, reference to the current task object. Null when adding.
		 * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
		 * @return array Array containing all the information pertaining to the additional fields
		 */
		public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {
			if (!empty($task)) {
				$this->values = get_object_vars($task);
			}
			if (!empty($parentObject->CMD)) {
				$this->editMode = ($parentObject->CMD === 'edit');
			}

			$this->addInputField('elementsPerRun', 3);
			$this->addInputField('clearCachePages', 0);
			$this->addInputField('storagePid', 0);
			$this->addCheckboxField('generateNames', FALSE);

			return $this->structure;
		}


		/**
		 * Checks the given values
		 *
		 * @param array $submittedData Reference to the array containing the data submitted by the user
		 * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
		 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
		 */
		public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
			if (empty($submittedData['elementsPerRun']) || !is_numeric($submittedData['elementsPerRun'])) {
				return FALSE;
			}
			return TRUE;
		}


		/**
		 * Saves given values into task object
		 *
		 * @param array $submittedData Contains data submitted by the user
		 * @param tx_scheduler_Task $task Reference to the current task object
		 * @return void
		 */
		public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
			if (!empty($task)) {
				$attributes = get_object_vars($task);
				foreach ($attributes as $key => $value) {
					if (isset($submittedData[$key])) {
						$task->$key = $submittedData[$key];
					}
				}
			}
		}


		/**
		 * Adds the structure of an input field
		 *
		 * @param string $fieldName Name of the field
		 * @param mixed $defaultValue Default value of the field
		 * @return void
		 */
		protected function addInputField($fieldName, $defaultValue = '') {
			if ($this->editMode && isset($this->values[$fieldName])) {
				$defaultValue = $this->values[$fieldName];
			}

			$this->structure[$fieldName] = array(
				'code'  => '<input type="text" name="tx_scheduler[' . $fieldName . ']" value="' . htmlspecialchars($defaultValue) . '" />',
				'label' => 'LLL:' . $this->languageFile . ':' . $this->labelPrefix . $fieldName,
			);
		}


		/**
		 * Adds the structure of a checkbox field
		 *
		 * @param string $fieldName Name of the field
		 * @param mixed $defaultValue Default value of the field
		 * @return void
		 */
		protected function addCheckboxField($fieldName, $defaultValue = FALSE) {
			if ($this->editMode && isset($this->values[$fieldName])) {
				$defaultValue = (bool) $this->values[$fieldName];
			}

			$this->structure[$fieldName] = array(
				'code'  => '<input type="checkbox" name="tx_scheduler[' . $fieldName . ']"' . ($defaultValue ? ' checked="checked"' : '') . ' />',
				'label' => 'LLL:' . $this->languageFile . ':' . $this->labelPrefix . $fieldName,
			);
		}

	}
?>