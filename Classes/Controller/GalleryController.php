<?php
namespace Speedprogs\SpGallery\Controller;
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
	class GalleryController extends \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController {

		/**
		 * @var \Speedprogs\SpGallery\Domain\Repository\GalleryRepository
		 * @inject
		 */
		protected $galleryRepository;

		/**
		 * @var \Speedprogs\SpGallery\Domain\Repository\ImageRepository
		 * @inject
		 */
		protected $imageRepository;

		/**
		 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
		 * @inject
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
		 * Initialize the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
			// Get UIDs and PIDs of the configured galleries
			$action = $this->request->getControllerActionName();
			if ($action !== 'create' && $action !== 'update' && !empty($this->settings['pages'])) {
				$this->ids = \Speedprogs\SpGallery\Utility\Persistence::getIds($this->settings['pages']);
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
			print_r($this->settings);die("text");
			if ($gallery === NULL) {
				if (empty($this->ids['uids'][0])) {
					$this->addMessage('no_gallery_defined');
				}
				$gallery = $this->ids['uids'][0];
			}

			// Load gallery from persistance
			if (!$gallery instanceof \Speedprogs\SpGallery\Domain\Model\Gallery) {
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
			print_r($this->settings);die("text");
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
			print_r($this->settings);die("text");
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
		 * @param \Speedprogs\SpGallery\Domain\Model\Image $newImage New image object
		 * @param array $coordinates The image size and position
		 * @return void
		 * @dontvalidate $newImage
		 * @dontvalidate $coordinates
		 */
		public function newAction(\Speedprogs\SpGallery\Domain\Model\Image $newImage = NULL, $coordinates = NULL) {
			$galleries = $this->getGalleries();
			$this->view->assign('galleries', $galleries);
			$this->view->assign('enableSelect', count($galleries) > 1);
			$this->view->assign('newImage', $newImage);
			$this->view->assign('coordinates', $coordinates);
		}


		/**
		 * Create a new image and forwards to defined redirect page
		 *
		 * @param \Speedprogs\SpGallery\Domain\Model\Image $newImage New image object
		 * @param array $coordinates The image size and position
		 * @return void
		 * @dontvalidate $coordinates
		 */
		public function createAction(\Speedprogs\SpGallery\Domain\Model\Image $newImage, $coordinates = NULL) {
		// Get gallery for new images
			$gallery = $newImage->getGallery();
			if (empty($gallery)) {
				$this->forwardWithMessage('gallery_not_found', 'new');
			}

		// Get arguments from request
			$arguments = $this->request->getArguments();
			$fileName = $newImage->getFileName();
			$fileInfo = \Speedprogs\SpGallery\Utility\File::getFileInfo('tx_spgallery_gallery.uploadFile');

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
		 * @param \Speedprogs\SpGallery\Domain\Model\Image $image The image object
		 * @return void
		 * @dontvalidate $image
		 */
		public function editAction(\Speedprogs\SpGallery\Domain\Model\Image $image = NULL) {
			$this->redirect('new');
		}


		/**
		 * Update given image
		 *
		 * @param \Speedprogs\SpGallery\Domain\Model\Image $image The image object
		 * @return void
		 * @dontvalidate $image
		 */
		public function updateAction(\Speedprogs\SpGallery\Domain\Model\Image $image = NULL) {
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
			$ordering = \Speedprogs\SpGallery\Utility\Persistence::getOrdering($this->settings['galleries']);

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
				$configuration = \Speedprogs\SpGallery\Utility\Backend::getExtensionConfiguration($extensionKey);
				if ($configuration['gallerySortingType'] === 'plugin') {
					$galleries = \Speedprogs\SpGallery\Utility\Persistence::sortBySelector($galleries, $uids, $direction);
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
			$fileType = \Speedprogs\SpGallery\Utility\File::getFileType($fileInfo['name']);
			if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inList($types, $fileType)) {
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
		 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
		 * @param \Speedprogs\SpGallery\Domain\Model\Image $image The  image
		 * @param array $fileInfo Information about upload file
		 * @return string Temp filename
		 */
		protected function uploadImage(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery, \Speedprogs\SpGallery\Domain\Model\Image $image, array $fileInfo) {
			// Get temporary directory
			$directory = \Speedprogs\SpGallery\Utility\File::getRelativeDirectory($this->tempDirectory);

			// Move uploaded image to gallery directory
			$fileName = \Speedprogs\SpGallery\Utility\File::moveUploadedFile($fileInfo['tmp_name'], $fileInfo['name'], $directory);
			if (empty($fileName)) {
				$this->forwardWithMessage('file_invalid', 'new');
			}

			// Override storagePid
			$setup = $this->configurationManager->getConfiguration(
				\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
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
		 * @param \Speedprogs\SpGallery\Domain\Model\Image $newImage The image
		 * @param array $coordinates The image size and position
		 * @return string New filename
		 */
		protected function cropImage(\Speedprogs\SpGallery\Domain\Model\Image $image, $coordinates) {
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
				$fileName = \Speedprogs\SpGallery\Utility\Image::crop($fileName, $x, $y, $w, $h);
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
		 * @param \Speedprogs\SpGallery\Domain\Model\Gallery $gallery The gallery
		 * @param \Speedprogs\SpGallery\Domain\Model\Image $image The image
		 * @param string $tempName Path to temporary file
		 * @return void
		 */
		protected function addImageToGallery(\Speedprogs\SpGallery\Domain\Model\Gallery $gallery, \Speedprogs\SpGallery\Domain\Model\Image $image, $tempName) {
			if (empty($tempName)) {
				$this->forwardWithMessage('file_empty', 'new');
			}

			// Get gallery directory
			$directory = $gallery->getImageDirectory();
			if (empty($directory)) {
				$this->forwardWithMessage('directory_invalid', 'new');
			}
			$directory = \Speedprogs\SpGallery\Utility\File::getRelativeDirectory($directory);
			$newFileName = $directory . basename($tempName);
			if (!\Speedprogs\SpGallery\Utility\File::moveFile($tempName, $newFileName)) {
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
			\Speedprogs\SpGallery\Utility\Image::generate(array($newFileName), $this->settings);
		}

	}
?>