<?php
	$extensionClassesPath = t3lib_extMgm::extPath('sp_gallery', 'Classes/');

	return array(
		'tx_spgallery_controller_gallerycontroller'          => $extensionClassesPath . 'Controller/GalleryController.php',
		'tx_spgallery_domain_model_gallery'                  => $extensionClassesPath . 'Domain/Model/Gallery.php',
		'tx_spgallery_domain_repository_galleryrepository'   => $extensionClassesPath . 'Domain/Repository/GalleryRepository.php',
		'tx_spgallery_utility_file'                          => $extensionClassesPath . 'Utility/File.php',
		'tx_spgallery_utility_typoscript'                    => $extensionClassesPath . 'Utility/TypoScript.php',
		'tx_spgallery_service_galleryimage'                  => $extensionClassesPath . 'Service/GalleryImage.php',
		'tx_spgallery_task_directorylistener'                => $extensionClassesPath . 'Task/DirectoryListener.php',
		'tx_spgallery_viewHelpers_abstractGalleryViewHelper' => $extensionClassesPath . 'ViewHelpers/AbstractGalleryViewHelper.php',
		'tx_spgallery_viewhelpers_galleryviewhelper'         => $extensionClassesPath . 'ViewHelpers/GalleryViewHelper.php',
	);
?>