<?php


use ACFCustomDatabaseTables\Container;


$container                         = [];
$container['store_url']            = 'https://hookturn.io/';
$container['remote_docs_file_url'] = 'http://hookturn.io/wp-content/docs/acf-custom-database-tables.json';


$container['plugin_dir'] = function ( Container $c ) {
	return plugin_dir_path( $c['plugin_file'] );
};


$container['plugin_url'] = function ( Container $c ) {
	return plugin_dir_url( $c['plugin_file'] );
};


$container['asset_url'] = function ( Container $c ) {
	return $c['plugin_url'] . 'src/asset';
};


$container['config_dir'] = function ( Container $c ) {
	return $c['plugin_dir'] . 'src/config';
};


$container['view_dir'] = function ( Container $c ) {
	return $c['plugin_dir'] . 'src/asset/view';
};


$container['settings'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Settings( require $c['config_dir'] . '/settings.php' );
};


$container['wpdb'] = function ( Container $c ) {
	global $wpdb;

	return $wpdb;
};


$container['cache.data'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Cache\DataCache();
};


$container['cache.table_object'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Cache\TableObjectCache();
};


$container['table_name_validator'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Service\TableNameValidator( $c['wpdb'] );
};


$container['table_validator'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Data\TableValidator();
};


$container['column_validator'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Data\ColumnValidator();
};


$container['table_map'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Data\TableMap( $c['table_validator'] );
};


$container['json_file_parser'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\FileIO\JSONFileParser();
};


$container['table_json_file_generator'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\FileIO\TableJSONFileGenerator(
		$c['settings'],
		$c['table_name_validator'],
		$c['table_validator'],
		$c['acf_field_support_manager']
	);
};


$container['admin_notice_handler'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\UI\AdminNoticeHandler();
};


$container['persistent_admin_notice_handler'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\UI\PersistentAdminNoticeHandler();
};


$container['asset_manager'] = function ( Container $c ) {
	$manager           = new \ACFCustomDatabaseTables\UI\AssetManager( $c['asset_url'] );
	$plugin_version    = $c['plugin_version'];
	$asset_definitions = require $c['config_dir'] . '/assets.php';

	// set plugin version where assets have a 'null' version.
	foreach ( $asset_definitions as $type => $assets ) {
		$asset_definitions[ $type ] = array_map( function ( $asset ) use ( $plugin_version ) {
			isset( $asset['version'] ) and $asset['version'] or $asset['version'] = $plugin_version;

			return $asset;
		}, $assets );
	}

	$manager->set_asset_definitions( $asset_definitions );
	$manager->set_registration_hook( 'admin_enqueue_scripts' );
	$manager->init();

	return $manager;
};


$container['controller.settings_page'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Controller\SettingsPageController(
		$c['service.diagnostic_reporter'],
		$c['service.documentation_provider'],
		$c['controller.license_form'],
		$c['asset_manager'],
		$c['admin_notice_handler']
	);
};


$container['controller.acf_field_group_admin'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Controller\ACFFieldGroupAdminController(
		$c['field_group_custom_table_meta_box'],
		$c['factory.field_group'],
		$c['persistent_admin_notice_handler'],
		$c['table_json_file_generator'],
		$c['asset_manager']
	);
};


$container['controller.update_tables_form'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Controller\UpdateTablesFormController(
		$c['coordinator.table_creation'],
		$c['admin_notice_handler']
	);
};


$container['controller.default_context'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Controller\DefaultContextController( $c['coordinator.intercept'] );
};


$container['factory.dynamic_column'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Factory\DynamicColumnFactory(
		$c['wpdb'],
		$c['column_validator']
	);
};


$container['factory.dynamic_table'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Factory\DynamicTableFactory(
		$c['factory.dynamic_column'],
		$c['table_validator']
	);
};


$container['factory.field_group'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory();
};


$container['update_post_metadata_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\UpdatePostMetadataIntercept( $c['coordinator.core_metadata'] );
};


$container['update_user_metadata_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\UpdateUserMetadataIntercept( $c['coordinator.core_metadata'] );
};


$container['acf_field_group_delete_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\ACFFieldGroupDeleteIntercept(
		$c['factory.field_group'],
		$c['table_json_file_generator']
	);
};


$container['post_delete_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\PostDeleteIntercept();
};


$container['user_delete_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\UserDeleteIntercept();
};


$container['acf_get_field_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\ACFGetFieldIntercept( $c['acf_field_support_manager'], $c['coordinator.core_metadata'] );
};


$container['acf_update_field_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\ACFUpdateFieldIntercept( $c['acf_field_support_manager'], $c['coordinator.core_metadata'] );
};


$container['acf_delete_field_intercept'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Intercept\ACFDeleteFieldIntercept( $c['acf_field_support_manager'], $c['coordinator.core_metadata'] );
};


$container['coordinator.core_metadata'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Coordinator\CoreMetadataCoordinator();
};


$container['coordinator.table_creation'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Coordinator\TableCreationCoordinator( $c['coordinator.table'] );
};


$container['coordinator.table'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Coordinator\TableCoordinator(
		$c['json_file_parser'],
		$c['settings'],
		$c['table_map'],
		$c['factory.dynamic_table'],
		$c['cache.table_object']
	);
};


$container['coordinator.intercept'] = function ( Container $c ) {
	$coord = new \ACFCustomDatabaseTables\Coordinator\InterceptCoordinator(
		$c['settings'],
		$c['coordinator.table'],
		$c['cache.data']
	);
	$coord->register_intercept( $c['acf_get_field_intercept'] );
	$coord->register_intercept( $c['acf_update_field_intercept'] );
	$coord->register_intercept( $c['acf_field_group_delete_intercept'] );
	$coord->register_intercept( $c['post_delete_intercept'] );
	$coord->register_intercept( $c['user_delete_intercept'] );
	$coord->register_intercept( $c['acf_delete_field_intercept'] );
	$coord->register_intercept( $c['update_post_metadata_intercept'] );
	$coord->register_intercept( $c['update_user_metadata_intercept'] );

	return $coord;
};


$container['service.diagnostic_reporter'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Service\DiagnosticReporter( $c['wpdb'], $c['settings'] );
};


$container['service.documentation_provider'] = function ( Container $c ) {
	// daily timestamp unless WP_DEBUG is on
	$timestamp  = ( defined( 'WP_DEBUG' ) and WP_DEBUG )
		? time()
		: strtotime( '00:00:00' );
	$remoteDocs = $c['remote_docs_file_url'] . '?t=' . $timestamp;

	return new \ACFCustomDatabaseTables\Service\DocumentationProvider( $remoteDocs );
};


$container['controller.license_form'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Controller\LicenseFormController( $c['service.license'] );
};


$container['service.license'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Service\License();
};


$container['acf_field_support_manager'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Service\ACFFieldSupportManager();
};


$container['plugin_updater'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Vendor\ManualPackages\PluginUpdater(
		$c['store_url'],
		$c['plugin_file'],
		[
			'item_name' => $c['plugin_name'],
			'version'   => $c['plugin_version'],
			'license'   => $c['service.license']->get(),
			'author'    => 'Hookturn ft. Phil Kurth',
			'beta'      => false,
		]
	);
};


$container['field_group_custom_table_meta_box'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\UI\FieldGroupCustomTableMetaBox(
		$c['wpdb'],
		$c['factory.field_group'],
		$c['service.diagnostic_reporter'],
		$c['table_name_validator']
	);
};


$container['module.integer_type_cast'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Module\IntegerTypeCastModule();
};


$container['module.serialized_data'] = function ( Container $c ) {
	return new \ACFCustomDatabaseTables\Module\SerializedDataModule();
};


return $container;