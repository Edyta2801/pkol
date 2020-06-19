<?php


namespace ACFCustomDatabaseTables\FileIO;


use ACFCustomDatabaseTables\Data\TableValidator;
use ACFCustomDatabaseTables\Model\ACFFieldGroup;
use ACFCustomDatabaseTables\Service\ACFFieldSupportManager;
use ACFCustomDatabaseTables\Service\TableNameValidator;
use ACFCustomDatabaseTables\Settings;
use WP_Error;


/**
 * Class TableJSONFileGenerator
 * @package ACFCustomDatabaseTables\FileIO
 *
 * todo: things grew pretty quick here, so this whole class could use some refactoring at some point
 */
class TableJSONFileGenerator {


	/** @var Settings */
	private $settings;


	/** @var TableNameValidator */
	private $table_name_validator;


	/** @var TableValidator */
	private $table_validator;


	/** @var ACFFieldGroup */
	private $field_group;


	/** @var ACFFieldSupportManager */
	private $field_support_manager;


	/**
	 * TableJSONFileGenerator constructor.
	 *
	 * @param Settings $settings
	 * @param TableNameValidator $table_name_validator
	 * @param TableValidator $table_validator
	 * @param ACFFieldSupportManager $field_support_manager
	 */
	public function __construct( Settings $settings, TableNameValidator $table_name_validator, TableValidator $table_validator, ACFFieldSupportManager $field_support_manager ) {
		$this->settings              = $settings;
		$this->table_name_validator  = $table_name_validator;
		$this->table_validator       = $table_validator;
		$this->field_support_manager = $field_support_manager;
	}


	/**
	 * @param ACFFieldGroup $field_group
	 */
	public function set_field_group( ACFFieldGroup $field_group ) {
		$this->field_group = $field_group;
	}


	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return string|WP_Error
	 */
	public function generate_from_field_group( ACFFieldGroup $field_group ) {
		$this->set_field_group( $field_group );

		return $this->generate();
	}


	/**
	 * @return array|WP_Error Returns the table definition array on success, WP_Error on failure
	 */
	public function generate() {

		if ( ! $this->field_group ) {
			return new WP_Error( 'acfcdt', 'ACFFieldGroup object not set.' );
		}

		if ( is_wp_error( $validate = $this->table_name_validator->validate( $this->field_group->table_name() ) ) ) {
			return $validate;
		}

		if ( ! $this->field_group->fields() ) {
			return new WP_Error( 'acfcdt', 'Field group has no fields – no definition file could be created.' );
		}

		if ( ! $this->get_supported_fields( $this->field_group ) ) {
			return new WP_Error( 'acfcdt', 'None of the fields in this field group are supported for custom database table use at this time.' );
		}

		if ( ! $relationship = $this->get_relationship( $this->field_group ) ) {
			return new WP_Error( 'acfcdt', 'Could not establish a relationship for the custom database tables – the field group needs to have either a <strong>post type</strong> or <strong>user</strong> in its location rules.' );
		}

		if ( ! file_exists( $dir = $this->settings->get( 'json_dir' ) ) ) {
			if ( false === wp_mkdir_p( $dir ) ) {
				return new WP_Error( 'acfcdt', 'Custom database tables JSON save directory could not be created. Try creating the directory manually then running the process again. Directory: ' . $dir );
			}
		}

		if ( ! is_writable( $dir = $this->settings->get( 'json_dir' ) ) ) {
			return new WP_Error( 'acfcdt', 'Custom database tables JSON save directory is not writable. Directory: ' . $dir );
		} else {
			$this->harden_table_definition_directory();
		}

		$table_name  = $this->field_group->table_name();
		$join_tables = $this->build_join_tables_array( $this->field_group );
		$columns     = $this->build_columns_array( $this->field_group );

		// todo - keygen doesn't really belong here. Better to move this to the table normalisation process.
		$object_key = $this->table_validator->get_object_relationship_key_name( [ 'relationship' => $relationship ] );
		array_unshift( $columns, [
			'name'   => $object_key,
			'format' => '%d',
		] );

		array_unshift( $columns, [
			'name'           => 'id',
			'format'         => '%d',
			'null'           => false,
			'auto_increment' => true,
			'unsigned'       => true,
		] );

		$definition = [
			'name'         => $table_name,
			'relationship' => $relationship,
			'primary_key'  => [
				'id'
			],
			'keys'         => [
				[
					'name'    => $object_key,
					'columns' => [
						$object_key
					],
					'unique'  => true,
				]
			],
			'columns'      => $columns,
		];

		if ( $join_tables ) {
			$definition['join_tables'] = $join_tables;
		}

		/**
		 * Taking a snapshot of the definition so that we can, if we need to, prevent future edits to a field group
		 * that may have been manually adjusted. This isn't a feature right now, so saving a field group will definitely
		 * overwrite any manual edits to a table definition JSON file, but this gives us something to compare to if/when
		 * we start to do this.
		 */
		$definition['hash']     = md5( json_encode( $definition ) );
		$definition['modified'] = $this->field_group->post_modified_time();

		return $save = $this->write_to_file( $this->get_file_path( $this->field_group ), $definition );
	}


	/**
	 * Method creates two files to help prevent people accessing table definitions:
	 *
	 * 1. empty index.php file
	 * 2. .htaccess file
	 */
	public function harden_table_definition_directory() {

		$dir = $this->settings->get( 'json_dir' );

		if ( ! file_exists( $index_file = $dir . '/index.php' ) ) {
			if ( false === ( fclose( fopen( $index_file, 'w' ) ) ) ) {
				trigger_error( 'Could not create empty index.php file. You should create the file manually. File: ' . $index_file );
			} else {
				if ( false === file_put_contents( $index_file, "<?php // silence" ) ) {
					trigger_error( 'Could not write to index.php file. File: ' . $index_file );
				}
			}
		}

		if ( ! file_exists( $htaccess_file = $dir . '/.htaccess' ) ) {
			if ( false === ( fclose( fopen( $htaccess_file, 'w' ) ) ) ) {
				trigger_error( 'Could not create .htaccess file. You should create the file manually. File: ' . $htaccess_file );
			} else {
				if ( false === file_put_contents( $htaccess_file, "Options -Indexes\r\nDeny from all" ) ) {
					trigger_error( 'Could not write to .htaccess file. File: ' . $htaccess_file );
				}
			}
		}

	}


	/**
	 * Writes an array as encoded JSON to a given file. If the file doesn't already exist, this will attempt to create
	 * it.
	 *
	 * @param string $file The path and name of a file to write to
	 * @param array $definition_array
	 *
	 * @return array|WP_Error
	 */
	public function write_to_file( $file, $definition_array ) {

		$file_exists = file_exists( $file );

		if ( ! $file_exists ) {
			if ( false === ( fclose( fopen( $file, 'w' ) ) ) ) {
				return new WP_Error( 'acfcdt', "Could not create table definition JSON file for field group. File: $file" );
			}
		}

		if ( false === file_put_contents( $file, acf_json_encode( $definition_array ), true ) ) {
			return new WP_Error( 'acfcdt', "There was a problem writing to the table definition JSON file. File: $file" );
		}

		return [
			'action'     => $file_exists ? 'updated' : 'created',
			'definition' => $definition_array,
			'file'       => $file
		];
	}


	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return string
	 */
	public function get_file_path( ACFFieldGroup $field_group ) {

		$save_dir  = untrailingslashit( $this->settings->get( 'json_dir' ) );
		$file_name = sanitize_file_name( $field_group->definition_file_name() );
		$path_info = pathinfo( $file_name );

		if ( isset( $path_info['extension'] ) and $path_info['extension'] ) {
			$file_name = str_replace( '.' . $path_info['extension'], '', $file_name );
		}

		return "$save_dir/$file_name.json";
	}


	/**
	 * Returns a relationship array for use in the definition file. This currently just finds the first post-type/user
	 * that it can and returns a relationship for that, as we are only currently supporting one object relationship per
	 * table at this time. Support for more objects will come.
	 *
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function get_relationship( ACFFieldGroup $field_group ) {
		if ( $location_rules = $field_group->get_setting( 'location' ) ) {

			$flattened_rules = call_user_func_array( 'array_merge', $location_rules );

			$flattened_rules = array_filter( $flattened_rules, function ( $rule ) {
				return $rule['operator'] === '==';
			} );

			// find first post type or user in rule set and match that. later, we'll support more complex relationships to multiple objects
			foreach ( $flattened_rules as $rule ) {
				if ( $rule['param'] === 'post_type' ) {

					return [ 'type' => 'post', 'post_type' => $rule['value'] ];

				} elseif ( in_array(
					$rule['param'], [
						'user_form',
						//'user_role',
						//'current_user',
						//'current_user_role'
					]
				) ) {
					return [ 'type' => 'user' ];
				}
			}
		}

		return [];
	}


	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function get_supported_fields( ACFFieldGroup $field_group ) {

		$supported_fields = ( $fields = $field_group->fields() )
			? array_filter( $fields, [ $this->field_support_manager, 'is_supported' ] )
			: [];

		return apply_filters( 'acfcdt/field_group_supported_fields', $supported_fields, $field_group->table_name() );
	}


	/**
	 * @param $fields array of ACF field arrays (field group's fields array)
	 *
	 * @return array
	 */
	public function extract_field_names( $fields ) {
		$names = $tables = array_map( function ( $field ) {
			return $field['name'];
		}, $fields );

		return array_values( $names );
	}


	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function extract_join_fields( ACFFieldGroup $field_group ) {
		return array_filter( $field_group->fields(), function ( $field ) {

			$table_name        = $this->field_group->table_name();
			$field_is_eligible = $this->field_support_manager->field_eligible_for_join_table( $field );
			$create_join_table = false;

			if ( $field_is_eligible ) {

				$create_join_table = $this->settings->get( 'enable_join_tables_globally' );

				/**
				 * Filter this and return TRUE to enable the addition of join table definitions in the generated table
				 * definition JSON. You can do this for certain fields, field types, or even entire tables using the
				 * available args.
				 *
				 * IF you just return FALSE on this filter, no join table definitions will be added to any JSON files on
				 * the next time the schema is updated. IF you just return TRUE, all eligible fields will create join
				 * tables on the next schema update.
				 *
				 * IF you already have join tables in place and you disable some using this filter, the schema will need
				 * to be updated before ACF Custom Database Tables will understand the change.
				 *
				 * Note: you can't use this to activate join tables on fields that don't currently support that, as this
				 * filter only runs on eligible fields.
				 */
				$create_join_table = apply_filters( 'acfcdt/field_creates_join_table', $create_join_table, $field, $table_name );
			}

			return $create_join_table;
		} );
	}


	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function build_columns_array( ACFFieldGroup $field_group ) {

		$columns = [];

		$fields               = $this->get_supported_fields( $field_group );
		$excluded_field_names = [];

		if ( $join_table_fields = $this->extract_join_fields( $field_group ) ) {
			$excluded_field_names = $this->extract_field_names( $join_table_fields );
		}

		foreach ( $fields as $field ) {

			if ( in_array( $field['name'], $excluded_field_names ) ) {
				continue;
			}

			$sanitized_field_name = $this->table_name_validator->sanitize( $field['name'] );
			$columns[]            = [
				'name' => $sanitized_field_name,
				'map'  => [
					'type'       => 'acf_field_name',
					'identifier' => $field['name'],
				],
			];
		}

		return $columns;
	}


	/**
	 * @param ACFFieldGroup $field_group
	 *
	 * @return array
	 */
	public function build_join_tables_array( ACFFieldGroup $field_group ) {

		$join_tables = [];

		if ( ! $eligible_fields = $this->extract_join_fields( $field_group ) ) {
			return $join_tables;
		}

		$parent_table_name = $field_group->table_name();
		$relationship      = $this->get_relationship( $field_group );

		/**
		 * todo - this is a bit of a hacky way to get the object key – change the approach when time allows, likely by
		 * moving all this to the normalisation process. Note: this is also used in $this->generate()
		 */
		$obj_key = $this->table_validator->get_object_relationship_key_name( [ 'relationship' => $relationship ] );

		foreach ( $eligible_fields as $field ) {
			$sanitized_field_name = $this->table_name_validator->sanitize( $field['name'] );
			$field_format         = $field['type'] === 'page_link' ? '%s' : '%d';
			$join_tables[]        = [
				'name'        => "{$parent_table_name}__{$sanitized_field_name}",
				'primary_key' => [
					'id'
				],
				'keys'        => [
					[
						'name'    => $obj_key . '_' . $sanitized_field_name,
						'columns' => [
							$obj_key,
							$sanitized_field_name
						],
						'unique'  => true,
					],
					[
						'name'    => $sanitized_field_name,
						'columns' => [
							$sanitized_field_name
						],
					]
				],
				'columns'     => [
					[
						'name'           => 'id',
						'format'         => '%d',
						'null'           => false,
						'auto_increment' => true,
						'unsigned'       => true,
					],
					[
						'name'   => $obj_key,
						'format' => '%d'
					],
					[
						'name'   => $sanitized_field_name,
						'format' => $field_format,
						'map'    => [
							'type'       => 'acf_field_name',
							'identifier' => $field['name']
						],
					],
					[
						'name'    => '_sort_order',
						'format'  => '%d',
						'default' => 0
					],
				]
			];
		}

		return $join_tables;

	}


}