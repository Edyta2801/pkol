<?php
/**
 * Plugin Name: Advanced Custom Fields: Custom Database Tables
 * Plugin URI: https://www.hookturn.io/downloads/acf-custom-database-tables
 * Description: Store ACF data in custom database tables
 * Version: 1.0.5
 * Author: Hookturn ft. Phil Kurth
 * Author URI: http://www.hookturn.io
 * License: GPLv3 or later
 */


use ACFCustomDatabaseTables\Activator;
use ACFCustomDatabaseTables\Container;
use ACFCustomDatabaseTables\Core;
use ACFCustomDatabaseTables\Psr4Autoloader;


// If this file is called directly, abort.
defined( 'WPINC' ) or die();


include plugin_dir_path( __FILE__ ) . 'src/app/Psr4Autoloader.php';
include plugin_dir_path( __FILE__ ) . 'src/app/Activator.php';


/**
 * Main instance function.
 *
 * Need to interact with the ACF Custom Tables main instance? Call this function and go wild...just not too wild.
 * Seriously, though – if you are needing to access this, do so with caution as this plugin's internals are likely to
 * change significantly in its early stages of life.
 *
 * @return Core|null
 */
function acf_custom_database_tables() {

	static $instance;

	if ( Activator::is_acf_installed() and ! $instance ) {

		$dir = plugin_dir_path( __FILE__ );

		$loader = new Psr4Autoloader();
		$loader->register();
		$loader->addNamespace( 'ACFCustomDatabaseTables', $dir . 'src/app' );

		$definitions                   = require $dir . 'src/config/container.php';
		$definitions['plugin_file']    = __FILE__;
		$definitions['plugin_name']    = 'ACF Custom Database Tables';
		$definitions['plugin_version'] = '1.0.5';

		$container = new Container( $definitions );
		$instance  = new Core( $container );
	}

	return $instance;
}


add_action( 'plugins_loaded', function () {
	if ( $instance = acf_custom_database_tables() ) {
		$instance->init();
	}
} );


register_activation_hook( __FILE__, function () {
	Activator::check_activation_constraints();
} );