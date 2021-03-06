<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2015, EllisLab, Inc.
 * @license		https://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 2.6
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Parse Node Class
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class ParseNode extends EE_TreeNode {

	private $_dirty;
	private $_parser;
	private $_entry_id_fn;
	private $_entry_id_opts;

	public function __construct($name, $payload = NULL)
	{
		parent::__construct($name, $payload);

		// if entry_id="4|5|6" was given, we filter them here
		$parameter = $this->param('entry_id');

		if ($parameter)
		{
			$this->_entry_id_fn = 'array_intersect';

			if (strncasecmp($parameter, 'not ', 4) == 0)
			{
				$this->_entry_id_fn = 'array_diff';
				$parameter = substr($parameter, 4);
			}

			$parameter = trim($parameter, " |\r\n\t");
			$this->_entry_id_opts = explode('|', $parameter);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve the field name
	 *
	 * Removes the namespace so that we're only left with the last
	 * segment. If you need the full tag name, use `name()`
	 *
	 * @return 	string	The raw field name
	 */
	public function field_name()
	{
		$field_name = ':'.$this->name;
		return substr($field_name, strrpos($field_name, ':') + 1);
	}

	// --------------------------------------------------------------------

	/**
	 * Set a parameter
	 *
	 * Params is theoretically accessible through __get, but we don't
	 * return a reference so we can't modify it (except overriding it).
	 *
	 * @param 	string	The parameter name
	 * @param	mixed	The parameter value
	 * @return 	void
	 */
	public function set_param($key, $value = NULL)
	{
		$this->data['params'][$key] = $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Get a parameter
	 *
	 * Accessor to avoid constantly doing isset checks.
	 *
	 * @param 	string	The parameter name
	 * @param	mixed	a default to return if the parameter is not set
	 * @return 	mixed	parameter value || default
	 */
	public function param($key, $default = NULL)
	{
		return isset($this->data['params'][$key]) ? $this->data['params'][$key] : $default;
	}

	// --------------------------------------------------------------------

	/**
	 * Make the node aware of a relationship
	 *
	 * Creates an internal parent->child relationship so that we can return
	 * all child ids for any given incoming parent later.
	 *
	 * @param 	int		the parent entry id
	 * @param	int		the child entry id
	 * @return 	void
	 */
	public function add_entry_id($parent, $child)
	{
		$ids =& $this->data['entry_ids'];

		if (empty($child))
		{
			$child = array();
		}

		if ( ! isset($ids[$parent]))
		{
			$ids[$parent] = array();
		}

		if (is_array($child))
		{
			$ids[$parent] = array_merge($ids[$parent], $child);
		}
		else
		{
			$ids[$parent][] = $child;
		}

		$this->_dirty = TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Entry id accessor
	 *
	 * Ensures that only ids that are allowed by the entry_id= parameter
	 * are processed. This used to be in the setter, but it ends up being
	 * quite an expensive operation.
	 *
	 * @return 	[int]	parent => [child ids]
	 */
	public function entry_ids()
	{
		$ids =& $this->data['entry_ids'];

		if ($this->_dirty)
		{
			if (isset($this->_entry_id_opts))
			{
				$fn = $this->_entry_id_fn;
				$opts = $this->_entry_id_opts;

				foreach ($ids as $parent => $children)
				{
					$ids[$parent] = array_unique(
						$fn($children, $opts),
						SORT_NUMERIC
					);
				}
			}

			$this->_dirty = FALSE;
		}

		return $ids;
	}

	// --------------------------------------------------------------------

	/**
	 * At the end of the channel entries parsing loop we need to recurse
	 * into the child tags of our tree. The relationship_parser would need
	 * to manually keep track of the stack as the parsing happens depth
	 * first. It's much easier to bridge it here.
	 *
	 * @param 	string	tagdata for the children to parse
	 * @param	array	the current channel entries row
	 * @return 	string	child parsed tagdata
	 */
	public function callback_tagdata_loop_end($tagdata, $row)
	{
		foreach ($this->children() as $child)
		{
			$tagdata = $this->parser->parse_node($child, $row['entry_id'], $tagdata);
		}

		return $tagdata;
	}
}

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Parse Node Class
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 *
 * We store a shortcut path to the kids that need their own queries:
 * http://en.wikipedia.org/wiki/Transitive_closure
 */
class QueryNode extends ParseNode {

	private $closureChildren = array();

	// @override
	protected function _set_parent(EE_TreeNode $p)
	{
		parent::_set_parent($p);

		do
		{
			if ($p instanceOf QueryNode)
			{
				$p->addClosurePath($this);
				break;
			}

			$p = $p->parent();
		}
		while ($p);
	}

	public function closureChildren()
	{
		return $this->closureChildren;
	}

	public function addClosurePath(QueryNode $closureChild)
	{
		$this->closureChildren[] = $closureChild;
	}
}

/* End of file Nodes.php */
/* Location: ./system/expressionengine/libraries/relationship_parser/Nodes.php */