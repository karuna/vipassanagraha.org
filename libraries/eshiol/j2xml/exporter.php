<?php
/**
 * @version		13.3.105 exporter.php
 * 
 * @package		J2XML
 * @subpackage	lib_j2xml
 * @since		1.5.2.14
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010-2013 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

//Import filesystem libraries.
jimport('joomla.filesystem.file');

jimport('eshiol.j2xml.table');
jimport('eshiol.j2xml.version');
JTable::addIncludePath(dirname(__FILE__) . '/table');

class J2XMLExporter
{
	static $image_match_string = '/<img.*?src="([^"]*)".*?[^>]*>/s';
	// images/stories is path of the images of the sections and categories hard coded in the file \libraries\joomla\html\html\list.php at the line 52
	static $image_path = "images";
	
	/*
	 * Export content articles, images, section and categories
	 * @return 		xml string
	 * @since		1.5.2.14
	 */
	static function contents($ids, $export_images, $export_categories, $export_users, &$images)
	{		
		$xml = '';		

		$admin = 42;
		
		$category = JTable::getInstance('category', 'eshTable');
		$categories = array();

		$user = JTable::getInstance('user', 'eshTable');
		$users = array();

		foreach($ids as $id)
		{
			$item = JTable::getInstance('content', 'eshTable');
			$item->load($id);

			if ($export_users)
			{
				if (($item->created_by != $admin) 
				&& (!array_key_exists($item->created_by, $users)))
				{
					$user->load($item->created_by);
					$users[$item->created_by] = $user->toXML();
				}
				if (($item->modified_by != $admin) 
					&& ($item->modified_by != 0) 
					&& (!array_key_exists($item->modified_by, $users)))
				{
					$user->load($item->modified_by);
					$users[$item->modified_by] = $user->toXML();
				}
			}	

			if ($export_categories && ($item->catid > 0))
			{
				self::_category($item->catid, $export_images, $images, $categories);
			}	

			if ($export_images)
			{
				$text = $item->introtext.$item->fulltext;
				$image = preg_match_all(self::$image_match_string,$text,$matches,PREG_PATTERN_ORDER);
				if (count($matches[1]) > 0)
				{
					for ($i = 0; $i < count($matches[1]); $i++)
					{
						$image = $matches[1][$i];						
						$file_path = JPATH_SITE.DS.str_replace("/", DS, $matches[1][$i]);
						if (!array_key_exists($image, $images) && JFile::exists($file_path))
							$images[$image] = "\t\t<img src=\"".htmlentities($image, ENT_QUOTES, "UTF-8")."\">"
								."\t\t\t".base64_encode(file_get_contents($file_path))
								."\t\t</img>\n";
					}
				}
			}
			$xml .= $item->toXML();
		}
		foreach($categories as $category)
			$xml .= $category;
		foreach($users as $user)
			$xml .= $user;
		return $xml;
	}

	/*
	 * Export users
	 * @return 		xml string
	 * @since		1.5.3beta4.39
	 */
	static function users($ids)
	{		
		$xml = '';
		
		foreach($ids as $id)
		{
			$item = JTable::getInstance('user', 'eshTable');
			$item->load($id);

			$xml .= $item->toXML();
		}
		
		return $xml;
	}

	/*
	 * Export categories
	 * @return 		xml string
	 * @since		1.5.3beta5.43
	 */
	static function categories($ids, $export_images, $export_sections, $export_users, &$images)
	{		
		$xml = '';
		$sections = array();

		foreach($ids as $id)
		{
			$item = JTable::getInstance('category', 'eshTable');
			$item->load($id);
			$xml .= $item->toXML();

			/* export section */
			if ($export_sections && ($item->section > 0))
			{
				if (!array_key_exists($item->section, $sections))
				{
					$section = JTable::getInstance('section', 'eshTable');
					$section->load($item->section);
					$sections[$item->section] = $section->toXML();
					if ($export_images)
					{
						if ($section->image)
						{
							$image = self::$image_path."/".$section->image;
							$file_path = JPATH_SITE.DS.str_replace("/", DS, $image);
							if (!array_key_exists($image, $images) && JFile::exists($file_path))
								$images[$image] = "\t\t<img src=\"".$image."\">"
									."\t\t\t".base64_encode(file_get_contents($file_path))
									."\t\t</img>\n";
						}
						$text = html_entity_decode($section->description);
						$image = preg_match_all(self::$image_match_string,$text,$matches,PREG_PATTERN_ORDER);
						if (count($matches[1]) > 0)
						{
							for ($i = 0; $i < count($matches[1]); $i++)
							{
								$image = $matches[1][$i];						
								$file_path = JPATH_SITE.DS.str_replace("/", DS, $matches[1][$i]);
								if (!array_key_exists($image, $images) && JFile::exists($file_path))
									$images[$image] = "\t\t<img src=\"".$image."\">"
										."\t\t\t".base64_encode(file_get_contents($file_path))
										."\t\t</img>\n";
							}
						}
					}					
				}
			}	
			if ($export_images)
			{
				if ($item->image)
				{
					$image = self::$image_path."/".$item->image;
					$file_path = JPATH_SITE.DS.str_replace("/", DS, $image);
					if (!array_key_exists($image, $images) && JFile::exists($file_path))
						$images[$image] = "\t\t<img src=\"".$image."\">"
							."\t\t\t".base64_encode(file_get_contents($file_path))
							."\t\t</img>\n";
				}
				$text = html_entity_decode($item->description);
				$image = preg_match_all(self::$image_match_string,$text,$matches,PREG_PATTERN_ORDER);
				if (count($matches[1]) > 0)
				{
					for ($i = 0; $i < count($matches[1]); $i++)
					{
						$image = $matches[1][$i];						
						$file_path = JPATH_SITE.DS.str_replace("/", DS, $matches[1][$i]);
						if (!array_key_exists($image, $images) && JFile::exists($file_path))
							$images[$image] = "\t\t<img src=\"".$image."\">"
								."\t\t\t".base64_encode(file_get_contents($file_path))
								."\t\t</img>\n";
					}
				}
			}
			/* export contents */
			$db = JFactory::getDBO();
			$query = "SELECT `id` FROM `#__content` WHERE `catid` = $id";
			$db->setQuery($query);
			$content_ids = $db->loadResultArray();
			if (isset($content_ids))
				$xml .= 
					self::contents(
						$content_ids, 
						$export_images, 
						0, 
						$export_users,
						$images
					);
		}
		
		return $xml;
	}

	static function export($xml, $debug, $export_gzip)
	{
		if ($debug > 0)
		{
			$app = JFactory::getApplication();
			$data = ob_get_contents();
			if ($data)
			{	
				$app->enqueueMessage(JText::_('COM_J2XML_MSG_ERROR_EXPORT'), 'error');
					$app->enqueueMessage($data, 'error');
				return false;
			}
		}
		ob_clean();
			
		$version = explode(".", J2XMLVersion::$DOCVERSION);
		$xmlVersionNumber = $version[0].$version[1].substr('0'.$version[2], strlen($version[2])-1);
		
		$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$data .= J2XMLVersion::$DOCTYPE."\n";
		$data .= "<j2xml version=\"".J2XMLVersion::$DOCVERSION."\">\n";
		$data .= $xml; 
		$data .= "</j2xml>";
		// modify the MIME type
		$document = JFactory::getDocument();
		if ($export_gzip)
		{
			$document->setMimeEncoding('application/gzip-compressed', true);
			JResponse::setHeader('Content-disposition', 'attachment; filename="j2xml'.$xmlVersionNumber.date('YmdHis').'.gz"', true);
			$data = gzencode($data, 9);
		}
		else 
		{
			$document->setMimeEncoding('application/xml', true);
			JResponse::setHeader('Content-disposition', 'attachment; filename="j2xml'.$xmlVersionNumber.date('YmdHis').'.xml"', true);
		}
		echo $data;
		return true;
	}

	/*
	 * Export category
	 * @return 		xml string
	 * @since		1.6.1.60
	 */
	private static function _category($id, $export_images, &$images, &$categories)
	{
		if (!array_key_exists($id, $categories))
		{
			$category = JTable::getInstance('category', 'eshTable');
			$category->load($id);
			
			if ($category->parent_id > 1)
				self::_category($category->parent_id, $export_images, $images, $categories);
			
			$categories[$id] = $category->toXML();
			$text = html_entity_decode($category->description);
			$image = preg_match_all(self::$image_match_string,$text,$matches,PREG_PATTERN_ORDER);
			if (count($matches[1]) > 0)
			{
				for ($i = 0; $i < count($matches[1]); $i++)
				{
					$image = $matches[1][$i];						
					$file_path = JPATH_SITE.DS.str_replace("/", DS, $matches[1][$i]);
					if (!array_key_exists($image, $images) && JFile::exists($file_path))
						$images[$image] = "\t\t<img src=\"".$image."\">"
							."\t\t\t".base64_encode(file_get_contents($file_path))
							."\t\t</img>\n";
				}
			}
			//TODO:export content images
/*
			if (($export_images) && ($category->image))
			{
				$image = self::$image_path."/".$category->image;
				$file_path = JPATH_SITE.DS.str_replace("/", DS, $image);
				if (!array_key_exists($image, $images) && JFile::exists($file_path))
					$images[$image] = "\t\t<img src=\"".$image."\">"
						."\t\t\t".base64_encode(file_get_contents($file_path))
						."\t\t</img>\n";
			}
*/			
		}
	}

	/*
	 * Export weblinks
	 * @return 		xml string
	 * @since		1.5.3beta3.38
	 */
	static function weblinks($ids, $export_images, $export_categories, $export_users, &$images)
	{		
		$xml = '';		

		$admin = 42;
		
		$category = JTable::getInstance('category', 'eshTable');
		$categories = array();

		$user = JTable::getInstance('user', 'eshTable');
		$users = array();
		
		foreach($ids as $id)
		{
			$item = JTable::getInstance('weblink', 'eshTable');
			$item->load($id);

			if ($export_users)
			{
				if (($item->created_by != $admin) 
				&& (!array_key_exists($item->created_by, $users)))
				{
					$user->load($item->created_by);
					$users[$item->created_by] = $user->toXML();
				}
				if (($item->modified_by != $admin) 
					&& ($item->modified_by != 0) 
					&& (!array_key_exists($item->modified_by, $users)))
				{
					$user->load($item->modified_by);
					$users[$item->modified_by] = $user->toXML();
				}
			}	
			
			if ($export_categories && ($item->catid > 0))
			{
				self::_category($item->catid, $export_images, $images, $categories);
			}	
			if ($export_images)
			{
				$text = $item->description;
				$image = preg_match_all(self::$image_match_string,$text,$matches,PREG_PATTERN_ORDER);
				if (count($matches[1]) > 0)
				{
					for ($i = 0; $i < count($matches[1]); $i++)
					{
						$image = $matches[1][$i];						
						$file_path = JPATH_SITE.DS.str_replace("/", DS, $matches[1][$i]);
						if (!array_key_exists($image, $images) && JFile::exists($file_path))
							$images[$image] = "\t\t<img src=\"".htmlentities($image, ENT_QUOTES, "UTF-8")."\">"
								."\t\t\t".base64_encode(file_get_contents($file_path))
								."\t\t</img>\n";
					}
				}
			}
			$xml .= $item->toXML();
		}
		foreach($categories as $category)
			$xml .= $category;
		foreach($users as $user)
			$xml .= $user;
		return $xml;
	}
}