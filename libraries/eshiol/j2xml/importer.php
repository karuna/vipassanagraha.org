<?php
/**
 * @version		13.3.102 eshiol/j2xml/importer.php
 * 
 * @package		J2XML
 * @subpackage	lib_j2xml
 * @since		1.6.0
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2013 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

//jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class J2XMLImporter
{
	private static $messages = array(
		'articleok' => 'COM_J2XML_MSG_ARTICLE_IMPORTED',
		'articlenotok' => 'COM_J2XML_MSG_ARTICLE_NOT_IMPORTED',	
		'userok' => 'COM_J2XML_MSG_USER_IMPORTED',
		'usernotok' => 'COM_J2XML_MSG_USER_NOT_IMPORTED',
		'sectionok' => 'COM_J2XML_MSG_SECTION_IMPORTED',
		'sectionnotok' => 'COM_J2XML_MSG_SECTION_NOT_IMPORTED',
		'categoryok' => 'COM_J2XML_MSG_CATEGORY_IMPORTED',
		'categorynotok' => 'COM_J2XML_MSG_CATEGORY_NOT_IMPORTED',
		'folderok' => 'COM_J2XML_MSG_FOLDER_WAS_SUCCESSFULLY_CREATED',
		'foldernotok' => 'COM_J2XML_MSG_ERROR_CREATING_FOLDER',
		'imageok' => 'COM_J2XML_MSG_IMAGE_IMPORTED',
		'imagenotok' => 'COM_J2XML_MSG_IMAGE_NOT_IMPORTED',						
		'weblinkok' => 'COM_J2XML_MSG_WEBLINK_IMPORTED',
		'weblinknotok' => 'COM_J2XML_MSG_WEBLINK_NOT_IMPORTED',						
		'weblinkcatnotok' => 'COM_J2XML_MSG_WEBLINKCAT_NOT_PRESENT',
		'structurenotok' => 'COM_J2XML_MSG_CATEGORY_ID_PRESENT',
	);				
	
	private static $codes = array(
		'articleok' => 0,
		'articlenotok' => 1,
		'userok' => 2,
		'usernotok' => 3,
		'sectionok' => 4,
		'sectionnotok' => 5,
		'categoryok' => 6,
		'categorynotok' => 7,
		'folderok' => 8,
		'foldernotok' => 9,
		'imageok' => 10,
		'imagenotok' => 11,
		'weblinkok' => 12,
		'weblinknotok' => 13,
		'weblinkcatnotok' => 14,
		'structureok' => 15,
		'description'=>999
	);	
		
	static function import($xml, $params)
	{
		$msg = array();
		$db = JFactory::getDBO();
		$nullDate = $db->getNullDate();
		$user = JFactory::getUser();
		$user_id = $user->get('id');
		$now = (version_compare(JPlatform::getShortVersion(), '12.2.0', 'ge')) ? JFactory::getDate()->format("%Y-%m-%d-%H-%M-%S") : JFactory::getDate()->toFormat("%Y-%m-%d-%H-%M-%S");
		$option = JRequest::getCmd('option');
		
		$import_content = $params->get('import_content', '2');
		$import_users = $params->get('import_users', '1');
		$import_categories = $params->get('import_categories', '1');
		$import_images = $params->get('import_images', '1');
		
		$keep_access = $params->get('keep_access', '0');
		$keep_state = $params->get('keep_state', '2');
		$keep_author = $params->get('keep_author', '1');
		$keep_category = $params->get('keep_category', '1');
		$keep_attribs = $params->get('keep_attribs', '1');
		$keep_metadata = $params->get('keep_metadata', '1');
		$keep_frontpage = $params->get('keep_frontpage', '1');
		$keep_rating = $params->get('keep_rating', '1');
		$keep_id = $params->get('keep_id', '0');
		
		$keep_user_id = $params->get('keep_user_id', '0');
		$keep_user_attribs = $params->get('keep_user_attribs', '1');
		
		$xmlrpc = $params->get('xmlrpc');
		
		$query = "SELECT id FROM #__categories"
			. " WHERE path = 'uncategorised'"
			. " AND extension = 'com_content'"
			;
		$db->setQuery($query);
		$uncategorised = $db->loadResult();

		$users_id = array();
		$query = "SELECT * FROM #__users WHERE id = 42";
		$db->setQuery($query);
		$user = $db->loadObject();
		if ($user)
			$users_id['admin'] = 42;
		else
			$users_id['admin'] = 62;
		$users_id[0] = 0;
				
		if ($import_users)
		{
			foreach($xml->xpath("user") as $record)
			{
				$data = array();
				foreach($record->children() as $key => $value)
				{
					if (count($value->children()) === 0)
						$data[trim($key)] = trim($value);
					else 
						foreach ($value->children() as $v)
							$data[trim($key)][] = trim($v);
				}
				$alias = $data['username'];
				$id = $data['id'];
				
				$query = 'SELECT id, name'
					. ' FROM #__users'
					. ' WHERE'. (($keep_user_id == 1)
						? ' id = '.$id
						: ' username = '.$db->Quote($alias)
						)
					;

				$db->setQuery($query);
				$user = $db->loadObject();
				if (!$user || ($import_users == 2))
				{
					$table = JTable::getInstance('user');
					if ($import_users == 2)
					{
						$data['id'] = $user->id;
						$table->load($data['id']);
					}
					else
					{
						$data['id'] = null;
					}

					// Add the groups to the user data.
					$groups = array();
					$query = $db->getQuery(true);
					$query->select('id, title');
					$query->from('#__usergroups');
					if ($data['group'])
						$groups[] = $db->Quote($data['group']);
					foreach ($data['grouplist'] as $v)
						$groups[] = $db->Quote($v);
					$query->where('title in ('.implode(',', $groups).')');
					$db->setQuery($query);
					$data['groups'] = $db->loadAssocList('title','id');

					if (count($data['groups']) == 0)
						$data['groups']['Public'] = 1;
						
					if (!$keep_user_attribs)
						$data['attribs'] = null;
					else
					{
						$attribs = new JRegistry;
						$attribs->loadString($data['params'], 'INI');
						$attribs->set('timezone', '');
						$data['params'] = $attribs->toString();
					}
					
					if ($table->save($data))
					{
						if (!$user && ($keep_user_id == 1))
						{
							$query = "UPDATE #__users SET id = {$id} WHERE id = {$table->id}";
							$db->setQuery($query);
							$db->query();
							$query = "UPDATE #__user_usergroup_map SET user_id={$id} WHERE user_id={$table->id}";
							$db->setQuery($query);
							$db->query();
							$table->id = $id;
						}
						$users_id[$alias] = $table->id;
						$users_title[$alias] = $table->name;
						self::trace(true, $table->name, 'user', $msg, $xmlrpc);
					}
					else
					{
						self::trace(false, $data['name'], 'user', $msg, $xmlrpc);
						self::trace(false, $table->getError(), 'description', $msg, $xmlrpc);
					}
				}
				elseif ($user)
				{
					$users_id[$alias] = $user->id;
					$users_title[$alias] = $user->name;
				}
			}
		}
		
		if ($import_categories)
		{
			foreach($xml->xpath("category") as $record)
			{
				$data = array();
				foreach($record->children() as $key => $value)
					$data[trim($key)] = trim($value);
				$alias = $data['alias'] = JApplication::stringURLSafe($data['alias']);
				$id = $data['id'];
				$data['title'] = htmlspecialchars_decode($data['title']);				
				$data['description'] = htmlspecialchars_decode($data['description']);
				
				$path = $data['path'];
				
				$i = strrpos($path, '/');
				if ($i === false) {
					$section_alias = '';
					$data['section'] = 1;
				} else {
					$section_alias = substr($path, 0, $i);
					if (!isset($categories_id[$section_alias])) {
						$query = 'SELECT id, title'
							. ' FROM #__categories'
							. ' WHERE extension = '. $db->Quote($data['extension'])
							. ' AND path = '. $db->Quote($section_alias)
							;
						$db->setQuery($query);
						//list($section_id, $section_title) = $db->loadResultArray();
						$section_id = $db->loadColumn(0);
						$section_title = $db->loadColumn(1);
						$categories_id[$section_alias] = $section_id;
						$categories_title[$section_alias] = $section_title;
					}
					$data['section'] = $categories_id[$section_alias];
				}
				if (($keep_id == 1) && ($id > 0))
					$query = 'SELECT id, title'
					. ' FROM #__categories'
					. ' WHERE extension = '. $db->Quote('com_content')
					. ' AND id = '.$id
					;
				else
					$query = 'SELECT id, title'
						. ' FROM #__categories'
						. ' WHERE extension = '. $db->Quote('com_content')
						. ' AND path = '. $db->Quote($path)
						;
				
				$db->setQuery($query);
				$category = $db->loadObject();
				if (!$category || ($import_categories == 2))
				{
					$data['checked_out'] = 0;
					$data['checked_out_time'] = $nullDate;
					$table = JTable::getInstance('category');

					if (!$category) // new category
					{
						if ($keep_id)
						{
							$query = 'SELECT id, title, extension'
								. ' FROM #__categories'
								. ' WHERE id = '.$id
								;
							$db->setQuery($query);
							$category = $db->loadObject();
						}
						$data['id'] = null;
						if ($keep_access > 0)
							$data['access'] = $keep_access;
						if ($keep_state < 2)
							// Force the state
							$data['published'] = $keep_state;
						//else keep the original state
						
						if (!$keep_attribs)
							$data['params'] = '{"category_layout":"","image":""}';
						
						$table->setLocation($data['section'], 'last-child');
					}
					else // category already exists
					{
						$data['id'] = $category->id;
						$table->load($data['id']);
						
						if ($keep_access > 0)
							// don't modify the access level
							$data['access'] = null;
						
						if ($keep_state != 0)  
							// don't modify the state
							$data['published'] = null;
						//else keep the original state		

						if (!$keep_attribs)
							$data['params'] = null;
							
						if (!$keep_author) 
						{
							$data['created'] = null;
							$data['created_by'] = null; 
							$data['created_by_alias'] = null; 				
							$data['modified'] = $now; 
							$data['modified_by'] = $user_id; 
							$data['version'] = $table->version + 1; 
						}	
						else // save default values
						{
							$data['created'] = $now;
							$data['created_by'] = $user_id; 
							$data['created_by_alias'] = null; 				
							$data['modified'] = $nullDate; 
							$data['modified_by'] = null; 
							$data['version'] = 1; 
						}
					}
					$data['parent_id'] = $data['section'];

					if ($table->save($data))
					{
						if (!$category && ($keep_id == 1))
						{
							$query = "UPDATE #__categories SET `id` = {$id} WHERE `id` = {$table->id}";
							$db->setQuery($query);
							$db->query();
							$table->id = $id;
							$query = "UPDATE #__assets SET `name` = 'com_content.category.{$id}' WHERE `id` = {$table->asset_id}";
							$db->setQuery($query);
							$db->query();
						}
						// Rebuild the tree path.
						$table->rebuildPath();
						$categories_id[$path] = $table->id;
						if ($section_alias)
							$table->title = $categories_title[$section_alias].'/'.$table->title; 
						$categories_title[$path] = $table->title;
						if ($keep_id && ($id != $table->id))
							self::trace(false, $table->title, 'structure', $msg, $xmlrpc);
						else
							self::trace(true, $table->title, 'category', $msg, $xmlrpc);
					}
					else
					{
						self::trace(false, $data['title'], 'category', $msg, $xmlrpc);
					}
				}
				elseif ($category)
				{
					$categories_id[$section_alias.'/'.$alias] = $category->id;
					$categories_title[$section_alias.'/'.$alias] = $category->title;
				}
			}
		}
		
		if ($keep_frontpage)
		{
			$query = 'SELECT max(ordering)'
				. ' FROM #__content_frontpage'
				;
			$db->setQuery($query);
			$frontpage = (int)$db->loadResult();			
		}
		
		foreach($xml->xpath("content") as $record)
		{
			$data = array();
			foreach($record->children() as $key => $value)
				$data[trim($key)] = trim($value);
			$alias = $data['alias'] = JApplication::stringURLSafe($data['alias']);
			$id = $data['id'];
			$catid = $data['catid'];
			$data['title'] = htmlspecialchars_decode($data['title']);				
			$data['introtext'] = htmlspecialchars_decode($data['introtext']);
			$data['fulltext'] = htmlspecialchars_decode($data['fulltext']);
			
			$table = JTable::getInstance('content');
			
			if ($keep_id == 1)
				$query = 'SELECT id, title'
					. ' FROM #__content'
					. ' WHERE id = '.$id
					;
			else
				$query = 'SELECT #__content.id, #__content.title'
					. ' FROM #__content LEFT JOIN #__categories'
					. ' ON #__content.catid = #__categories.id'
					. ' WHERE #__categories.path = '. $db->Quote($data['catid'])
					. ' AND #__content.alias = '. $db->Quote($alias)
					;
				
			$db->setQuery($query);
			$content = $db->loadObject();
			
			if (!$content || $import_content == 2)			
			{
				$data['checked_out'] = 0;
				$data['checked_out_time'] = $nullDate;
				
				if (!$content)
				{ // new article
					$isNew = true; 
					$data['id'] = null;
					if ($keep_access > 0)
						$data['access'] = $keep_access;
					if ($keep_state < 2)
						// Force the state
						$data['state'] = $keep_state;
					
					if (!$keep_attribs)
						$data['attribs'] = '{"category_layout":"","image":""}';
					
					if (!$keep_metadata)
					{
						$data['metadata'] = '{"author":"","robots":""}';
						$data['metakey'] = '';
						$data['metadesc'] = '';
					}
				}
				else // article already exists
				{
					$isNew = false; 
					$data['id'] = $content->id;

					if ($keep_access > 0)
						// don't modify the access level
						$data['access'] = null;
					
					if ($keep_state != 0)  
						// don't modify the state
						$data['state'] = null;
					//else keep the original state		

					if (!$keep_attribs)
						$data['attribs'] = null;
					
					if (!$keep_metadata)
					{
						$data['metadata'] = null;
						$data['metakey'] = null;
						$data['metadesc'] = null;
					}
				}
				
				// keep category
				if ($keep_category == 1)
				{
					// keep category
					if (!isset($data['sectionid']) && !isset($data['catid']))
					{
						// uncategorised
						$data['catid'] = $uncategorised;
					}
					else if (isset($categories_id[$data['catid']]))
					{
						// category already loaded
						$data['catid'] = $categories_id[$data['catid']];
					}
					else
					{
						// load category
						$query = 'SELECT id'
							. ' FROM #__categories'
							. ' WHERE path = '. $db->Quote($data['catid'])
							;
						$db->setQuery($query);
						$category_id = (int)$db->loadResult();
						if ($category_id > 0)
						{
							$categories_id[$data['catid']] = $category_id;
							$data['catid'] = $category_id;
						}
						else
							$data['catid'] = $uncategorised;
					}
				} 
				elseif ($keep_category == 2)
				{
					$data['sectionid'] = 0;
					$data['catid'] = $params->get('category');
				}
				else if ($content)
				{
					// don't keep category and article already exists
					$data['sectionid'] = null; 
					$data['catid'] =null; 				
				}
				else
				{
					// don't keep category & article is new & Joomla!1.6
					// set uncategorised
					$data['catid'] = $uncategorised;
				}
							
				if ($keep_author)
				{
					if (isset($users_id[$data['created_by']]))
						$data['created_by'] = $users_id[$data['created_by']];
					else
					{
						$query = 'SELECT id'
							. ' FROM #__users'
							. ' WHERE username = '. $db->Quote($data['created_by'])
							;
						$db->setQuery($query);
						$userid = (int)$db->loadResult();
						if ($userid > 0)
						{
							$users_id[$data['created_by']] = $userid;
							$data['created_by'] = $userid;
						}
						else
							$data['created_by'] = $user_id;
					}
					if (isset($data['modified_by']))
					{
						if (isset($users_id[$data['modified_by']]))
							$data['modified_by'] = $users_id[$data['modified_by']];
						else
						{
							$query = 'SELECT id'
								. ' FROM #__users'
								. ' WHERE username = '. $db->Quote($data['modified_by'])
								;
							$db->setQuery($query);
							$userid = (int)$db->loadResult();
							if ($userid > 0)
							{
								$users_id[$data['modified_by']] = $userid;
								$data['modified_by'] = $user;
							}
							else
								$data['modified_by'] = $user_id;
						}
					}
				}
				else if ($content)
				{
					$data['created'] = null;
					$data['created_by'] = null; 
					$data['created_by_alias'] = null; 				
					$data['modified'] = null; 
					$data['modified_by'] = null; 
					$data['version'] = null; 
				}
				else
				{
					$data['created'] = $now;
					$data['created_by'] = $user_id; 
					$data['created_by_alias'] = null; 				
					$data['modified'] = $nullDate; 
					$data['modified_by'] = null; 
					$data['version'] = 1; 
				}

				if (!$keep_frontpage)
					$data['featured'] = 0;

				// Initialise variables;
				$dispatcher = JDispatcher::getInstance();
				
				// Include the content plugins for the on save events.
				JPluginHelper::importPlugin('content');
				$table->bind($data);
								
				// Trigger the onContentBeforeSave event.
				$result = $dispatcher->trigger('onContentBeforeSave', array($option.'.article', &$table, $isNew));

				if (!in_array(false, $result, true))
				{
					if ($table->store())
					{
						if (!$content && ($keep_id == 1))
						{
							$query = "UPDATE #__content SET `id` = {$id} WHERE `id` = {$table->id}";
							$db->setQuery($query);
							$db->query();
							$table->id = $id;
							$query = "UPDATE #__assets SET `name` = 'com_content.article.{$id}' WHERE `id` = {$table->asset_id}";
							$db->setQuery($query);
							$db->query();
						}
						
						if ($keep_frontpage)
						{
							if ($data['featured'] == 0)
								$query = "DELETE FROM #__content_frontpage WHERE content_id = ".$table->id;
							else if($keep_id)
								$query = 
									  ' INSERT IGNORE INTO `#__content_frontpage`'
									. ' SET content_id = '.$table->id.','
									. '     ordering = '.$data['featured'];
							else
							{
								$frontpage++;
								$query = 
									  ' INSERT IGNORE INTO `#__content_frontpage`'
									. ' SET content_id = '.$table->id.','
									. '     ordering = '.$frontpage;
							}
							$db->setQuery($query);
							$db->query();
						}
	
						if ($keep_rating)
						{
							if (isset($data['rating_count']))
								if ($data['rating_count'] > 0)
								{
									$rating = new stdClass();
									$rating->content_id = $table->id;
									$rating->rating_count = $data['rating_count'];
									$rating->rating_sum = $data['rating_sum'];
									$rating->lastip = $_SERVER['REMOTE_ADDR'];
									if (!$db->insertObject('#__content_rating', $rating))
										$db->updateObject('#__content_rating', $rating, 'content_id');
								}
								else
								{
									$query = "DELETE FROM `#__content_rating` WHERE `content_id`=".$table->id;
									$db->setQuery($query);
									$db->query();
								}
						}
						self::trace(true, $table->title, 'article', $msg, $xmlrpc);
						// Trigger the onContentAfterSave event.
						$dispatcher->trigger('onContentAfterSave', array($option.'.article', &$table, $isNew));
					}
					else
					{
						self::trace(false, $data['title'].' (id='.$id.')', 'article', $msg, $xmlrpc);
						if ($data['catid'])
							self::trace(false, $table->getError(), 'description', $msg, $xmlrpc);
						else
							self::trace(false, 'Category '.$catid.' not found.', 'description', $msg, $xmlrpc);
					}
				}
			}
		}
		
		/*
		 * Import Weblinks
		*/
		foreach($xml->xpath("weblink") as $record)
		{
			$attributes = $record->attributes();
			$data = array();
			foreach($record->children() as $key => $value)
				$data[trim($key)] = trim($value);
			$alias = $data['alias'];
			$data['title'] = htmlspecialchars_decode($data['title']);
			$data['description'] = htmlspecialchars_decode($data['description']);
				
			$query = 'SELECT id, title'
				. ' FROM #__weblinks'
				. ' WHERE alias = '. $db->Quote($alias)
				;
			$db->setQuery($query);
			$weblink = $db->loadObject();
								
			if (!$weblink || $import_weblinks)
			{
				$data['checked_out'] = 0;
				$data['checked_out_time'] = $nullDate;
		
				$table = JTable::getInstance('weblink', 'WeblinksTable');
		
				if (!$weblink)
				{ // new weblink
					$data['id'] = null;
				}
				else // weblink already exists
				{
					$data['id'] = $weblink->id;
					$table->load($data['id']);
				}
									
				if (isset($categories_id['com_weblinks/'.$data['catid']]))
				{
					// category already loaded
					$data['catid'] = $categories_id['com_weblinks/'.$data['catid']];
				}
				else
				{
					// load category
					$query = 'SELECT id'
						. ' FROM #__categories'
						. ' WHERE alias = '. $db->Quote($data['catid'])
						. ' AND extension = '. $db->Quote('com_weblinks')
						. ' AND level = 1'
						;
					$db->setQuery($query);
					$category_id = (int)$db->loadResult();
					if ($category_id > 0)
					{
						$categories_id['com_weblinks/'.$data['catid']] = $category_id;
						$data['catid'] = $category_id;
					}
					else
					{
						self::trace(false, $data['title'], 'weblinkcat', $msg, $xmlrpc);
						continue;
					}
				}
				// Trigger the onContentBeforeSave event.
				$table->bind($data);
				if ($table->store())
				{
					self::trace(true, $table->title, 'weblink', $msg, $xmlrpc);
					// Trigger the onContentAfterSave event.
				}
				else
				{
					self::trace(false, $data['title'], 'weblink', $msg, $xmlrpc);
					self::trace(false, $table->getError(), 'description', $msg, $xmlrpc);
				}
			}
		}
						
		if ($import_images)
		{
			jimport('joomla.filesystem.folder');
			foreach($xml->img as $image)
			{ 
				$src = JPATH_SITE.DS.str_replace('/', DS, htmlspecialchars_decode($image['src'])); 
				$data = $image;
				if (!file_exists($src) || ($import_images == 2))
				{
					// many thx to Stefanos Tzigiannis
					$folder = dirname($src);
					if (!JFolder::exists($folder)) {
						if (JFolder::create($folder))
							self::trace(true, $folder, 'folder', $msg, $xmlrpc);
						else
						{
							self::trace(false, $folder, 'folder', $msg, $xmlrpc);
							break;
						}
					}
 					if (JFile::write($src, base64_decode($data)))
					    self::trace(true, $image['src'], 'image', $msg, $xmlrpc);
					else
						self::trace(false, $image['src'], 'image', $msg, $xmlrpc);
				}
			}
		} 
		if ($xmlrpc === true)
			return new xmlrpcval($msg, "array");
	}	
		
	private static function trace($ok, $title, $type, &$msg, $xmlrpc = false)
	{
		// get app based on cli or web
		$app = (PHP_SAPI != 'cli') ? JFactory::getApplication('administrator') : JCli::getInstance();
		
		if ($xmlrpc === true)
		{
			if ($type == 'description')
				$msg[] = new xmlrpcval(
					array(
						"code" => new xmlrpcval(self::$codes[$type], 'int'),
						"string" => new xmlrpcval($title, 'string')
					), "struct"
			  	);
			else
				$msg[] = new xmlrpcval(
					array(
						"code" => new xmlrpcval(self::$codes[$type.($ok?'ok':'notok')], 'int'),
						"string" => new xmlrpcval($title, 'string')
					), "struct"
			  	);
		}
		else
		{
			if ($type == 'description')
				$app->enqueueMessage(
					$title,($ok)?'message':'notice'
					);
			else
				$app->enqueueMessage(
					JText::sprintf(self::$messages[$type.($ok?'ok':'notok')], $title), 
					($ok)?'message':'notice'
					);
		}
	}
}
?>
