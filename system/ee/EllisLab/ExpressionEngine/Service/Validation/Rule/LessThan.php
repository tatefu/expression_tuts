<?php

namespace EllisLab\ExpressionEngine\Service\Validation\Rule;

use EllisLab\ExpressionEngine\Service\Validation\ValidationRule;

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, EllisLab, Inc.
 * @license		https://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 3.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Less Than Validation Rule
 *
 * @package		ExpressionEngine
 * @subpackage	Validation\Rule
 * @category	Service
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class LessThan extends ValidationRule {

	public function validate($key, $value)
	{
		list($compare) = $this->assertParameters('compare_to');

		if ( ! is_numeric($compare))
		{
			return FALSE;
		}

		return ($value < $compare);
	}

	public function getLanguageKey()
	{
		return 'less_than';
	}
}
