<?php
namespace EllisLab\ExpressionEngine\Service\Filter;

use InvalidArgumentException;
use EllisLab\ExpressionEngine\Library\CP\URL;
use EllisLab\ExpressionEngine\Service\View\ViewFactory;

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
 * ExpressionEngine Site Filter Class
 *
 * This will provide the HTML for a filter that will list a set of sites as well
 * as a custom <input> element for searching for a site.
 *
 * @package		ExpressionEngine
 * @category	Service
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Site extends Filter {

	protected $msm_enabled = FALSE;

	/**
	 * Constructor
	 *
	 * @see Filter::$options for the format of the options array
	 *
	 * @param array $options An associative array of options
	 */
	public function __construct(array $options = array())
	{
		$this->name = 'filter_by_site';
		$this->label = 'site';
		$this->placeholder = lang('filter_by_site');
		$this->options = $options;
	}

	/**
	 * Sets the $msm_enabled boolean variable to TRUE
	 *
	 * @return void
	 */
	public function enableMSM()
	{
		$this->msm_enabled = TRUE;
	}

	/**
	 * Sets the $msm_enabled boolean variable to FALSE
	 *
	 * @return void
	 */
	public function disableMSM()
	{
		$this->msm_enabled = FALSE;
	}

	/**
	 * Validation: is the value in our list of options?
	 */
	public function isValid()
	{
		// This is "valid" if MSM is Disabled
		if ( ! $this->msm_enabled)
		{
			return TRUE;
		}

		if ( ! (int) $this->value())
		{
			return FALSE;
		}

		return (array_key_exists((int) $this->value(), $this->options));
	}

	/**
	 * @see Filter::render for render behavior and arguments
	 *
	 * Overrides the abstract render behavior by returning an empty string
	 * if multtiple sites are not available.
	 */
	public function render(ViewFactory $view, URL $url)
	{
		if ( ! $this->msm_enabled)
		{
			return '';
		}

		parent::render($view, $url);
	}

}
// EOF
