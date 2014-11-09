<?php
/**
 * @version		13.2.96 tables/table.php
 * 
 * @package		J2XML
 * @subpackage	com_j2xml
 * @since		1.5.3.39
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

class eshTable extends JTable
{
	/**
	 * Name of the database table to model.
	 *
	 * @var		string
	 * @since	1.0
	 */
	public $_tbl	= '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var		string
	 * @since	1.0
	 */
	public $_tbl_key = '';

	/**
	 * JDatabase connector object.
	 *
	 * @var		object
	 * @since	1.0
	 */
	public $_db;
	
	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param	string Name of the table to model.
	 * @param	string Name of the primary key field in the table.
	 * @param	object JDatabase connector object.
	 * @since	1.0
	 */
	function __construct($table, $key, &$db)
	{
		parent::__construct($table, $key, $db);
	}
	
	/**
	 * Export item list to xml
	 *
	 * @access public
	 * @param boolean Map foreign keys to text values
	 */
	protected function _serialize($excluded=array(),$aliases=array(),$jsons=array())
	{
		// Initialise variables.
		$xml = array();

		foreach (get_object_vars($this) as $k => $v)
		{
			// If the value is null or non-scalar, or the field is internal ignore it.
			if (!is_scalar($v) || ($k[0] == '_'))
				continue;
			if ($excluded && in_array($k, $excluded))
				continue;
			if ($aliases && array_key_exists($k, $aliases))
				continue;
			else if ($jsons && in_array($k, $jsons))
				$v = json_encode($v);
			$xml[] = $this->_setValue($k, $v);
		}
		foreach($aliases as $k => $query)
		{
			$this->_db->setQuery($query);
			$v = $this->_db->loadResultArray();
			if (count($v) == 1)
				$xml[] = $this->_setValue($k, $v[0]);
			else 
			{
				$xml[] = '<'.$k.'list>';
				foreach ($v as $val)
					$xml[] = $this->_setValue($k, $val);
				$xml[] = '</'.$k.'list>';
			}
		}		
		// Return the XML array imploded over new lines.
		return implode("\n", $xml);
	}
	
	function _setValue($k, $v)
	{
		// Open root node.
		$xml = '<'.$k.'>';
		// Set value.
		if (is_numeric($v))
//			$xml .= '<![CDATA['.$v.']]>';
			$xml .= $v;
		else if ($v != '')
			$xml .= '<![CDATA['.htmlentities($v, ENT_NOQUOTES | ENT_DISALLOWED, "UTF-8").']]>';
		// Close root node.
		$xml .= '</'.$k.'>';
		// Return the XML value.
		return $xml;
	}
}
