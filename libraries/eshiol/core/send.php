<?php
/** 
 * @version		12.0.1 eshiol/core/send.php
 * 
 * @package		eshiol Library
 * @subpackage	lib_eshiol
 * @since		12.0.1
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2012 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * eshiol Library is free software. This version may have been modified 
 * pursuant to the GNU General Public License, and as distributed it includes 
 * or is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.toolbar.button');

JHTML::_('behavior.mootools');

/**
 * Renders an Send button
 */
class JButtonSend extends JButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'Send';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type  Unused string.
	 * @param   string   $name  The name of the button icon class.
	 * @param   string   $text  Button text.
	 * @param   string   $task  Task associated with the button.
	 * @param   boolean  $list  True to allow lists
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   12.0.1
	 */
	public function fetchButton($type = 'Send', $name = '', $text = '', $task = '', $view = '', $list=true)
	{
		$i18n_text = JText::_($text);
		$class = $this->fetchIconClass($name);

		// Load the modal behavior script.
		JHTML::_('behavior.framework',true);
		$uncompressed = JFactory::getConfig()->get('debug') ? '-uncompressed' : '';
		JHTML::_('script','system/modal'.$uncompressed.'.js', true, true);
		JHTML::_('stylesheet','media/system/css/modal.css');
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration(" .icon-32-waiting {background:url(../media/lib_eshiol/images/icon-32-waiting.gif) no-repeat; }");
		$doc->addScript("../media/lib_eshiol/js/encryption.js");
		$doc->addScript("../media/lib_eshiol/js/core.js");
		$todo		= JString::strtolower(JText::_( $text ));
		$message	= JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
				
		$tmp = explode('.', $task);
		$task = (isset($tmp[2]) ? $tmp[1].'.'.$tmp[2] : $tmp[1]);
		$url = "index.php?option=com_$tmp[0]&task=$task&format=json";
		$url .= '&'.JUtility::getToken().'=1';
		$url = base64_encode($url);

		$link = JRoute::_("index.php?option=com_{$tmp[0]}&amp;view={$view}&amp;layout=modal&amp;tmpl=component&amp;filter_state=1&amp;field={$name}&amp;url={$url}");
		$rel_handler = "{handler: 'iframe', size: {x: 800, y: 400}}";
		$onclick = "if(document.adminForm.boxchecked.value==0) alert('{$message}'); else SqueezeBox.setContent('iframe',this.href);";		
		
		$html = "<a class=\"toolbar\" href=\"{$link}\" rel=\"{$rel_handler}\" onclick=\"{$onclick} return false; \">";
		$html .= "<span class=\"$class\">\n";
		$html .= "</span>\n";
		$html .= "$i18n_text\n";
		$html .= "</a>\n";

		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param   string   $type      Unused string.
	 * @param   string   $name      Name to be used as apart of the id
	 * @param   string   $text      Button text
	 * @param   string   $task      The task associated with the button
	 * @param   boolean  $list      True to allow use of lists
	 * @param   boolean  $hideMenu  True to hide the menu on click
	 *
	 * @return  string  Button CSS Id
	 *
	 * @since   12.0.1
	 */
	public function fetchId($type = 'Send', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		return $this->_parent->getName() . '-' . $name;
	}
}
