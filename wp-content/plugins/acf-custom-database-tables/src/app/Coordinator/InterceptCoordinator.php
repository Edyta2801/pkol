<?php


namespace ACFCustomDatabaseTables\Coordinator;


use ACFCustomDatabaseTables\Cache\DataCache;
use ACFCustomDatabaseTables\Intercept\InterceptBase;
use ACFCustomDatabaseTables\Settings;


class InterceptCoordinator {


	/** @var  Settings */
	private $settings;


	/** @var  TableCoordinator */
	private $tables;


	/** @var InterceptBase[] */
	private $intercepts = [];


	/** @var DataCache */
	private $cache;


	/**
	 * InterceptCoordinator constructor.
	 *
	 * @param Settings $settings
	 * @param TableCoordinator $tables
	 * @param DataCache $cache
	 */
	public function __construct( Settings $settings, TableCoordinator $tables, DataCache $cache ) {
		$this->tables   = $tables;
		$this->settings = $settings;
		$this->cache    = $cache;
	}


	/**
	 * Registers an intercept against this object.
	 *
	 * @param InterceptBase $intercept
	 */
	public function register_intercept( InterceptBase $intercept ) {
		if ( ! ( $intercept instanceof InterceptBase ) ) {
			$type = gettype( $intercept );
			trigger_error( "$type does not extend InterceptBase. Intercept was not registered." ); // todo - maybe handle these differently
		}
		$intercept->set_intercept_coordinator( $this );
		$this->intercepts[] = $intercept;
	}


	/**
	 * Initialises all intercepts
	 */
	public function init() {
		foreach ( $this->intercepts as $intercept ) {
			$intercept->init();
		}
	}


	/**
	 * @return Settings
	 */
	public function settings() {
		return $this->settings;
	}


	/**
	 * @param $key
	 * @param string $context
	 *
	 * @return bool
	 */
	public function has_table( $key, $context = 'post' ) {
		// no map, no table
		if ( ! ( $map = $this->tables->map() ) ) {
			return false;
		}

		return (bool) $map->locate_table_by_acf_field_name( $key, $context );
	}


	/**
	 * TODO - Needs review and testing – VERY ROUGHLY ADDED
	 *
	 * @param $key
	 * @param string $context
	 *
	 * @return bool
	 */
	public function is_join_table( $key, $context = 'post' ) {
		// no map, no table
		if ( ! ( $map = $this->tables->map() ) ) {
			return false;
		}

		if ( ! ( $table_name = $map->locate_table_by_acf_field_name( $key, $context ) ) ) {
			return false;
		}

		return $map->is_join_table( $table_name );
	}


	/**
	 * Creates/updates a single value
	 *
	 * @param string $key
	 * @param string|array $value
	 * @param string $context
	 * @param int $id
	 *
	 * @return mixed|\WP_Error
	 */
	public function update( $key, $value, $context = 'post', $id ) {
		if ( ! ( $map = $this->tables->map() ) ) {
			return $value; // no map object found, let's bypass this and just return the value.
		}

		$table_name  = $map->locate_table_by_acf_field_name( $key, $context );
		$column_name = $map->locate_column_name_by_acf_field_name( $table_name, $key );
		$table       = $this->tables->get_table_object( $table_name );

		if ( is_wp_error( $table ) ) {
			trigger_error( "Could not locate the `$table_name` object." );

			return $value;
		}

		$bool = $table->update_value( $column_name, $value, $id );

		if ( $bool === false ) {
			return new \WP_Error( 'acfcdt', "Error saving field to custom table `$table_name`. Error message: " . $table->db->last_error );
		}

		$this->cache->delete_record( $table_name, $context, $id );

		return $value;
	}


	/**
	 * @param $key
	 * @param string $context
	 * @param $id
	 *
	 * @return \ACFCustomDatabaseTables\DB\DynamicTableBase|null|\WP_Error
	 */
	public function find( $key, $context = 'post', $id ) {

		if ( ! $map = $this->tables->map() ) {
			return null;
		}

		if ( ! $table_name = $map->locate_table_by_acf_field_name( $key, $context ) ) {
			return null;
		}

		$column_name = $map->locate_column_name_by_acf_field_name( $table_name, $key );

		$row = $this->cache->get_record( $table_name, $context, $id );

		if ( ! $row ) {

			$table = $this->tables->get_table_object( $table_name );

			if ( is_wp_error( $table ) ) {
				trigger_error( "Could not locate the `$table_name` object." );

				return $table;
			}

			if ( $row = $table->find_value( $column_name, $context, $id ) ) {
				$this->cache->set_record( $table_name, $context, $id, $row );
			}
		}

		return ( $row and isset( $row[ $column_name ] ) )
			? $row[ $column_name ]
			: null;

	}


	/**
	 * Deletes data for a particular object and clears any cached records for that object
	 *
	 * @param string $context e.g; post:book
	 * @param int $object_id e.g; post.ID or user.ID
	 */
	public function delete_all_data_for_object( $context, $object_id ) {

		$data = explode( ':', $context );

		switch ( $data[0] ) {
			case 'user':
				// todo - when we need it
				break;
			case 'post':
				$this->delete_all_data_for_post( $object_id, $data[1] );
				break;
		}

	}


	/**
	 * @param $post_id
	 * @param $post_type
	 */
	public function delete_all_data_for_post( $post_id, $post_type ) {

		$table_names = $this->tables->map()->locate_all_tables_by_post_type( $post_type );

		foreach ( $table_names as $table_name ) {
			$table = $this->tables->get_table_object( $table_name );
			$table->delete_where( [ 'post_id' => $post_id ] );
			$this->cache->delete_record( $table_name, "post:{$post_type}", $post_id );
		}

	}


	/**
	 * @param $user_id
	 */
	public function delete_all_data_for_user( $user_id ) {

		$table_names = $this->tables->map()->locate_all_user_tables();

		foreach ( $table_names as $table_name ) {
			$table = $this->tables->get_table_object( $table_name );
			$table->delete_where( [ 'user_id' => $user_id ] );
			$this->cache->delete_record( $table_name, "user", $user_id );
		}

	}


}