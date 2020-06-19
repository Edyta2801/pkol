<?php
/**

 *@package pkol_import

 *
/*
Plugin Name: PKOL Import
Description: Import data
Author: yaqbick
*/

?>
<?php

defined('ABSPATH') or die('you cannot access this file');
add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu()
{
    add_menu_page('My Plugin Page', 'PKOL Import', 'read', 'pkol_import', 'display_panel');
}



function display_panel()
{
    require_once(ABSPATH.'wp-content/plugins/pkol_import/panel.php');
}
