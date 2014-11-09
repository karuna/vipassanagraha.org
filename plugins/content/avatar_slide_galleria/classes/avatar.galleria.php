<?php
// No direct access
defined('_JEXEC') or die;
if(class_exists('AvatarGalleriaOptions') != true)
{
	class AvatarGalleriaOptions{
		public $path		= NULL;
		public $time		= 4000;		
		public $auto		= 'true';	
		public $slideHeight = '300px';	
		public $slideWidth	= '100%';	
		public $lightBox	= 'true'; 	
		public $transition	= 'fade';	
		public $transitionSpeed = 500;	
		public $searchBy	='search';	
		public $maxImages	= 20;		
		public $flickrSort	= 'interestingness-desc';
		public $imageQuality= 'original';
		public $string		= 'galleria';
		public $imgCrop 	= 'true';
		public $imgPan		= 'true'; 
		public $imgPanSmoothness = 12; 	
		public $showCounter	= 'true'; 
		public $showImageNav= 'true'; 	
		public $swipe		= 'true';	
		public $thumbnails	= 'true';	
		public $cr			= 'true';	
		public $responsive	= 'true';	
		public $info		= 'true';	
		public $theme		= 'classic';
		public $dataSort	= 'false';
		public $source 		= 'folder';
		public $progress	= 'true';
		public $fullscreen	= 'true';
		public $jquery		= 'latest';
	}
}
?>