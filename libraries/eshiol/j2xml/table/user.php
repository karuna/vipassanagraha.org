<?php
/**
 * @version		12.5.86 tables/category.php
 * 
 * @package		J2XML
 * @subpackage	com_j2xml
 * @since		1.5.3beta4.39
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
defined('_JEXEC') or die('Restricted access');

/**
* User Table class
* @since 		1.5.3beta4.39
*/
class eshTableUser extends eshTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.5.3beta4.39
	 */
	function __construct(& $db) {
		parent::__construct('#__users', 'id', $db);
	}

	/**
	 * Export item list to xml
	 *
	 * @access public
	 */
	function toXML($mapKeysToText = false)
	{
		$xml = ''; 
		
		// Initialise variables.
		$xml = array();
		
		// Open root node.
		$xml[] = '<user>';
		
		$xml[] = parent::_serialize(null, 
			array(
				'group'=>'SELECT #__usergroups.title FROM #__usergroups, #__user_usergroup_map WHERE #__usergroups.id = #__user_usergroup_map.group_id AND #__user_usergroup_map.user_id = '.(int)$this->id
				)
			); // $excluded,$aliases,$jsons

		// Close root node.
		$xml[] = '</user>';
						
		// Return the XML array imploded over new lines.
		return implode("\n", $xml);
	}
}
