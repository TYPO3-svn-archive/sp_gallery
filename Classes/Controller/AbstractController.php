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
	 * Abstract controller
	 */
	abstract class Tx_SpGallery_Controller_AbstractController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * @var array
		 */
		protected $plugin;


		/**
		 * Initialize the current action
		 *
		 * @return void
		 */
		protected function initializeAction() {
				// Pre-parse TypoScript setup
			$this->settings = Tx_SpGallery_Utility_TypoScript::parse($this->settings);

				// Get information about current plugin
			$contentObject = $this->configurationManager->getContentObject();
			$this->plugin = (!empty($contentObject->data) ? $contentObject->data : array());

				// Initialize concrete controller
			$this->initializeController();
		}


		/**
		 * Initialize concrete controller
		 *
		 * @return void
		 */
		protected function initializeController() {

		}


		/**
		 * Return the ID of configured page
		 *
		 * @param string $setting Name of the page in settings
		 * @return integer PID of the page
		 */
		protected function getPageId($setting) {
			if (!empty($setting) && !empty($this->settings[$setting])) {
				return (int) str_replace('pages_', '', $this->settings[$setting]);
			}
			return 0;
		}


		/**
		 * Clear cache of given pages
		 *
		 * @param string $pages List of page ids
		 * @return void
		 */
		protected function clearPageCache($pages) {
			if (!empty($pages)) {
				$pages = t3lib_div::intExplode(',', $pages, TRUE);
				Tx_Extbase_Utility_Cache::clearPageCache($pages);
			}
		}


		/**
		 * Translate a label
		 *
		 * @param string $label Label to translate
		 * @param array $arguments Optional arguments array
		 * @return string Translated label
		 */
		protected function translate($label, array $arguments = NULL) {
			$extensionKey = $this->request->getControllerExtensionKey();
			return Tx_Extbase_Utility_Localization::translate($label, $extensionKey, $arguments);
		}


		/**
		 * Add message to flash messages
		 *
		 * @param string $message Identifier of the message
		 * @param array $arguments Optional array of arguments
		 * @return void
		 */
		protected function addMessage($message, array $arguments = NULL) {
			$this->flashMessageContainer->add($this->translate($message, $arguments));
		}


		/**
		 * Send flash message and redirect to given action
		 *
		 * @param string $message Identifier of the message to send
		 * @param string $action Name of the action
		 * @param string $controller Optional name of the controller
		 * @param array $arguments Optional array of arguments
		 * @param integer $pageUid Optional UID of the page to redirect to
		 * @return void
		 */
		protected function redirectWithMessage($message, $action, $controller = NULL, array $arguments = NULL, $pageUid = NULL) {
			$this->addMessage($message);
			$this->redirect($action, $controller, NULL, $arguments, $pageUid);
		}


		/**
		 * Send flash message and forward to given action
		 *
		 * @param string $message Identifier of the message to send
		 * @param string $action Name of the action
		 * @param string $controller Optional name of the controller
		 * @param array $arguments Optional array of arguments
		 * @return void
		 */
		protected function forwardWithMessage($message, $action, $controller = NULL, array $arguments = NULL) {
			$this->addMessage($message);
			$this->forward($action, $controller, NULL, $arguments, $pageUid);
		}

	}
?>