<?php 
/**
 * @version		$Id: helper.php 5 2012-04-06 20:10:35Z mozart $
 * @copyright	JoomAvatar.com
 * @author		Tran Nam Chung
 * @mail		chungnt@joomavatar.com
 * @link		http://joomavatar.com
 * @license		License GNU General Public License version 2 or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
	<div id="avatar_galleria_<?php echo $id?>" class="galleria-<?php echo $plgGalleria[$countPlg]->theme?>" style="margin: auto;width:<?php echo $plgGalleria[$countPlg]->slideWidth;?>;height:<?php echo $plgGalleria[$countPlg]->slideHeight?>;">
		<?php
			$count = 0;
			for($p = 0 ; $p < sizeof($path) ; $p++)
			{			
				for($n = 0 ; $n < sizeof($tmpListImage[$p]) && $n< $plgGalleria[$countPlg]->maxImages ; $n++, $count++)
				{
					$tmp = $tmpListImage[$p][$n];
					echo "<a href='".JURI::base()."images/".$path[$p]."/".$tmp."'>";
					echo "<img style='display:none;' ";
					
					if(file_exists(JPATH_ROOT."/images/".$path[$p]."/thumb/".$tmp))
							echo "src='".JURI::base()."images/".$path[$p]."/thumb/".$tmp."'";
					else
						echo "src='".JURI::base()."images/".$path[$p]."/".$tmp."'";
					$title = preg_replace('/_/', ' ', substr( $tmp, 0, strrpos($tmp, '.')));
					echo 'data-title="'.htmlspecialchars($title).'"/></a>';
				}
			}    		
		?>
	</div>