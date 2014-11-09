<?php
/**
 * @version		3.0.25 plugins/system/j2xml/j2xml.php
 * 
 * @package		J2XML
 * @subpackage	plg_system_j2xml
 * @since		1.5.2
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010-2012 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

class plgSystemJ2XML extends JPlugin
{
	var $params = null;
	/**
	 * CONSTRUCTOR
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function __construct(&$subject, $params)
	{
		$this->params = $params;
		parent::__construct($subject, $params);
		JPlugin::loadLanguage('plg_system_j2xml');
	}

	/**
	 * Method is called by index.php and administrator/index.php
	 *
	 * @access	public
	 */
	public function onAfterDispatch()
	{
		$app = JFactory::getApplication();
		if($app->getName() != 'administrator') {
			return true;
		}

		$enabled = JComponentHelper::getComponent('com_j2xml', true);
		if (!$enabled->enabled) 
			return true; 

		$option = JRequest::getVar('option');
		$view = JRequest::getVar('view');

		if (version_compare(JPlatform::RELEASE, '12', '<'))
		{
			if (($option == 'com_content') && (!$view || $view == 'articles')
				|| ($option == 'com_users') && (!$view || $view == 'users')
				|| ($option == 'com_weblinks') && (!$view || $view == 'weblinks')
			) {		
				jimport('eshiol.core.send');
				jimport('eshiol.core.standard2');
					
				$toolbar = JToolBar::getInstance('toolbar');
				$doc = JFactory::getDocument();
				$icon_32_export = " .icon-32-j2xml_export {background:url(../media/plg_system_j2xml/images/icon-32-export.png) no-repeat; }"; 
				$doc->addStyleDeclaration($icon_32_export);
				$icon_32_send = " .icon-32-j2xml_send {background:url(../media/plg_system_j2xml/images/icon-32-send.png) no-repeat; }"; 
				$doc->addStyleDeclaration($icon_32_send);
				
				$control = substr($option, 4);
				$toolbar->prependButton('Separator', 'divider');
				$toolbar->prependButton('Send', 'j2xml_send', 'PLG_SYSTEM_J2XML_BUTTON_SEND', 'j2xml.'.$control.'.send', 'websites');
				$toolbar->prependButton('Standard2', 'j2xml_export', 'PLG_SYSTEM_J2XML_BUTTON_EXPORT', 'j2xml.'.$control.'.export');
			}
		}
		else
		{
			if (($option == 'com_content') && (!$view || $view == 'articles')
//				|| ($option == 'com_users') && (!$view || $view == 'users')
//				|| ($option == 'com_weblinks') && (!$view || $view == 'weblinks')
			) {		
				jimport('eshiol.core.standard12');
				$bar = JToolBar::getInstance('toolbar');
				$bar->appendButton('Standard12', 'download', PLG_SYSTEM_J2XML_BUTTON_EXPORT, 'j2xml.content.export', true);
			}
		}
		return true;
	}
}
?>