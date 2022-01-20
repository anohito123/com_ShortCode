<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact Plugin
 *
 * @since  3.2
 */
class PlgButtonShortcode extends JPlugin
{
		protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  JObject  The button options as JObject
	 *
	 * @since   1.5
	 */
	public function onDisplay($name)
	{
		$input = JFactory::getApplication()->input;
		$user  = JFactory::getUser();

		// Can create in any category (component permission) or at least in one category
	

		// This ACL check is probably a double-check (form view already performed checks)
	
		$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;'. JSession::getFormToken() . '=1&amp;editor=' . $name;
//$link = 123;

//var_dump(JPATH_PLUGINS.'/shortcode/sc.php');exit;

		//$link = substr(JURI::base(),)

       // $link = str_replace('administrator','plugins',JURI::base()).'editors-xtd/shortcode/sc.php';
			
		$button = new JObject;
		$button->modal   = true;
		$button->class   = 'btn';
		
		$button->link    = "index.php?option=com_minitool&tmpl=component";
		
		//var_dump($button->link);exit;
		$button->text    = JText::_('插入简码');
		$button->name    = 'file-add';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 450}}";

		return $button;
	}


}
