<?php
/**
 * XTypo: Plugin for Content
 * @version		$Id: xtypo.php 2.1
 * @Official site http://www.templateplazza.com
 * @package		Joomla 2.5.x
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Icon http://www.famfamfam.com/lab/icons/silk/
 */
 
// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.event.plugin' );
//require_once JPATH_SITE.'/components/com_content/helpers/route.php';	

class plgContentxtypo extends JPlugin 
{
	function plgContentxtypo(&$subject, $params){
		parent::__construct( $subject, $params );
 	}
	
	function onContentPrepare( $context, &$row, &$params, $limitstart=0 )
	{
		// Get Plugin info
		//$plugin	= JPluginHelper::getPlugin('content','xtypo');
		//$param = $this->params;
			
		$pluginbase		= ''.JURI::base(true).'/plugins/content/xtypo/';
		$theme 			=  $this->params->get('theme', '');
		//$loadJQuery 	= $this->params->get('jquery', 0);
		$document		= JFactory::getDocument();
		$document->addStyleSheet( $pluginbase.'themes/'.$theme.'/style.css' );
		//$document->addStyleSheet( $pluginbase.'assets/prettify.css' );
		
		// make code inside pre tag pretty, hopefully the template already load jQuery library if not get from Google CDN
		/*
		if ( !JFactory::getApplication()->get('jquery') && $loadJQuery ) {
            $document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js');
			//$document->addScriptDeclaration(" $ = jQuery.noConflict();");
            JFactory::getApplication()->set('jquery', true);
        }
		
		$document->addScript($pluginbase.'assets/prettify.js');
		$document->addScriptDeclaration('
			!function ($) {
				$(function(){
					 window.prettyPrint && prettyPrint(); 
				});
			}(window.jQuery)
		');
		*/
		$document->addScript( $pluginbase.'assets/script.js' );

$regex = array(
"xtypo_alert" => array("<p class=\"xtypo-alert\"><span class=\"iconxt-alert\"></span>***code***</p>","#{xtypo_alert}(.*?){/xtypo_alert}#s") ,
"xtypo_info" => array("<p class=\"xtypo_info\"><span class=\"iconxt-info\"></span>***code***</p>","#{xtypo_info}(.*?){/xtypo_info}#s") ,
"xtypo_warning" => array("<p class=\"xtypo_warning\"><span class=\"iconxt-warning\"></span>***code***</p>","#{xtypo_warning}(.*?){/xtypo_warning}#s") ,
"xtypo_sticky" => array("<p class=\"xtypo_sticky\"><span class=\"iconxt-sticky\"></span>***code***</p>","#{xtypo_sticky}(.*?){/xtypo_sticky}#s") ,
"xtypo_feed" => array("<p class=\"xtypo_feed\"><span class=\"iconxt-feed\"></span>***code***</p>","#{xtypo_feed}(.*?){/xtypo_feed}#s") ,
"xtypo_download" => array("<p class=\"xtypo_download\"><span class=\"iconxt-download\"></span>***code***</p>","#{xtypo_download}(.*?){/xtypo_download}#s") ,
"xtypo_quote" => array("<blockquote class=\"xtypo_quote\"><p>***code***</p></blockquote>","#{xtypo_quote}(.*?){/xtypo_quote}#s") ,
"xtypo_quote_left" => array("<blockquote class=\"xtypo_quote_left\"><p>***code***</p></blockquote>","#{xtypo_quote_left}(.*?){/xtypo_quote_left}#s") ,
"xtypo_quote_right" => array("<blockquote class=\"xtypo_quote_right\"><p>***code***</p></blockquote>","#{xtypo_quote_right}(.*?){/xtypo_quote_right}#s") ,
"xtypo_code" => array("<code class=\"xtypo_code\"><br />***code***</code>", "#{xtypo_code}(.*?){/xtypo_code}#s") ,
//"xtypo_code" => array("<pre class=\"prettyprint linenums\"><br/>***code***</code>", "#{xtypo_code}(.*?){/xtypo_code}#s") ,
"xtypo_dropcap" => array("<span class=\"xtypo_dropcap\">***code***</span>", "#{xtypo_dropcap}(.*?){/xtypo_dropcap}#s") ,
"xtypo_button1" => array("<div class=\"xtypo_button1\">***code***</div>","#{xtypo_button1}(.*?){/xtypo_button1}#s") ,
"xtypo_button2" => array("<div class=\"xtypo_button2\">***code***</div>","#{xtypo_button2}(.*?){/xtypo_button2}#s") ,
"xtypo_button3" => array("<div class=\"xtypo_button3\">***code***</div>","#{xtypo_button3}(.*?){/xtypo_button3}#s") ,

// start rounded 1
"xtypo_rounded1" => array("<div class=\"xtypo_rounded1\">***code***</div>", "#{xtypo_rounded1}(.*?){/xtypo_rounded1}#s"),
"xtypo_rounded_left1" => array("<div class=\"xtypo_rounded1_left\">***code***</div>" , "#{xtypo_rounded_left1}(.*?){/xtypo_rounded_left1}#s"),
"xtypo_rounded_right1" => array("<div class=\"xtypo_rounded1_right\">***code***</div>", "#{xtypo_rounded_right1}(.*?){/xtypo_rounded_right1}#s"),

// start rounded 2
"xtypo_rounded2" => array("<div class=\"xtypo_rounded2\">***code***</div>", "#{xtypo_rounded2}(.*?){/xtypo_rounded2}#s"),
"xtypo_rounded_left2" => array("<div class=\"xtypo_rounded2_left\">***code***</div>", "#{xtypo_rounded_left2}(.*?){/xtypo_rounded_left2}#s"),
"xtypo_rounded_right2" => array("<div class=\"xtypo_rounded2_right\">***code***</div>", "#{xtypo_rounded_right2}(.*?){/xtypo_rounded_right2}#s"),

// start rounded 3
"xtypo_rounded3" => array("<div class=\"xtypo_rounded3\">***code***</div>", "#{xtypo_rounded3}(.*?){/xtypo_rounded3}#s"),
"xtypo_rounded_left3" => array("<div class=\"xtypo_rounded3_left\">***code***</div>", "#{xtypo_rounded_left3}(.*?){/xtypo_rounded_left3}#s"),
"xtypo_rounded_right3" => array("<div class=\"xtypo_rounded3_right\">***code***</div>", "#{xtypo_rounded_right3}(.*?){/xtypo_rounded_right3}#s"),

// start rounded 4
"xtypo_rounded4" => array("<div class=\"xtypo_rounded4\">***code***</div>", "#{xtypo_rounded4}(.*?){/xtypo_rounded4}#s"),
"xtypo_rounded_left4" => array("<div class=\"xtypo_rounded4_left\">***code***</div>", "#{xtypo_rounded_left4}(.*?){/xtypo_rounded_left4}#s"),
"xtypo_rounded_right4" => array("<div class=\"xtypo_rounded4_right\">***code***</div>", "#{xtypo_rounded_right4}(.*?){/xtypo_rounded_right4}#s"),

"xtypo_list" => array("<div class=\"xtypo_list\">***code***</div>","#{xtypo_list}(.*?){/xtypo_list}#s") ,
"xtypo_list_left" => array("<div class=\"xtypo_list_left\">***code***</div>","#{xtypo_list_left}(.*?){/xtypo_list_left}#s") ,
"xtypo_list_right" => array("<div class=\"xtypo_list_right\">***code***</div>","#{xtypo_list_right}(.*?){/xtypo_list_right}#s") ,

);


	// prepend and append code
	$startcode = "<!-- Xtypo - Another Quality Freebie from TemplatePlazza.com -->";
	$endcode = "";
	    
	if ( !$this->params->get( 'enabled', 1 ) ) {		
		foreach ($regex as $key => $value) {
			$row->text = preg_replace( $regex[$key][1], '', $row->text );
		}
		return;
	}
		

	foreach ($regex as $key => $value) {  // searching for marks   		
		if (preg_match_all($regex[$key][1], $row->text, $matches, PREG_PATTERN_ORDER) > 0) {      			 
			foreach ($matches[1] as $match) {	
				$code = str_replace("***code***", $match, $regex[$key][0] );
				$a = preg_quote($match);
				$a = str_replace("'", "\'", $a);
				$row->text = preg_replace("'{".preg_quote($key)."}".$a."{/".preg_quote($key)."}'s", $startcode.$code.$endcode , $row->text );

	    	}
	 	}	    	
	}
	}
}
?>
