<?php


$json_dir = \ACFCustomDatabaseTables\Utils\Dir::get_json_dir();


/**
 * Plugin settings array
 */
return [


	/**
	 * These settings do not pass through any filter and cannot be changed after initialisation.
	 */
	'immutable'                      => [

		/**
		 * The directory where all plugin-generated files (cache files, JSON files) are stored.
		 */
		'json_dir'            => $json_dir,

		/**
		 * Specifies the directory where the table map array is stored as a PHP include. This saves us from having to
		 * process all JSON files on every page load.
		 */
		'table_map_cache_dir' => "{$json_dir}/.cache"
	],


	/**
	 * Specifies whether ACF meta VALUES are saved to core meta tables alongside custom tables. Setting this to FALSE
	 * will bypass core meta tables for those values where a custom table exists for that data.
	 *
	 * Override this using the acfcdt/settings/store_acf_values_in_core_meta filter.
	 */
	'store_acf_values_in_core_meta'  => true,


	/**
	 * Specifies whether ACF meta key references are saved to core meta tables. Setting this to FALSE will prevent the
	 * storage of ACF key references. e.g;
	 *
	 *  'field_name'  => 'Some Value',              // field name/value pair
	 *  '_field_name' => 'field_5ac1c42330eb4'      // reference/key pair – this settings controls this data
	 *
	 * REALLY IMPORTANT:
	 *
	 * Unless you are using ACF JSON, these references are necessary for correct data storage and retrieval. If you set
	 * this to false and you aren't using ACF JSON, you will have problems.
	 *
	 * Override this using the acfcdt/settings/store_acf_keys_in_core_meta filter.
	 */
	'store_acf_keys_in_core_meta'    => true,


	/**
	 * Specifies whether the ACF 3rd party customisation filters will run on field values before the data is stored in
	 * a custom table.
	 *
	 * Override this using the acfcdt/settings/allow_acf_update_value_filters filter.
	 */
	'allow_acf_update_value_filters' => true,


	/**
	 * Specifies whether the ACF 3rd party customisation filters will run on field values after the data has been
	 * retrieved from a custom table.
	 *
	 * Override this using the acfcdt/settings/allow_acf_load_value_filters filter.
	 */
	'allow_acf_load_value_filters'   => true,


	/**
	 * Specifies whether join tables will be created for eligible fields by default on table definition creation. If set
	 * to TRUE, any eligible field – unless specifically overridden – will result in a join table.
	 *
	 * Override this using the acfcdt/settings/enable_join_tables_globally filter.
	 */
	'enable_join_tables_globally'    => false,


	/**
	 * Controls which modules will be activated on plugin initialisation. If a module is set to TRUE, it will be activated.
	 *
	 * Override this using the acfcdt/settings/activate_modules filter. Note: this setting is only evaluated on plugin
	 * initialisation, so if you need to modify this, it will need to be from within a plugin.
	 */
	'activate_modules'               => [

		/**
		 * Module converts integer representations of strings to type integer before data is stored in custom tables.
		 * Arrays of integers are JSON encoded in a cleaner fashion that when left as arrays of strings.
		 */
		'integer_type_cast' => true,

		/**
		 * Module stores eligible data as serialized string instead of the default JSON encoded string.
		 *
		 * If this module is activated after a database has already been collecting data, only newly saved/updated data will
		 * be serialized. Any old posts will continue to have their data stored in the default JSON encoded format until they
		 * are saved/updated.
		 */
		'serialized_data'   => false,
	]


];