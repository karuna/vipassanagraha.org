<?php
/**
 * @version		$Id: mod_avatar_slide_galleria.php 97 2012-04-14 chungtn2910@gmail.com $
 * @copyright	JoomAvatar.com
 * @author		Tran Nam Chung
 * @link		http://joomavatar.com
 * @license		License GNU General Public License version 2 or later
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
require_once( dirname(__FILE__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'avatar.galleria.php' );
class plgContentavatar_slide_galleria extends JPlugin
{
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();
			
		$countPlg = 0 ;
		if(!isset($plgGalleria)){$plgGalleria = array();}
		
		
		jimport('joomla.html.pane');
		$text = &$article->text;

		$pattern = '/\{avatargalleria[^}]*\/}/i';
		preg_match_all($pattern, $text, $matches);
		$arrayFormat = array();
		
		foreach ($matches[0] as $format)
		{
			ob_start();
			$plgGalleria[$countPlg]= new AvatarGalleriaOptions;
			$format = strip_tags($format);
			try
			{
				$pattern = '/\bsrc=[a-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0)
				{
					$tmp = explode('=', $Info[0]);			
					if(strtolower($tmp[1]) == "flickr" || strtolower($tmp[1]) == "picasa" || strtolower($tmp[1]) == "folder")
						$plgGalleria[$countPlg]->source = $tmp[1];
					else
						throw new Exception("check '".$tmp[0]."' again");		
				}
				else{
					throw new Exception("miss '".$tmp[0]."'");
				}
				
				//check path
				$pattern = '/\bpath=[a-z,A-Z0-9\/]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0)
				{
					$tmp = explode('=', $Info[0]);			
					$plgGalleria[$countPlg]->path = $tmp[1];
				}
				else
				{
					if($plgGalleria[$countPlg]->source == "folder")
					{
						throw new Exception("miss '".$tmp[0]."'");
					}
				}
				
				//check autoplay
				$pattern = '/\bauto=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->auto = $tmp[1];
					else {
						throw new Exception("check '".$tmp[0]."' again");
					}
				}
				
				//check  time
				$pattern = '/\btime=[0-9]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					$plgGalleria[$countPlg]->time = $tmp[1];
				}
				
				//check height
				$pattern = '/\bheight=[0-9px%]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0)
				{
					$tmp = explode('=', $Info[0]);
					$plgGalleria[$countPlg]->slideHeight = $tmp[1];
				}
				
				//check width
				$pattern = '/\bwidth=[0-9px%]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					$plgGalleria[$countPlg]->slideWidth = $tmp[1];
				}
				
				//check responsive
				$pattern = '/\bresponsive=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->responsive = $tmp[1];
					else{
						throw new Exception("check '".$tmp[0]."' again");
					}
				}
				
				//check lightBox
				$pattern = '/\blightbox=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->lightBox = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check fullscreen
				$pattern = '/\bfullscreen=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->fullscreen = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check progress
				$pattern = '/\bprogress=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->progress = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check dataSort
				$pattern = '/\bdatasort=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "false" || strtolower($tmp[1]) == "random")
						$plgGalleria[$countPlg]->dataSort = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check transition
				$pattern = '/\btransition=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "fade" || strtolower($tmp[1]) == "slide" || strtolower($tmp[1]) == "pulse" ||strtolower($tmp[1]) == "fadeslide")
						$plgGalleria[$countPlg]->transition = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check theme
				$pattern = '/\btheme=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "classic" || strtolower($tmp[1]) == "august" || strtolower($tmp[1]) == "september")
						$plgGalleria[$countPlg]->theme = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				else
				{
					throw new Exception("miss '".$tmp[0]."'");
				}
				
				//check transition speed
				$pattern = '/\bspeed=[0-9]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					$plgGalleria[$countPlg]->transitionSpeed = $tmp[1];
				}
				
				//check search method
				$pattern = '/\bsearch=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if($plgGalleria[$countPlg]->source == 'flickr')
					{
						if(strtolower($tmp[1]) == "search" || strtolower($tmp[1]) == "tags" || strtolower($tmp[1]) == "user" || strtolower($tmp[1]) == "set" || strtolower($tmp[1]) == "gallery" || strtolower($tmp[1]) == "groupSearch" || strtolower($tmp[1]) == "groupId")
							$plgGalleria[$countPlg]->searchBy = $tmp[1];
						else 
							throw new Exception("check '".$tmp[0]."' again");
					}
					if($plgGalleria[$countPlg]->source == 'picasa')
					{
						if(strtolower($tmp[1]) == "search" || strtolower($tmp[1]) == "useralbum" ||strtolower($tmp[1]) == "user")
							$plgGalleria[$countPlg]->searchBy = $tmp[1];
						else 
							throw new Exception("check '".$tmp[0]."' again");
					}
					
				}
				
				//check string for search
				$pattern = '/\bstring=\$.*\$/';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('$', $Info[0]);
					$plgGalleria[$countPlg]->string = $tmp[1];
				}
				else
				{
					if($plgGalleria[$countPlg]->source != 'folder')
					{
						throw new Exception("miss '".$tmp[0]."'");
					}
				}
				
				//check count
				$pattern = '/\bcount=[0-9]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					$plgGalleria[$countPlg]->maxImages = $tmp[1];
				}
				
				//check sort
				$pattern = '/\bflickrsort=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "dpa" || strtolower($tmp[1]) == "dpd" || strtolower($tmp[1]) == "dta" || strtolower($tmp[1]) == "dtd" || strtolower($tmp[1]) == "id" || strtolower($tmp[1]) == "ia" || strtolower($tmp[1]) == "relevance")
						$plgGalleria[$countPlg]->flickrSort = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check quality
				$pattern = '/\bquality=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "low" || strtolower($tmp[1]) == "medium" ||strtolower($tmp[1]) == "high" || strtolower($tmp[1]) == "original")
						$plgGalleria[$countPlg]->imageQuality = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check imageCrop
				$pattern = '/\bimgcrop=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "false" || strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "height" || strtolower($tmp[1]) == "width" || strtolower($tmp[1]) == "landscape" || strtolower($tmp[1]) == "portrait")
						$plgGalleria[$countPlg]->imgCrop = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check imagePan
				$pattern = '/\bimgpan=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->imgPan = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check imgPanSmoothness
				$pattern = '/\bimgpansmoothness=[0-9]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0)
				{
					$tmp = explode('=', $imgPanSmoothnessInfo[0]);
					$plgGalleria[$countPlg]->imgPanSmoothness = $tmp[1];
				}
				
				//check showCounter
				$pattern = '/\bshowcounter=[A-Za-z]*/i';
				preg_match($pattern, $format,$Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->showCounter = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check showNavigation
				$pattern = '/\bshownav=[A-Za-z]*/i';
				preg_match($pattern, $format,$Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->showImageNav = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check swipe
				$pattern = '/\bswipe=[A-Za-z]*/i';
				preg_match($pattern, $format,$Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->swipe=$tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check thumbnails
				$pattern = '/\bthumbnails=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false" || strtolower($tmp[1]) == "empty" || strtolower($tmp[1]) == "numbers")
						$plgGalleria[$countPlg]->thumbnails = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				$pattern = '/\bcr=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->cr = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check show info
				$pattern = '/\binfo=[A-Za-z]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
					if(strtolower($tmp[1]) == "true" || strtolower($tmp[1]) == "false")
						$plgGalleria[$countPlg]->info = $tmp[1];
					else 
						throw new Exception("check '".$tmp[0]."' again");
				}
				
				//check jquery
				$pattern = '/\bjquery=[A-Za-z0-9.]*/i';
				preg_match($pattern, $format, $Info);
				if (count($Info) > 0) 
				{
					$tmp = explode('=', $Info[0]);
						$plgGalleria[$countPlg]->jquery = $tmp[1];
				}
				
			} catch(Exception $e){
				echo "<p style='color:red;text-align:center;'>Avatar Slide Galleria Error: ", $e->getMessage(), "</p>";
			}
			//start echo HTML & JS
			$id = "plugin";
			$plgGalleria[$countPlg]->searchBy	="search";
			$plgGalleria[$countPlg]->maxImages 	= 7;
			$plgGalleria[$countPlg]->theme 		= "classic";
			$plgGalleria[$countPlg]->cr			= "false";
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::base().'plugins/content/avatar_slide_galleria/assets/css/galleria.'.$plgGalleria[$countPlg]->theme.'.css');
			switch ($plgGalleria[$countPlg]->jquery) {
				case 'unload':	
					break;
				case 'latest':
					$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js');
					$document->addScriptDeclaration('jQuery.noConflict();');
					break;
				default:
					$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/'.$plgGalleria[$countPlg]->jquery.'/jquery.min.js');
					$document->addScriptDeclaration('jQuery.noConflict();');
					break;
			}	
			$document->addScript(JURI::base().'plugins/content/avatar_slide_galleria/assets/js/galleria-1.2.9.min.js');
			switch ($plgGalleria[$countPlg]->source) {
				case 'folder':
					require_once( dirname(__FILE__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'avatar.image.php' );
					$tmp = new imageData();
					$path = explode(",",$plgGalleria[$countPlg]->path);
					$tmp->setPath($path);
					$tmpListImage = $tmp->getArrayImageLinks();
					require(JPATH_PLUGINS."/content/avatar_slide_galleria/tmpl/folder.php");
					break;
				case 'picasa':
					require(JPATH_PLUGINS."/content/avatar_slide_galleria/tmpl/picasa.php");
					break;
				case 'flickr':
					require(JPATH_PLUGINS."/content/avatar_slide_galleria/tmpl/flickr.php");
					break;
			}
			?>
			<script>
			jQuery.noConflict();
			(function($) 
			{ 
					$(document).ready( function()
					{	
						Galleria.loadTheme('<?php echo JURI::base()."plugins/content/avatar_slide_galleria/assets/js/galleria.".$plgGalleria[$countPlg]->theme.".js"?>');
						Galleria.JURI = "<?php echo JURI::base(); ?>plugins/content/avatar_slide_galleria/assets/images/loader.gif";
						<?php
						if($plgGalleria[$countPlg]->source == 'picasa')$document->addScript(JURI::base().'plugins/content/avatar_slide_galleria/assets/js/galleria.picasa.js');
						if($plgGalleria[$countPlg]->source == 'flickr')$document->addScript(JURI::base().'plugins/content/avatar_slide_galleria/assets/js/galleria.flickr.js');
						?>
						//Initialize Galleria
						Galleria.run('#avatar_galleria_<?php echo $id;?>',{
							lightbox		:<?php echo $plgGalleria[$countPlg]->lightBox;?>,
							responsive		: <?php echo $plgGalleria[$countPlg]->responsive;?>,
							transition		:'<?php echo $plgGalleria[$countPlg]->transition;?>',
							transitionSpeed	:<?php echo $plgGalleria[$countPlg]->transitionSpeed;?>,
							imageCrop		: <?php if(strtolower($plgGalleria[$countPlg]->imgCrop) == 'true' || strtolower($plgGalleria[$countPlg]->imgCrop)  == 'false') echo $plgGalleria[$countPlg]->imgCrop; else echo "'".$plgGalleria[$countPlg]->imgCrop."'";?>,
							imagePan		: <?php echo $plgGalleria[$countPlg]->imgPan;?>,
							imagePanSmoothness	: <?php echo $plgGalleria[$countPlg]->imgPanSmoothness;?>,
							showCounter		: <?php echo $plgGalleria[$countPlg]->showCounter;?>,
							showImagenav	: <?php echo $plgGalleria[$countPlg]->showImageNav;?>,
							_showFullscreen : <?php echo $plgGalleria[$countPlg]->fullscreen;?>,
							_showProgress	: <?php echo $plgGalleria[$countPlg]->progress;?>,
							swipe			: <?php echo $plgGalleria[$countPlg]->swipe;?>,
							showInfo		: <?php echo $plgGalleria[$countPlg]->info;?>,
							dataSort		: <?php if($plgGalleria[$countPlg]->dataSort == 'false') echo $plgGalleria[$countPlg]->dataSort; else echo"'".$plgGalleria[$countPlg]->dataSort."'";?>,
							thumbnails		: <?php if(strtolower($plgGalleria[$countPlg]->thumbnails) == 'true' || strtolower($plgGalleria[$countPlg]->thumbnails) == 'false') echo $plgGalleria[$countPlg]->thumbnails; else echo "'".$plgGalleria[$countPlg]->thumbnails."'";?>
							<?php 
								if(strtolower($plgGalleria[$countPlg]->auto) == 'true' ) {echo ',autoplay:';echo $plgGalleria[$countPlg]->time;}
								if($plgGalleria[$countPlg]->source == 'flickr' || $plgGalleria[$countPlg]->source == 'picasa')
								{
									echo ",".$plgGalleria[$countPlg]->source.":'".$plgGalleria[$countPlg]->searchBy.":".$plgGalleria[$countPlg]->string."',";
							    	echo $plgGalleria[$countPlg]->source;
									echo "Options: {max:".$plgGalleria[$countPlg]->maxImages.",";
							    	if($plgGalleria[$countPlg]->source == 'flickr') 
							    		 {echo "sort:'".$plgGalleria[$countPlg]->flickrSort."',";}
							    	echo "imageSize	: '".$plgGalleria[$countPlg]->imageQuality."'}";
						    	}?>
						});
					});
			})(jQuery);
			</script>
			<div class="avatar-copyright" style="width:100%;margin: 5px;text-align: center;display : <?php if (strtolower($plgGalleria[$countPlg]->cr) == 'false') echo 'none'; else echo 'block';?> ;">
			&copy; JoomAvatar.com
				<a target="_blank" href="http://joomavatar.com" title="Joomla Template & Extension">Joomla Extension</a>-
				<a target="_blank" href="http://joomavatar.com" title="Joomla Template & Extension">Joomla Template</a>
			</div>
		<?php
			$countPlg++;
			$content = ob_get_clean();
			$arrayFormat[$format] = $content;
		}
		
		foreach ($arrayFormat as $keyFormat => $valueFormat)
		{
			if (!empty($valueFormat)) {
				$text = str_replace($keyFormat, $valueFormat, $text);	
			} else {
				$text = str_replace($keyFormat, '', $text);
			}
		}
	}
}