<?php/** * @version		2.5.88 controllers/content.json.php *  * @package		J2XML * @subpackage	com_j2xml * @since		2.5.85 *  * @author		Helios Ciancio <info@eshiol.it> * @link		http://www.eshiol.it * @copyright	Copyright (C) 2010-2012 Helios Ciancio. All Rights Reserved * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3 * J2XML is free software. This version may have been modified pursuant * to the GNU General Public License, and as distributed it includes or * is derivative of works licensed under the GNU General Public License or * other free or open source software licenses. */ // no direct accessdefined('_JEXEC') or die('Restricted access.');jimport('joomla.application.component.controller');jimport('eshiol.j2xml.exporter');jimport('eshiol.j2xml.sender');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_languages'.DS.'helpers'.DS.'jsonresponse.php');/** * Content controller class. */class J2XMLControllerContent extends JController{		private static $messages = array(
		'COM_J2XML_MSG_ARTICLE_IMPORTED',
		'COM_J2XML_MSG_ARTICLE_NOT_IMPORTED',
		'COM_J2XML_MSG_USER_IMPORTED',
		'COM_J2XML_MSG_USER_NOT_IMPORTED',
		'COM_J2XML_MSG_SECTION_IMPORTED',
		'COM_J2XML_MSG_SECTION_NOT_IMPORTED',
		'COM_J2XML_MSG_CATEGORY_IMPORTED',
		'COM_J2XML_MSG_CATEGORY_NOT_IMPORTED',
		'COM_J2XML_MSG_FOLDER_WAS_SUCCESSFULLY_CREATED',
		'COM_J2XML_MSG_ERROR_CREATING_FOLDER',
		'COM_J2XML_MSG_IMAGE_IMPORTED',
		'COM_J2XML_MSG_IMAGE_NOT_IMPORTED',
		'COM_J2XML_MSG_WEBLINK_IMPORTED',
		'COM_J2XML_MSG_WEBLINK_NOT_IMPORTED',
		'COM_J2XML_MSG_WEBLINKCAT_NOT_PRESENT',
		'COM_J2XML_MSG_CATEGORY_ID_PRESENT'		);			function __construct($default = array())	{		parent::__construct();	}	public function display($cachable = false, $urlparams = false)	{		JRequest::setVar('view', 'content');		parent::display($cachable, $urlparams);	}		function send()	{		if (!JSession::checkToken('request'))		{			// Check for a valid token. If invalid, send a 403 with the error message.			JError::raiseWarning(403, JText::_('JINVALID_TOKEN'));			echo new JJsonResponse();
			return;		}		$cid = JRequest::getVar('cid', array(0), 'post', 'array');		$sid = JRequest::getVar('j2xml_send_id', null, 'post', 'int');						if (!$sid)		{			JError::raiseWarning(1, JText::_('UNKNOWN_HOST'));
			echo new JJsonResponse();			return;				}								$params = JComponentHelper::getParams('com_j2xml');		$images = array();		$xml = J2XMLExporter::contents($cid,					$params->get('export_images', '1'),					1,					$params->get('export_users', '1'),					$images				);		foreach ($images as $image)			$xml .= $image;		J2XMLSender::send(				$xml,						$params->get('debug', 0), 				$params->get('export_gzip', '0'),				$sid			);		echo new JJsonResponse();			}}?>