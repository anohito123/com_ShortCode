<?php
/**
 * Textmetrics - SEO made easy, for everyone
 *
 * @author       Jan Martin Roelofs
 * @copyright    (c) Textmetrics - 2019
 * @package      textmetrics
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      3.0.0
 * @date         2019-07-24
 *
 */

defined('_JEXEC') or die;

class com_minitoolInstallerScript
{


    public function install( $parent ) {
      //  $parent->getParent()->setRedirectURL('index.php?option=com_minitool');
    }

    public function update( $parent ) {
        // $parent->getParent()->setRedirectURL('index.php?option=com_textmetrics');
    }

    public function postflight( $type, $parent ) {
        $this->tm_plugin_install();
        $this->tm_plugin_enable();
      //  $this->tm_copy_setup_and_delete('webtexttool', 'anchor');

    }

    public function uninstall( $parent ) {
        $this->tm_plugin_disable();
    }

    /*
     * install the plugin
     */
    private function tm_plugin_install() {
        $installer =  new JInstaller();
        $path = dirname(__FILE__) . '/plugin/shortcode';
		$path1 = dirname(__FILE__) . '/plugin/analyzelogic';
        $installer->install($path);
		$installer->install($path1);
		
    }



    private function tm_plugin_enable() {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->update($db->quoteName('#__extensions'))
            ->set   ($db->quoteName('enabled') . ' = ' . (int) 1 )
            ->where ($db->quoteName('element') . ' = ' . $db->quote('shortcode'));
        $db->setQuery($query);
        $result = $db->execute();
		
		$query1 = $db->getQuery(true);
        $query1
            ->update($db->quoteName('#__extensions'))
            ->set   ($db->quoteName('enabled') . ' = ' . (int) 1 )
            ->where ($db->quoteName('element') . ' = ' . $db->quote('analyzelogic'));
        $db->setQuery($query1);
        $result = $db->execute();
    }

    private function tm_plugin_disable() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->update($db->quoteName('#__extensions'))
            ->set   ($db->quoteName('enabled') . ' = ' . (int) 0 )
            ->where ($db->quoteName('element') . ' = ' . $db->quote('shortcode'));
        $db->setQuery($query);
        $result = $db->execute();
		
		$query1 = $db->getQuery(true);
        $query1
            ->update($db->quoteName('#__extensions'))
            ->set   ($db->quoteName('enabled') . ' = ' . (int) 0 )
            ->where ($db->quoteName('element') . ' = ' . $db->quote('analyzelogic'));
        $db->setQuery($query1);
        $result = $db->execute();
    }


}