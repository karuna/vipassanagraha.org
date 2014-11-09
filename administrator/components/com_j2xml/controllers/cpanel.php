<?php
/**
 * @version		3.0.95 controllers/cpanel.php
 *
 * @package		J2XML
 * @subpackage	com_j2xml
 * @since		File available since Release v1.5.3
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

class j2xmlControllerCPanel extends JControllerAbstract
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct($default);
	}

	public function display($cachable = false, $urlparams = false)
	{
		JRequest::setVar('view', 'cpanel');
		JRequest::setVar('layout', 'default');
		parent::display($cachable, $urlparams);
	}

	function import()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');
		
		$app = JFactory::getApplication('administrator');
		$msg='';
		$db = JFactory::getDBO();
		$date = JFactory::getDate();
		$now = $date->toSQL();
		$params = JComponentHelper::getParams('com_j2xml');

		libxml_use_internal_errors(true);
		
		//Retrieve file details from uploaded file, sent from upload form:
		$file = JRequest::getVar('file_upload', null, 'files', 'array');
		if ($file['name'])
		{
			$filename = $file['tmp_name'];
		}
			else
		{
			$params = JComponentHelper::getParams('com_j2xml');
			$root = $params->get('root');
			if (!$root)
				$root = $_SERVER['DOCUMENT_ROOT'];
			if (strrpos($root, DS)===strlen($root)-1)
				$root = substr($root, strlen($root)-1);
			$filename = $root.JRequest::getVar('remote_file', null);
			$filename = str_replace('/', DS, $filename);
		}
		if (!($data = implode(gzfile($filename))))
			$data = file_get_contents($filename);
		
		$xml = simplexml_load_string($data);
		if (!$xml)
		{
			$errors = libxml_get_errors();
			foreach ($errors as $error) {
				$msg = $error->code.' - '.$error->message.' at line '.$error->line;
				switch ($error->level) {
					default:
					case LIBXML_ERR_WARNING:
						$app->enqueueMessage($msg,'message');
						break;
					case LIBXML_ERR_ERROR:
						$app->enqueueMessage($msg,'notice');
						break;
					case LIBXML_ERR_FATAL:
						$app->enqueueMessage($msg,'error');
						break;
				}
			}
			libxml_clear_errors();
		}
		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('j2xml');
		$results = $dispatcher->trigger('onBeforeImport', array('com_j2xml.cpanel', &$xml));
		
		if(!isset($xml['version']))
   			$app->enqueueMessage(JText::sprintf('COM_J2XML_MSG_FILE_FORMAT_UNKNOWN'),'error');
		else 
		{
			jimport('eshiol.j2xml.importer');
			
			$xmlVersion = $xml['version'];
			$version = explode(".", $xmlVersion);
			$xmlVersionNumber = $version[0].substr('0'.$version[1], strlen($version[1])-1).substr('0'.$version[2], strlen($version[2])-1); 
			if ($xmlVersionNumber == 120500)
			{
				set_time_limit(120);
				$params = JComponentHelper::getParams('com_j2xml');
				J2XMLImporter::import($xml,$params);
			}
			else
				$app->enqueueMessage(JText::sprintf('COM_J2XML_MSG_FILE_FORMAT_NOT_SUPPORTED', $xmlVersion),'error');
		}	
		$this->setRedirect('index.php?option=com_j2xml');	
	}
}