<?php

########################################################################
# Extension Manager/Repository config file for ext "sp_gallery".
#
# Auto generated 18-02-2012 23:01
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Image gallery',
	'description' => 'An image gallery based on Extbase, Fluid, jQuery, the Galleria gallery and the Jcrop plugin. It provides a teaser view, lightbox, fullscreen view, touch-screen support, a file system observer and a frontend upload form with cropping functionality.',
	'category' => 'plugin',
	'author' => 'Kai Vogel',
	'author_email' => 'kai.vogel@speedprogs.de',
	'author_company' => 'Speedprogs.de',
	'shy' => '',
	'dependencies' => 'cms,extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '3.0.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'scheduler' => '',
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:81:{s:16:"ext_autoload.php";s:4:"9149";s:21:"ext_conf_template.txt";s:4:"c01d";s:12:"ext_icon.gif";s:4:"3ac5";s:13:"ext_icons.php";s:4:"a9f8";s:17:"ext_localconf.php";s:4:"ac5e";s:14:"ext_tables.php";s:4:"0a2c";s:14:"ext_tables.sql";s:4:"350c";s:41:"Classes/Controller/AbstractController.php";s:4:"5b72";s:40:"Classes/Controller/GalleryController.php";s:4:"43e1";s:32:"Classes/Domain/Model/Gallery.php";s:4:"6141";s:30:"Classes/Domain/Model/Image.php";s:4:"de41";s:48:"Classes/Domain/Repository/AbstractRepository.php";s:4:"d879";s:47:"Classes/Domain/Repository/GalleryRepository.php";s:4:"dcd4";s:45:"Classes/Domain/Repository/ImageRepository.php";s:4:"6d28";s:20:"Classes/Hook/Tca.php";s:4:"24d5";s:24:"Classes/Hook/TceMain.php";s:4:"492e";s:38:"Classes/Hook/UxT3libTceformsInline.php";s:4:"ac18";s:32:"Classes/Object/ObjectBuilder.php";s:4:"c73c";s:32:"Classes/Persistence/Registry.php";s:4:"fd74";s:34:"Classes/Service/GalleryService.php";s:4:"0c38";s:34:"Classes/Task/DirectoryObserver.php";s:4:"06aa";s:57:"Classes/Task/DirectoryObserverAdditionalFieldProvider.php";s:4:"c6ef";s:27:"Classes/Utility/Backend.php";s:4:"96d1";s:24:"Classes/Utility/File.php";s:4:"f864";s:25:"Classes/Utility/Image.php";s:4:"ea9b";s:31:"Classes/Utility/Persistence.php";s:4:"afe5";s:28:"Classes/Utility/Template.php";s:4:"5ce4";s:30:"Classes/Utility/TypoScript.php";s:4:"8d61";s:49:"Classes/ViewHelpers/AbstractGalleryViewHelper.php";s:4:"e151";s:55:"Classes/ViewHelpers/AbstractTemplateBasedViewHelper.php";s:4:"1ca8";s:42:"Classes/ViewHelpers/BasenameViewHelper.php";s:4:"861b";s:38:"Classes/ViewHelpers/CaseViewHelper.php";s:4:"1cdc";s:43:"Classes/ViewHelpers/CropImageViewHelper.php";s:4:"f65c";s:42:"Classes/ViewHelpers/FileSizeViewHelper.php";s:4:"4236";s:47:"Classes/ViewHelpers/FlashMessagesViewHelper.php";s:4:"9200";s:41:"Classes/ViewHelpers/GalleryViewHelper.php";s:4:"51c0";s:42:"Classes/ViewHelpers/LocationViewHelper.php";s:4:"dfde";s:37:"Classes/ViewHelpers/RawViewHelper.php";s:4:"bff2";s:46:"Classes/ViewHelpers/TeaserImagesViewHelper.php";s:4:"d0d8";s:35:"Configuration/FlexForms/Gallery.xml";s:4:"f8a3";s:29:"Configuration/TCA/Gallery.php";s:4:"3dd3";s:27:"Configuration/TCA/Image.php";s:4:"6b2d";s:38:"Configuration/TypoScript/constants.txt";s:4:"d216";s:34:"Configuration/TypoScript/setup.txt";s:4:"92b5";s:40:"Resources/Private/Language/locallang.xml";s:4:"1908";s:52:"Resources/Private/Language/locallang_csh_gallery.xml";s:4:"325e";s:50:"Resources/Private/Language/locallang_csh_image.xml";s:4:"f699";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"e1ef";s:38:"Resources/Private/Layouts/Default.html";s:4:"6d77";s:42:"Resources/Private/Partials/FormErrors.html";s:4:"f5bc";s:43:"Resources/Private/Partials/TeaserImage.html";s:4:"2a87";s:45:"Resources/Private/Templates/Gallery/Edit.html";s:4:"4280";s:45:"Resources/Private/Templates/Gallery/List.html";s:4:"6e03";s:44:"Resources/Private/Templates/Gallery/New.html";s:4:"498e";s:45:"Resources/Private/Templates/Gallery/Show.html";s:4:"5f30";s:47:"Resources/Private/Templates/Gallery/Teaser.html";s:4:"51f6";s:51:"Resources/Private/Templates/Gallery/TeaserList.html";s:4:"2f05";s:46:"Resources/Private/Templates/Javascript/Crop.js";s:4:"b824";s:49:"Resources/Private/Templates/Javascript/Gallery.js";s:4:"204b";s:36:"Resources/Public/Images/Download.gif";s:4:"fa20";s:33:"Resources/Public/Images/Empty.png";s:4:"a83f";s:35:"Resources/Public/Images/Gallery.gif";s:4:"3ac5";s:34:"Resources/Public/Images/Hidden.png";s:4:"6846";s:33:"Resources/Public/Images/Image.gif";s:4:"d148";s:39:"Resources/Public/Images/Information.gif";s:4:"8c52";s:33:"Resources/Public/Images/Jcrop.gif";s:4:"7a4b";s:34:"Resources/Public/Images/Wizard.gif";s:4:"f714";s:49:"Resources/Public/Javascript/galleria-1.2.6.min.js";s:4:"914a";s:46:"Resources/Public/Javascript/jcrop-0.9.9.min.js";s:4:"26e8";s:47:"Resources/Public/Javascript/jquery-1.7.1.min.js";s:4:"ddb8";s:39:"Resources/Public/Stylesheet/Backend.css";s:4:"43de";s:39:"Resources/Public/Stylesheet/Gallery.css";s:4:"f904";s:37:"Resources/Public/Stylesheet/Jcrop.css";s:4:"d04a";s:42:"Resources/Public/Themes/classic/README.rst";s:4:"eaf4";s:49:"Resources/Public/Themes/classic/classic-demo.html";s:4:"56d3";s:50:"Resources/Public/Themes/classic/classic-loader.gif";s:4:"0b0f";s:47:"Resources/Public/Themes/classic/classic-map.png";s:4:"e554";s:52:"Resources/Public/Themes/classic/galleria.classic.css";s:4:"e7b3";s:51:"Resources/Public/Themes/classic/galleria.classic.js";s:4:"23cb";s:55:"Resources/Public/Themes/classic/galleria.classic.min.js";s:4:"4729";s:14:"doc/manual.sxw";s:4:"7d2d";}',
);

?>