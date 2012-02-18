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
		 * @var string
		 */
		protected $tempDirectory = 'typo3temp/pics/';


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
		 * @param Tx_Extbase_Persistence_Manager $persistenceManager
		 * @return void
		 */
		public function injectPersistenceManager(Tx_Extbase_Persistence_Manager $persistenceManager) {
			$this->persistenceManager = $persistenceManager;
		}


		/**
		 * Initialize the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
				// Get UIDs and PIDs of the configured galleries
			$action = $this->request->getControllerActionName();
			if ($action !== 'create' && $action !== 'update' && !empty($this->settings['pages'])) {
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
			$this->view->assign('galleries',  $this->getGalleries());
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
		 * @param array $coordinates The image size and position
		 * @return void
		 * @dontvalidate $newImage
		 * @dontvalidate $coordinates
		 */
		public function newAction(Tx_SpGallery_Domain_Model_Image $newImage = NULL, $coordinates = NULL) {
			$galleries = $this->getGalleries();
			$this->view->assign('galleries', $galleries);
			$this->view->assign('enableSelect', count($galleries) > 1);
			$this->view->assign('newImage', $newImage);
			$this->view->assign('coordinates', $coordinates);
		}


		/**
		 * Create a new image and forwards to defined redirect page
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $newImage New image object
		 * @param array $coordinates The image size and position
		 * @return void
		 * @dontvalidate $coordinates
		 */
		public function createAction(Tx_SpGallery_Domain_Model_Image $newImage, $coordinates = NULL) {
				// Get gallery for new images
			$gallery = $newImage->getGallery();
			if (empty($gallery)) {
				$this->forwardWithMessage('gallery_not_found', 'new');
			}

				// Get arguments from request
			$arguments = $this->request->getArguments();
			$fileName = $newImage->getFileName();
			$fileInfo = Tx_SpGallery_Utility_File::getFileInfo('tx_spgallery_gallery.uploadFile');

				// Upload image to temp directory and go back to preview
			if (!empty($fileInfo['tmp_name']) && $this->isValidImage($fileInfo)) {
				$fileName = $this->uploadImage($gallery, $newImage, $fileInfo);
				$arguments['newImage']['fileName'] = $fileName;
				$arguments['newImage']['imageWidth'] = $newImage->getImageWidth();
				$arguments['newImage']['imageHeight'] = $newImage->getImageHeight();
				$this->request->setArguments($arguments);
				$this->forward('new');
			}

				// Crop image and go back to preview
			if (!empty($coordinates['top']) || !empty($coordinates['left']) || !empty($coordinates['width']) || !empty($coordinates['height'])) {
				$fileName = $this->cropImage($newImage, $coordinates);
				$arguments['newImage']['fileName'] = $fileName;
				$arguments['newImage']['imageWidth'] = $newImage->getImageWidth();
				$arguments['newImage']['imageHeight'] = $newImage->getImageHeight();
				$arguments['coordinates'] = array();
				$this->request->setArguments($arguments);
				$this->forward('new');
			}

				// Add image to gallery
			$this->addImageToGallery($gallery, $newImage, $fileName);

				// Clear page cache
			if (!empty($this->settings['clearCachePages'])) {
				$this->clearPageCache($this->settings['clearCachePages']);
			}

				// Redirect
			if (!empty($this->settings['redirectPage'])) {
				$this->clearPageCache($this->settings['redirectPage']);
				$this->persistenceManager->persistAll();
				$parameters = array();
				if (!empty($this->settings['redirectWithParameters'])) {
					$parameters['gallery'] = $gallery;
					if (empty($this->settings['uploadReview'])) {
						$parameters['image'] = $newImage;
					}
				}
				$this->redirectToPage($this->settings['redirectPage'], $parameters);
			} else {
				$this->redirectWithMessage('image_created', 'new', NULL, NULL, NULL, 'ok');
			}
		}


		/**
		 * Display a form to update an image
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $image The image object
		 * @return void
		 * @dontvalidate $image
		 */
		public function editAction(Tx_SpGallery_Domain_Model_Image $image = NULL) {
			$this->redirect('new');
		}


		/**
		 * Update given image
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $image The image object
		 * @return void
		 * @dontvalidate $image
		 */
		public function updateAction(Tx_SpGallery_Domain_Model_Image $image = NULL) {
			$this->redirect('new');
		}


		/**
		 * Load galleries from persistance
		 *
		 * @return array Galleries
		 */
		protected function getGalleries() {
			$galleries = array();

				// Get configuration
			$uids     = (!empty($this->ids['uids']) ? $this->ids['uids'] : array(0));
			$pids     = (!empty($this->ids['pids']) ? $this->ids['pids'] : array(0));
			$offset   = (isset($this->settings['galleries']['offset']) ? (int) $this->settings['galleries']['offset'] : 0);
			$limit    = (isset($this->settings['galleries']['limit'])  ? (int) $this->settings['galleries']['limit']  : 10);
			$ordering = Tx_SpGallery_Utility_Persistence::getOrdering($this->settings['galleries']);

				// Find galleries according to configuration
			if (!empty($uids[0]) || !empty($pids[0])) {
				$galleries = $this->galleryRepository->findByUidsAndPids($uids, $pids, $offset, $limit, $ordering);
			} else if (!empty($this->settings['allGalleriesWhenEmpty'])) {
				$galleries = $this->galleryRepository->findAll($offset, $limit, $ordering);
			}

				// Order galleries according to manual sorting type
			if (!empty($galleries) && key($ordering) === 'sorting') {
				$extensionKey = $this->request->getControllerExtensionKey();
				$direction = (!empty($this->settings['galleries']['orderDirection']) ? $this->settings['galleries']['orderDirection'] : 'asc');
				$configuration = Tx_SpGallery_Utility_Backend::getExtensionConfiguration($extensionKey);
				if ($configuration['gallerySortingType'] === 'plugin') {
					$galleries = Tx_SpGallery_Utility_Persistence::sortBySelector($galleries, $uids, $direction);
				}
			}

			return $galleries;
		}


		/**
		 * Validate image file
		 *
		 * @param array $fileInfo Information about upload file
		 * @return boolean TRUE if the image is valid
		 */
		protected function isValidImage(array $fileInfo) {
				// Check if a file was uploaded
			if (empty($fileInfo) || empty($fileInfo['tmp_name']) || $fileInfo['error'] != UPLOAD_ERR_OK) {
				$this->forwardWithMessage('file_empty', 'new');
			}

				// Check type
			$types = trim(strtolower($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']));
			$fileType = Tx_SpGallery_Utility_File::getFileType($fileInfo['name']);
			if (!t3lib_div::inList($types, $fileType)) {
				$this->forwardWithMessage('file_type_invalid', 'new');
			}

				// Check size
			if (!empty($this->settings['uploadFileSize'])) {
				$size = (int) $this->settings['uploadFileSize'];
				if (empty($fileInfo['size']) || (int) $fileInfo['size'] > $size) {
					$this->forwardWithMessage('file_size_invalid', 'new');
				}
			}

			return TRUE;
		}


		/**
		 * Upload an image to given gallery
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * @param Tx_SpGallery_Domain_Model_Image $image The  image
		 * @param array $fileInfo Information about upload file
		 * @return string Temp filename
		 */
		protected function uploadImage(Tx_SpGallery_Domain_Model_Gallery $gallery, Tx_SpGallery_Domain_Model_Image $image, array $fileInfo) {
				// Get temporary directory
			$directory = Tx_SpGallery_Utility_File::getRelativeDirectory($this->tempDirectory);

				// Move uploaded image to gallery directory
			$fileName = Tx_SpGallery_Utility_File::moveUploadedFile($fileInfo['tmp_name'], $fileInfo['name'], $directory);
			if (empty($fileName)) {
				$this->forwardWithMessage('file_invalid', 'new');
			}

				// Override storagePid
			$setup = $this->configurationManager->getConfiguration(
				Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK
			);
			$setup['persistence']['storagePid'] = (int) $gallery->getPid();
			$this->configurationManager->setConfiguration($setup);

				// Complete image object
			$image->setGallery($gallery);
			$image->setFileName($directory . $fileName);
			$image->generateImageInformation();
			if (!empty($this->settings['generateName'])) {
				$image->generateImageName();
			}

			return $directory . $fileName;
		}


		/**
		 * Crop an image
		 *
		 * @param Tx_SpGallery_Domain_Model_Image $newImage The image
		 * @param array $coordinates The image size and position
		 * @return string New filename
		 */
		protected function cropImage(Tx_SpGallery_Domain_Model_Image $image, $coordinates) {
			if (empty($coordinates)) {
				return '';
			}

				// Build crop settings
			$factorX = (double) $coordinates['factorX'];
			$factorY = (double) $coordinates['factorY'];
			$y = round((int) $coordinates['top'] * $factorY);
			$x = round((int) $coordinates['left'] * $factorX);
			$w = round((int) $coordinates['width'] * $factorY);
			$h = round((int) $coordinates['height'] * $factorX);

				// Crop image
			$fileName = $image->getFileName();
			if (!empty($x) || !empty($y) || !empty($w) || !empty($h)) {
				$fileName = Tx_SpGallery_Utility_Image::crop($fileName, $x, $y, $w, $h);
				if (!empty($fileName)) {
					$image->setFileName($fileName);
					$image->generateImageInformation();
					if (!empty($this->settings['generateName'])) {
						$image->generateImageName();
					}
				}
			}

			return $fileName;
		}


		/**
		 * Add an image to given gallery
		 *
		 * @param Tx_SpGallery_Domain_Model_Gallery $gallery The gallery
		 * @param Tx_SpGallery_Domain_Model_Image $image The image
		 * @param string $tempName Path to temporary file
		 * @return void
		 */
		protected function addImageToGallery(Tx_SpGallery_Domain_Model_Gallery $gallery, Tx_SpGallery_Domain_Model_Image $image, $tempName) {
			if (empty($tempName)) {
				$this->forwardWithMessage('file_empty', 'new');
			}

				// Get gallery directory
			$directory = $gallery->getImageDirectory();
			if (empty($directory)) {
				$this->forwardWithMessage('directory_invalid', 'new');
			}
			$directory = Tx_SpGallery_Utility_File::getRelativeDirectory($directory);
			$newFileName = $directory . basename($tempName);
			if (!Tx_SpGallery_Utility_File::moveFile($tempName, $newFileName)) {
				$this->forwardWithMessage('move_failed', 'new');
			}

				// Complete image and gallery
			$image->setGallery($gallery);
			$image->setFileName($newFileName);
			$image->generateImageInformation();
			if (!empty($this->settings['generateName'])) {
				$image->generateImageName();
			}
			$this->imageRepository->add($image);
			$gallery->generateDirectoryHash();

				// Hide image in frontend and use admin review
			if (!empty($this->settings['uploadReview'])) {
				$image->setHidden(TRUE);
			}

				// Generate image files
			Tx_SpGallery_Utility_Image::generate(array($newFileName), $this->settings);
		}

	}
?>