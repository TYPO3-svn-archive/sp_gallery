<?php
	$extensionClassesPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sp_gallery', 'Classes/');

	return array(
		'tx_spgallery_controller_abstractcontroller'                 => $extensionClassesPath . 'Controller/AbstractController.php',
		'tx_spgallery_controller_gallerycontroller'                  => $extensionClassesPath . 'Controller/GalleryController.php',
		'tx_spgallery_domain_model_gallery'                          => $extensionClassesPath . 'Domain/Model/Gallery.php',
		'tx_spgallery_domain_model_image'                            => $extensionClassesPath . 'Domain/Model/Image.php',
		'tx_spgallery_domain_repository_abstractrepository'          => $extensionClassesPath . 'Domain/Repository/AbstractRepository.php',
		'tx_spgallery_domain_repository_galleryrepository'           => $extensionClassesPath . 'Domain/Repository/GalleryRepository.php',
		'tx_spgallery_domain_repository_imagerepository'             => $extensionClassesPath . 'Domain/Repository/ImageRepository.php',
		'tx_spgallery_hook_tca'                                      => $extensionClassesPath . 'Hook/Tca.php',
		'tx_spgallery_hook_tcemain'                                  => $extensionClassesPath . 'Hook/TceMain.php',
		'tx_spgallery_hook_uxt3libtceformsinline'                    => $extensionClassesPath . 'Hook/UxT3libTceformsInline.php',
		'tx_spgallery_object_objectbuilder'                          => $extensionClassesPath . 'Object/ObjectBuilder.php',
		'tx_spgallery_persistence_registry'                          => $extensionClassesPath . 'Persistence/Registry.php',
		'tx_spgallery_service_galleryservice'                        => $extensionClassesPath . 'Service/GalleryService.php',
		'tx_spgallery_task_directoryobserver'                        => $extensionClassesPath . 'Task/DirectoryObserver.php',
		'tx_spgallery_task_directoryobserveradditionalfieldprovider' => $extensionClassesPath . 'Task/DirectoryObserverAdditionalFieldProvider.php',
		'tx_spgallery_utility_backend'                               => $extensionClassesPath . 'Utility/Backend.php',
		'tx_spgallery_utility_file'                                  => $extensionClassesPath . 'Utility/File.php',
		'tx_spgallery_utility_image'                                 => $extensionClassesPath . 'Utility/Image.php',
		'tx_spgallery_utility_persistence'                           => $extensionClassesPath . 'Utility/Persistence.php',
		'tx_spgallery_utility_template'                              => $extensionClassesPath . 'Utility/Template.php',
		'tx_spgallery_utility_typoscript'                            => $extensionClassesPath . 'Utility/TypoScript.php',
		'tx_spgallery_viewHelpers_abstractgalleryviewhelper'         => $extensionClassesPath . 'ViewHelpers/AbstractGalleryViewHelper.php',
		'tx_spgallery_viewHelpers_abstracttemplatebasedviewhelper'   => $extensionClassesPath . 'ViewHelpers/AbstractTemplateBasedViewHelper.php',
		'tx_spgallery_viewHelpers_basenameviewhelper'                => $extensionClassesPath . 'ViewHelpers/BasenameViewHelper.php',
		'tx_spgallery_viewHelpers_caseviewhelper'                    => $extensionClassesPath . 'ViewHelpers/CaseViewHelper.php',
		'tx_spgallery_viewHelpers_cropimageviewhelper'               => $extensionClassesPath . 'ViewHelpers/CropImageViewHelper.php',
		'tx_spgallery_viewhelpers_filesizeviewhelper'                => $extensionClassesPath . 'ViewHelpers/FileSizeViewHelper.php',
		'tx_spgallery_viewhelpers_flashmessagesviewhelper'           => $extensionClassesPath . 'ViewHelpers/FlashMessagesViewHelper.php',
		'tx_spgallery_viewhelpers_galleryviewhelper'                 => $extensionClassesPath . 'ViewHelpers/GalleryViewHelper.php',
		'tx_spgallery_viewhelpers_locationviewhelper'                => $extensionClassesPath . 'ViewHelpers/LocationViewHelper.php',
		'tx_spgallery_viewhelpers_rawviewhelper'                     => $extensionClassesPath . 'ViewHelpers/RawViewHelper.php',
		'tx_spgallery_viewhelpers_teaserimagesviewhelper'            => $extensionClassesPath . 'ViewHelpers/TeaserImagesViewHelper.php',
	);
?>