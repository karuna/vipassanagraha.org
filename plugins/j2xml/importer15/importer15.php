<?php
/**
 * @version		3.0.7 plugins/j2xml/importer15/importer15.php
 * 
 * @package		J2XML
 * @subpackage	plg_j2xml_importer15
 * @since		2.5
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2013 Helios Ciancio. All Rights Reserved
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

class plgJ2XMLImporter15 extends JPlugin
{
	var $_params = null;
	/**
	 * CONSTRUCTOR
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function __construct(&$subject, $params)
	{
		parent::__construct($subject, $params);		

		$lang = JFactory::getLanguage();
		$lang->load('plg_j2xml_importer15', JPATH_SITE, null, false, false)
			|| $lang->load('plg_j2xml_importer15', JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load('plg_j2xml_importer15', JPATH_SITE, null, true)
			|| $lang->load('plg_j2xml_importer15', JPATH_ADMINISTRATOR, null, true);	
	}

	/**
	 * Method is called by 
	 *
	 * @access	public
	 */
	public function onBeforeImport($context, &$xml)
	{
		if (!class_exists('XSLTProcessor'))
		{
			JError::raiseWarning(1, JText::_('PLG_J2XML_IMPORTER15').' '.JText::_('PLG_J2XML_IMPORTER15_MSG_REQUIREMENTS_XSL'));
			return false;
		}
		
		$xslt = new XSLTProcessor();
		$xslfile = new DOMDocument();
		$xslfile->load(JPATH_ROOT.DS.'plugins'.DS.'j2xml'.DS.'importer15'.DS.'1506.xsl');
		$xslt->importStylesheet($xslfile);
		$xml = $xslt->transformToXML($xml);
		$xml = simplexml_load_string($xml);
		return true;
	}
}
