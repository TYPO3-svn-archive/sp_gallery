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
	 * Controller for the Gallery object
	 */
	class Tx_SpGallery_Controller_GalleryController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * @var Tx_SpGallery_Domain_Repository_GalleryRepository
		 */
		protected $galleryRepository;

		/**
		 * @var array
		 */
		protected $plugin;

		/**
		 * @var array
		 */
		protected $ids;


		/**
		 * @param Tx_SpGallery_Domain_Repository_GalleryRepository $galleryRepository
		 * @return void
		 */
		public function injectGalleryRepository(Tx_SpGallery_Domain_Repository_GalleryRepository $galleryRepository) {
			$this->galleryRepository = $galleryRepository;
		}


		/**
		 * Initializes the current action
		 *
		 * @return void
		 */
		protected function initializeAction() {
				// Pre-parse TypoScript setup
			$this->settings = Tx_SpGallery_Utility_TypoScript::parse($this->settings);

				// Get information about current plugin
			$contentObject = $this->configurationManager->getContentObject();
			$this->plugin = (!empty($contentObject->data) ? $contentObject->data : array());

				// Get UIDs and PIDs of the configured gallaries
			if (empty($this->settings['pages'])) {
				$this->flashMessageContainer->add('Extension sp_gallery: No galleries defined to show');
			}
			$this->ids = $this->getIds($this->settings['pages']);
		}


		/**
		 * Displays a single gallery
		 *
		 * @param integer $gallery The gallery to display
		 * @return string The rendered view
		 */
		public function showAction($gallery = NULL) {
			if ($gallery === NULL) {
				if (empty($this->ids['uids'][0])) {
					$this->flashMessageContainer->add('Extension sp_gallery: No gallery defined to show');
				}
				$gallery = $this->ids['uids'][0];
			}

			if (!empty($gallery)) {
				$this->view->assign('gallery', $this->galleryRepository->findByUid($gallery));
			} else {
				$this->flashMessageContainer->add('Extension sp_gallery: No storagePid defined');
			}

			$this->view->assign('settings', $this->settings);
			$this->view->assign('plugin',   $this->plugin);
			$this->view->assign('listPage', $this->getPageId('listPage'));
		}


		/**
		 * Displays all galleries
		 *
		 * @return void
		 */
		public function listAction() {
			if (empty($this->ids['uids']) && empty($this->ids['pids'])) {
				$this->flashMessageContainer->add('Extension sp_gallery: No storagePid defined');
			}

			$uids = (!empty($this->ids['uids']) ? $this->ids['uids'] : array(0));
			$pids = (!empty($this->ids['pids']) ? $this->ids['pids'] : array(0));
			$galleries = $this->galleryRepository->findByUidsAndPids($uids, $pids);

			$this->view->assign('galleries',  $galleries);
			$this->view->assign('settings',   $this->settings);
			$this->view->assign('plugin',     $this->plugin);
			$this->view->assign('singlePage', $this->getPageId('singlePage'));
		}


		/**
		 * Displays some teaser images of a gallery
		 *
		 * @return void
		 */
		public function teaserAction() {
			$this->showAction();
			$this->view->assign('singlePage', $this->getPageId('singlePage'));
		}


		/**
		 * Displays some teaser images of a list of galleries
		 *
		 * @return void
		 */
		public function teaserListAction() {
			$this->listAction();
		}


		/**
		 * Returns an array of PIDs and UIDs
		 *
		 * @param string $pages List of pages
		 * @return array UIDs and PIDs
		 */
		protected function getIds($pages) {
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
				$ids[$key][] = (int)$id;
			}

			return $ids;
		}


		/**
		 * Returns the ID of configured page
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

	}
?>