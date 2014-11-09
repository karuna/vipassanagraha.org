<?php
/**
 * @version		12.0.2 eshiol/core/send.php
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

// no direct access
defined('_JEXEC') or die('Restricted access.');
		$doTask	= $this->_getCommand($text, $task, $list);
		
		$html ="";
		$html .= "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "\n";
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
	 * @since   11.1
	 */
	public function fetchId($type = 'Standard2', $name = '', $text = '', $task = '', $list = true)
	{
		return $this->_parent->getName() . '-' . $name;
	}
	
		JHtml::_('behavior.framework');
		$message = JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		$message = addslashes($message);

		$tmp = explode('.', $task);
		$i = count($tmp);
		if ($i == 3)
		{
			$task = $tmp[1].'.'.$tmp[2];
			$option = $tmp[0];
		} 
		else 
			$option = '';
		
		$cmd = "Joomla.submitbutton('{$task}');";	
		if ($option)
			$cmd = "{
				var action = document.adminForm.action;
				document.adminForm.action = '".JURI::base(true)."/index.php?option=com_".$option."';
				".$cmd."
				document.adminForm.action = action;
			}"; 
		if ($list)
			$cmd = "
			if (document.adminForm.boxchecked.value==0)
				alert('{$message}');
			else ".$cmd;
		return $cmd;