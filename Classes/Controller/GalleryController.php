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
	class Tx_SpGallery_Controller_GalleryController extends Tx_SpGallery_Controller_AbstractController {

		/**
		 * @var Tx_SpGallery_Domain_Repository_GalleryRepository
		 */
		protected $galleryRepository;

		/**
		 * @var Tx_SpGallery_Domain_Repository_ImageRepository
		 */
		protected $imageRepository;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;

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
		 * Initialize the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
				// Get UIDs and PIDs of the configured galleries
			$action = $this->request->getControllerActionName();
			$showActions = array('show', 'list', 'teaser', 'teaserList');
			if (in_array($action, $showActions)) {
				if (empty($this->settings['pages'])) {
					$this->addMessage('no_galleries_defined');
				}
				$this->ids = Tx_SpGallery_Utility_Persistence::getIds($this->settings['pages']);
			}
		}


		/**
		 * Display a single gallery
		 *
		 * @param integer $gallery The gallery to display
		 * @param integer $image UID of the image to show in gallery
		 * @return string The rendered view
		 */
		public function showAction($gallery = NULL, $image = NULL) {
			if ($gallery === NULL) {
				if (empty($this->ids['uids'][0])) {
					$this->addMessage('no_gallery_defined');
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
		 * Display all galleries
		 *
		 * @return void
		 */
		public function listAction() {
			if (empty($this->ids['uids']) && empty($this->ids['pids'])) {
				$this->addMessage('no_galleries_defined');
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
		 * Display some teaser images of a gallery
		 *
		 * @return void
		 */
		public function teaserAction() {
			$this->showAction();
			$this->view->assign('singlePage', $this->getPageId('singlePage'));
		}


		/**
		 * Display some teaser images of a list of galleries
		 *
		 * @return void
		 */
		public function teaserListAction() {
			$this->listAction();
		}


		/**
		 * Display a form to create a new image
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $newImage New image object
		 * @return void
		 * @dontvalidate $newImage
		 */
		public function newAction(Tx_SpGallery_Domain_Model_Image $newImage = NULL) {
			$this->view->assign('newImage', $newImage);
		}


		/**
		 * Create a new image and forwards to defined redirect page
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $newImage New image object
		 * @return void
		 */
		public function createAction(Tx_SpGallery_Domain_Model_Image $newImage) {
			if (empty($this->settings['galleryUid'])) {
				$this->forwardWithMessage('no_gallery_defined', 'new');
			}

				// Get gallery for new images
			$gallery = $this->galleryRepository->findByUid((int) $this->settings['galleryUid']);
			if (empty($gallery)) {
				$this->forwardWithMessage('gallery_not_found', 'new');
			}

				// Override storagePid
			$configuration = $this->configurationManager->getConfiguration(
				Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK
			);
			$configuration['persistence']['storagePid'] = (int) $gallery->getPid();
			$this->configurationManager->setConfiguration($configuration);

				// Create image and update directory hash
			$this->uploadImage($gallery, $newImage);
			$this->imageRepository->add($newImage);
			$gallery->generateDirectoryHash();

				// Persist now to get the UID
			$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
			$persistenceManager->persistAll();

				// Redirect to edit action for preview and cropping
			$this->redirect('edit', NULL, NULL, array('image' => $newImage));
		}


		/**
		 * Upload an image to given gallery
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery to create image
		 * @param Tx_SpGallery_Domain_Model_Image $newImage The new image
		 * @return void
		 */
		protected function uploadImage(Tx_SpGallery_Domain_Model_Gallery $gallery, Tx_SpGallery_Domain_Model_Image $newImage) {
				// Get upload directory
			$directory = $gallery->getImageDirectory();
			if (empty($directory)) {
				$this->forwardWithMessage('directory_invalid', 'new');
			}
			$directory = Tx_SpGallery_Utility_File::getRelativeDirectory($directory);

				// Move uploaded image to gallery directory
			$fileName = Tx_SpGallery_Utility_File::moveUploadedFile(
				$_FILES['tx_spgallery_gallery']['tmp_name']['newImage']['fileName'],
				$_FILES['tx_spgallery_gallery']['name']['newImage']['fileName'],
				$directory
			);
			if (empty($fileName)) {
				$this->forwardWithMessage('file_invalid', 'new');
			}

				// Complete image object
			$newImage->setGallery($gallery);
			$newImage->setFileName($directory . $fileName);
			$newImage->generateImageInformation();
			if (!empty($this->settings['generateName'])) {
				$newImage->generateImageName();
			}
		}


		/**
		 * Display a form to crop an image
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $image The image object
		 * @param array $coordinates The image size and position
		 * @return void
		 * @dontvalidate $image
		 * @dontvalidate $coordinates
		 */
		public function editAction(Tx_SpGallery_Domain_Model_Image $image = NULL, array $coordinates = NULL) {
			$this->view->assign('settings', $this->settings);
			$this->view->assign('plugin',   $this->plugin);
			$this->view->assign('image',    $image);
		}


		/**
		 * Crop given image image
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $image The image object
		 * @param array $coordinates The image size and position
		 * @return void
		 */
		public function updateAction(Tx_SpGallery_Domain_Model_Image $image = NULL, array $coordinates = NULL) {
			print_r($coordinates);die();





				// Clear page cache
			if (!empty($this->settings['clearCachePages'])) {
				$this->clearPageCache($this->settings['clearCachePages']);
			}

				// Redirect
			if (!empty($this->settings['redirectPage'])) {
				$this->redirectToUri((int) $this->settings['redirectPage']);
			} else {
				$this->redirect('new');
			}
		}

	}
?>