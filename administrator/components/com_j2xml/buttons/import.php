<?php
/**
 * @version		2.5.85 buttons/import.php
 * @package		J2XML
 * @subpackage	com_j2xml
 * @since		1.5.0
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010-2012 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

class JButtonImport extends JButton
{
	/**
	 * Button type
	 *
	 * @access	public
	 * @var		string
	 */
	var $_name = 'Import';

	function fetchButton($type='Import', $name = '', $text1 = '', $text2 = '', $task = 'import', $list = true, $hideMenu = false )
	{
		// Prepare the query
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('element'));
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('type') . ' = ' . $db->quote('library'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('filemanager'));
		$query->where($db->quoteName('enabled') . ' = 1');
		$db->setQuery($query);
		$filemanagerlib = ($db->loadResult() != null);
		
		$doAction = 'index.php?option=com_j2xml&amp;task='.$task;
		$doTask	= $this->_getCommand($name, $task, $hideMenu);

		$html = "";
		$html .= "<form name=\"".$name."Form\" method=\"post\" enctype=\"multipart/form-data\" action=\"$doAction\" style=\"float:left\">\n";
    	$html .= "<input type=\"file\" name=\"file_upload\" />\n";
    	if ($filemanagerlib)
    	{
			JHTML::script('FileManager.js','media/lib_filemanager/Source/');
			JHTML::script('Language.en.js','media/lib_filemanager/Language/');
			JHtml::script('com_j2xml/selectfile.js', false, true);
			JHTML::stylesheet('FileManager.css','media/lib_filemanager/Css/');
    		$html .= "<input id=\"remote_file\" name=\"remote_file\" />";	
    	}
    	$html .= JHTML::_('form.token');
    	$html .= "</form>\n";

    	if ($filemanagerlib)
    	{
    		$i18n_text	= JText::_($text1);
			$class	= $this->fetchIconClass($name.'-open');
	    	$html .= "<a href=\"#\" id=\"{$name}-open\" class=\"toolbar\">\n";
			$html .= "<span class=\"$class\" title=\"$i18n_text\">\n";
			$html .= "</span>\n";
			$html .= "$i18n_text\n";
			$html .= "</a>\n";
    	}
		$i18n_text	= JText::_($text2);
		$class	= $this->fetchIconClass($name.'-import');
		$html .= "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "<span class=\"$class\" title=\"$i18n_text\">\n";
		$html .= "</span>\n";
		$html .= "$i18n_text\n";
		$html .= "</a>\n";
		
   		return $html;
	}

	/**
	 * Get the name of the toolbar.
	 *
	 * @return	string
	 * @since	1.5
	 */
	private function _getToolbarName()
	{
		return $this->_parent->getName();
	}

	/**
	 * Get the button CSS Id
	 *
	 * @access	public
	 * @return	string	Button CSS Id
	 * @since	1.5
	 */
	public function fetchId($type='Import', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		return $this->_getToolbarName().'-'.$name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @access	private
	 * @param	string	$name	The task name as seen by the user
	 * @param	string	$task	The task used by the application
	 * @param	???		$list
	 * @param	boolean	$hide
	 * @return	string	JavaScript command string
	 * @since	1.5
	 */
	private function _getCommand($name, $task, $hide)
	{
		$todo		= JString::strtolower(JText::_($name));
		$message	= JText::sprintf('COM_J2XML_BUTTON_PLEASE_SELECT_A_FILE_TO', $todo);
		$message	= addslashes($message);
		$hidecode	= $hide ? 'hideMainMenu();' : '';
	
		return "javascript:if((document.".$name."Form.file_upload.value=='') && (document.".$name."Form.remote_file.value=='')){alert('$message');}else{ $hidecode document.".$name."Form.submit()}";
	}
}