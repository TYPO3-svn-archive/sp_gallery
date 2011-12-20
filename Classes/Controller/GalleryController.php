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
		 * @var Tx_SpGallery_Domain_Repository_ImageRepository
		 */
		protected $imageRepository;

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
		 * @param Tx_SpGallery_Domain_Repository_GalleryRepository $galleryRepository
		 * @return void
		 */
		public function injectImageRepository(Tx_SpGallery_Domain_Repository_ImageRepository $imageRepository) {
			$this->imageRepository = $imageRepository;
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

				// Override storagePid
			if (!empty($this->settings['newRecordPid'])) {
				$configuration = $this->configurationManager->getConfiguration(
					Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK
				);
				$configuration['persistence']['storagePid'] = (int) $this->settings['newRecordPid'];
				$this->configurationManager->setConfiguration($configuration);
			}

				// Get UIDs and PIDs of the configured gallaries
			if (empty($this->settings['pages'])) {
				$this->flashMessageContainer->add('No galleries defined to show');
			}
			$this->ids = Tx_SpGallery_Utility_Persistence::getIds($this->settings['pages']);
		}


		/**
		 * Displays a single gallery
		 *
		 * @param integer $gallery The gallery to display
		 * @param integer $image UID of the image to show in gallery
		 * @return string The rendered view
		 */
		public function showAction($gallery = NULL, $image = NULL) {
			if ($gallery === NULL) {
				if (empty($this->ids['uids'][0])) {
					$this->flashMessageContainer->add('No gallery defined to show');
				}
				$gallery = $this->ids['uids'][0];
			}

				// Load gallery from persistance
			if (!$gallery instanceof Tx_SpGallery_Domain_Model_Gallery) {
				$gallery = $this->galleryRepository->findByUid((int) $gallery);
			}

				// Set template variables
			$this->view->assign('gallery',  $gallery);
			$this->view->assign('image',    $image);
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
				$this->flashMessageContainer->add('No galleries defined to show');
			}

				// Load galleries from persistance
			$uids      = (!empty($this->ids['uids']) ? $this->ids['uids'] : array(0));
			$pids      = (!empty($this->ids['pids']) ? $this->ids['pids'] : array(0));
			$offset    = (isset($this->settings['galleries']['offset']) ? (int) $this->settings['galleries']['offset'] : 0);
			$limit     = (isset($this->settings['galleries']['limit'])  ? (int) $this->settings['galleries']['limit']  : 10);
			$ordering  = Tx_SpGallery_Utility_Persistence::getOrdering($this->settings['galleries']);
			$galleries = $this->galleryRepository->findByUidsAndPids($uids, $pids, $offset, $limit, $ordering);

				// Order galleries according to manual sorting type
			$extensionKey = $this->request->getControllerExtensionKey();
			$direction = (!empty($this->settings['galleries']['orderDirection']) ? $this->settings['galleries']['orderDirection'] : 'asc');
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extensionKey])) {
				$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extensionKey]);
				if ($configuration['gallerySortingType'] === 'plugin') {
					$galleries = Tx_SpGallery_Utility_Persistence::sortBySelector($galleries, $uids, $direction);
				}
			}

				// Set template variables
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
		 * Displays a form to create a new image
		 *
		 * @param Tx_TorrGallery_Domain_Model_Image $newImage Image object which has not yet been added to the repository
		 * @return void
		 * @dontvalidate $newImage
		 */
		public function newAction(Tx_SpGallery_Domain_Model_Image $newImage = NULL) {
			$storagePidConfigured = Tx_SpGallery_Utility_Persistence::hasStoragePage('sp_gallery', 'image');
			if ($newImage === NULL && !$storagePidConfigured) {
				throw new Exception('Please configure "plugin.tx_spgallery.persistence.storagePid" in TypoScript setup');
			}
			$this->view->assign('newImage', $newImage);
		}


		/**
		 * Creates a new image and forwards to the list action
		 *
		 * @param Tx_TorrGallery_Domain_Model_Image $newImage Image object which has not yet been added to the repository
		 * @param Tx_TorrGallery_Domain_Model_Author $newAuthor Author object which has not yet been added to the repository
		 * @return void
		 */
		public function createAction(Tx_TorrGallery_Domain_Model_Image $newImage, Tx_TorrGallery_Domain_Model_Author $newAuthor) {

				// Complete image object
			$newImage->setImage($filename);

				// Add new objects
			$this->imageRepository->add($newImage);

				// Clear list page cache
			$listPage = (!empty($this->settings['listPage']) ? $this->settings['listPage'] : $GLOBALS['TSFE']->id);
			$this->clearPageCache($listPage);

			$this->redirect('list');
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