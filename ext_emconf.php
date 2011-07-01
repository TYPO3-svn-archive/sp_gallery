<?php

########################################################################
# Extension Manager/Repository config file for ext "sp_gallery".
#
# Auto generated 30-06-2011 15:32
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Image gallery',
	'description' => 'An image gallery based on extbase, fluid, jQuery and the Galleria gallery.',
	'category' => 'plugin',
	'author' => 'Kai Vogel',
	'author_email' => 'kai.vogel@speedprogs.de',
	'author_company' => 'Speedprogs.de',
	'shy' => '',
	'dependencies' => 'cms,extbase,fluid,scheduler',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
			'scheduler' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:39:{s:16:"ext_autoload.php";s:4:"f2d8";s:12:"ext_icon.gif";s:4:"3ac5";s:17:"ext_localconf.php";s:4:"b068";s:14:"ext_tables.php";s:4:"d254";s:14:"ext_tables.sql";s:4:"5d5a";s:40:"Classes/Controller/GalleryController.php";s:4:"387a";s:32:"Classes/Domain/Model/Gallery.php";s:4:"440a";s:47:"Classes/Domain/Repository/GalleryRepository.php";s:4:"8125";s:32:"Classes/Service/GalleryImage.php";s:4:"aa85";s:34:"Classes/Task/DirectoryListener.php";s:4:"a871";s:24:"Classes/Utility/File.php";s:4:"cb5b";s:30:"Classes/Utility/TypoScript.php";s:4:"301d";s:49:"Classes/ViewHelpers/AbstractGalleryViewHelper.php";s:4:"a564";s:41:"Classes/ViewHelpers/GalleryViewHelper.php";s:4:"8084";s:46:"Classes/ViewHelpers/TeaserImagesViewHelper.php";s:4:"541b";s:35:"Configuration/FlexForms/Gallery.xml";s:4:"e2d4";s:29:"Configuration/TCA/Gallery.php";s:4:"97c7";s:38:"Configuration/TypoScript/constants.txt";s:4:"31e5";s:34:"Configuration/TypoScript/setup.txt";s:4:"652b";s:40:"Resources/Private/Language/locallang.xml";s:4:"5353";s:78:"Resources/Private/Language/locallang_csh_tx_spgallery_domain_model_gallery.xml";s:4:"0646";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"97f2";s:38:"Resources/Private/Layouts/Default.html";s:4:"6d77";s:45:"Resources/Private/Templates/Gallery/List.html";s:4:"911f";s:45:"Resources/Private/Templates/Gallery/Show.html";s:4:"d674";s:47:"Resources/Private/Templates/Gallery/Teaser.html";s:4:"d847";s:51:"Resources/Private/Templates/Gallery/TeaserList.html";s:4:"fa3a";s:34:"Resources/Public/Images/Wizard.gif";s:4:"f714";s:49:"Resources/Public/Javascript/galleria-1.2.4.min.js";s:4:"9ba8";s:47:"Resources/Public/Javascript/jquery-1.6.1.min.js";s:4:"a34f";s:39:"Resources/Public/Stylesheet/gallery.css";s:4:"5c66";s:42:"Resources/Public/Themes/classic/README.rst";s:4:"eaf4";s:49:"Resources/Public/Themes/classic/classic-demo.html";s:4:"e796";s:50:"Resources/Public/Themes/classic/classic-loader.gif";s:4:"0b0f";s:47:"Resources/Public/Themes/classic/classic-map.png";s:4:"e554";s:52:"Resources/Public/Themes/classic/galleria.classic.css";s:4:"f4e3";s:51:"Resources/Public/Themes/classic/galleria.classic.js";s:4:"dff4";s:55:"Resources/Public/Themes/classic/galleria.classic.min.js";s:4:"3936";s:14:"doc/manual.sxw";s:4:"de2e";}',
);

?>