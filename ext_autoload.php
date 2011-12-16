<?php
	$extensionClassesPath = t3lib_extMgm::extPath('sp_gallery', 'Classes/');

	return array(
		'tx_spgallery_controller_gallerycontroller'                  => $extensionClassesPath . 'Controller/GalleryController.php',
		'tx_spgallery_domain_model_gallery'                          => $extensionClassesPath . 'Domain/Model/Gallery.php',
		'tx_spgallery_domain_model_image'                            => $extensionClassesPath . 'Domain/Model/Image.php',
		'tx_spgallery_domain_repository_galleryrepository'           => $extensionClassesPath . 'Domain/Repository/GalleryRepository.php',
		'tx_spgallery_domain_repository_imagerepository'             => $extensionClassesPath . 'Domain/Repository/ImageRepository.php',
		'tx_spgallery_hook_tca'                                      => $extensionClassesPath . 'Hook/Tca.php',
		'tx_spgallery_hook_uxt3libtceformsinline'                    => $extensionClassesPath . 'Hook/UxT3libTceformsInline.php',
		'tx_spgallery_object_objectbuilder '                         => $extensionClassesPath . 'Object/ObjectBuilder.php',
		'tx_spgallery_persistence_registry'                          => $extensionClassesPath . 'Persistence/Registry.php',
		'tx_spgallery_service_galleryimage'                          => $extensionClassesPath . 'Service/GalleryImage.php',
		'tx_spgallery_task_directoryobserver'                        => $extensionClassesPath . 'Task/DirectoryObserver.php',
		'tx_spgallery_task_directoryobserveradditionalfieldprovider' => $extensionClassesPath . 'Task/DirectoryObserverAdditionalFieldProvider.php',
		'tx_spgallery_utility_file'                                  => $extensionClassesPath . 'Utility/File.php',
		'tx_spgallery_utility_typoscript'                            => $extensionClassesPath . 'Utility/TypoScript.php',
		'tx_spgallery_viewHelpers_abstractGalleryViewHelper'         => $extensionClassesPath . 'ViewHelpers/AbstractGalleryViewHelper.php',
		'tx_spgallery_viewhelpers_galleryviewhelper'                 => $extensionClassesPath . 'ViewHelpers/GalleryViewHelper.php',
		'tx_spgallery_viewhelpers_locationviewhelper'                => $extensionClassesPath . 'ViewHelpers/LocationViewHelper.php',
		'tx_spgallery_viewhelpers_teaserimagesviewhelper'            => $extensionClassesPath . 'ViewHelpers/TeaserImagesViewHelper.php',
		'tx_spgallery_viewhelpers_rawviewhelper'                     => $extensionClassesPath . 'ViewHelpers/RawViewHelper.php',
	);
?>