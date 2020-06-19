<?php


namespace ACFCustomDatabaseTables\Model;


/**
 * Class FieldGroup
 *
 * Internal field group object. This is not necessarily a full parity representation of an ACF field group but, instead,
 * has all data/methods required by this plugin.
 *
 * @see \ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory for object creation.
 *
 */
class ACFFieldGroup {


	const MANAGE_TABLE_DEFINITION_KEY = 'acfcdt_manage_table_definition';
	const TABLE_NAME_KEY = 'acfcdt_table_name';
	const DEFINITION_FILE_NAME_KEY = 'acfcdt_table_definition_file_name';


	/** @var \WP_Post $post */
	private $post;


	/** @var array Unserialized post content from ACF field group post type */
	private $settings = [];


	/** @var array Registered fields on the field group (post meta) */
	private $fields = [];


	/** @var bool */
	private $has_unique_table_name;


	/** @var bool */
	private $has_unique_file_name;


	/** @var string */
	private $unique_file_name;


	/**
	 * FieldGroup constructor.
	 *
	 * @param \WP_Post $post
	 */
	public function __construct( \WP_Post $post ) {
		$this->post     = $post;
		$this->settings = maybe_unserialize( $post->post_content );
	}


	/**
	 * Gets a setting from ACF field group
	 *
	 * @param $setting_name
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public function get_setting( $setting_name, $default = '' ) {
		return isset( $this->settings[ $setting_name ] )
			? $this->settings[ $setting_name ]
			: $default;
	}


	/**
	 * Conditional check to see if table is active for this field group. Functions as setter if bool provided as arg.
	 *
	 * @param null $bool
	 *
	 * @return bool
	 */
	public function should_manage_table_definition( $bool = null ) {
		if ( is_bool( $bool ) ) {
			update_post_meta( $this->post->ID, self::MANAGE_TABLE_DEFINITION_KEY, $bool );
		}

		// try post meta
		$bool = (bool) get_post_meta( $this->post->ID, self::MANAGE_TABLE_DEFINITION_KEY, true );

		// try field group content (in case post meta not available after JSON sync)
		$bool or $bool = (bool) $this->get_setting( self::MANAGE_TABLE_DEFINITION_KEY );

		return $bool;
	}


	/**
	 * Internal meta handler for table name
	 *
	 * @param null $string
	 *
	 * @return mixed
	 */
	private function stored_table_name( $string = null ) {

		if ( is_string( $string ) ) {
			update_post_meta( $this->post->ID, self::TABLE_NAME_KEY, $string );
		}

		return get_post_meta( $this->post->ID, self::TABLE_NAME_KEY, true );
	}


	/**
	 * Sets/gets custom table name, minus the $wpdb->prefix
	 *
	 * @param null $string
	 *
	 * @return string
	 */
	public function table_name( $string = null ) {
		if ( is_string( $string ) ) {
			$this->stored_table_name( $string );
		}

		// try post meta
		$name = $this->stored_table_name();

		// try field group content (in case post meta not available after JSON sync)
		$name or $name = $this->get_setting( self::TABLE_NAME_KEY );

		return $name;
	}


	/**
	 * @return array
	 */
	public function fields() {
		if ( ! $this->fields ) {
			$this->fields = acf_get_fields( acf_get_field_group($this->post->ID) );
		}

		return $this->fields ?: [];
	}


	public function reset_post_meta() {
		delete_post_meta( $this->post->ID, self::MANAGE_TABLE_DEFINITION_KEY );
		delete_post_meta( $this->post->ID, self::TABLE_NAME_KEY );
		delete_post_meta( $this->post->ID, self::DEFINITION_FILE_NAME_KEY );
	}


	/**
	 * todo - consider moving this into a separate object
	 *
	 * @param $file_name
	 *
	 * @return mixed|string
	 */
	public function sanitize_definition_file_name( $file_name ) {
		$file_name = sanitize_file_name( $file_name );
		$info      = pathinfo( $file_name );

		// strip the extension
		if ( isset( $info['extension'] ) and $info['extension'] ) {
			$file_name = str_replace( '.' . $info['extension'], '', $file_name );
		}

		return $file_name;
	}


	/**
	 * @param null $string
	 *
	 * @return mixed
	 */
	public function definition_file_name( $string = null ) {

		if ( is_string( $string ) ) {
			update_post_meta( $this->post->ID, self::DEFINITION_FILE_NAME_KEY, $string );
		}

		return $this->unique_file_name
			?: get_post_meta( $this->post->ID, self::DEFINITION_FILE_NAME_KEY, true )
				?: $this->get_setting( self::DEFINITION_FILE_NAME_KEY )
					?: $this->generate_file_name();
	}


	/**
	 * @return int
	 */
	public function post_modified_time() {
		return get_post_modified_time( 'U', true, $this->post->ID, true ) ?: 0;
	}


	/**
	 * Generates a file name for this field group
	 *
	 * @return string
	 */
	private function generate_file_name() {

		if ( ! $this->unique_file_name ) {
			$this->unique_file_name = uniqid( "table_{$this->post->ID}x" );
		}

		return $this->unique_file_name;
	}


	/**
	 * @param $acf_field_group_array
	 */
	public function update_post_meta_from_field_group_array( $acf_field_group_array ) {

		if ( isset( $acf_field_group_array[ self::MANAGE_TABLE_DEFINITION_KEY ] ) ) {
			$this->should_manage_table_definition( (bool) $acf_field_group_array[ self::MANAGE_TABLE_DEFINITION_KEY ] );
		}

		if ( isset( $acf_field_group_array[ self::TABLE_NAME_KEY ] ) ) {
			$this->table_name( $acf_field_group_array[ self::TABLE_NAME_KEY ] );
		}

		if ( isset( $acf_field_group_array[ self::TABLE_NAME_KEY ] ) ) {
			$this->definition_file_name( $acf_field_group_array[ self::DEFINITION_FILE_NAME_KEY ] );
		}
	}


	/**
	 * Looks to the field group settings and saves those in post meta for the field group
	 */
	public function update_post_meta_from_internal_field_group_settings() {

		if ( $setting = $this->get_setting( self::MANAGE_TABLE_DEFINITION_KEY ) ) {
			$this->should_manage_table_definition( (bool) $setting );
		}

		if ( $setting = $this->get_setting( self::TABLE_NAME_KEY ) ) {
			$this->table_name( $setting );
		}

		if ( $setting = $this->get_setting( self::DEFINITION_FILE_NAME_KEY ) ) {
			$this->definition_file_name( $setting );
		}
	}


	/**
	 * If any other field group posts are found with the same table name, returns false
	 *
	 * Only checks field group data at this time, so it is still possible for a dev to manually create tables with the
	 * same name by creating definition files manually. This would result in data conflicts, so we should work on that
	 * for a future version.
	 *
	 * @return bool
	 */
	public function has_unique_table_name() {

		if ( ! $this->has_unique_table_name ) {

			$this->has_unique_table_name = true;

			// very rough here...
			global $wpdb;
			$table_name = $this->table_name();
			if ( ! $this->owns_table_name( $table_name ) ) {
				$table_exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s;", $wpdb->prefix . $table_name ) );
				if ( $table_exists or $this->another_field_group_owns_table_name() ) {
					$this->has_unique_table_name = false;
				}
			}
		}

		return $this->has_unique_table_name;
	}


	public function another_field_group_owns_table_name() {
		return (bool) get_posts( [
			'post_type'              => 'acf-field-group',
			'post__not_in'           => [ $this->post->ID ],
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => [
				[
					'key'   => self::TABLE_NAME_KEY,
					'value' => $this->table_name(),
				]
			]
		] );
	}


	/**
	 * If any other field group posts are found with the same file name, returns false
	 *
	 * Only checks field group data at this time, so it is still possible for a dev to manually create tables with the
	 * same name by creating definition files manually. This would result in data conflicts, so we should work on that
	 * for a future version.
	 *
	 * @return bool
	 */
	public function has_unique_file_name() {

		if ( ! $this->has_unique_file_name ) {
			$this->has_unique_file_name = ! get_posts( [
				'post_type'              => 'acf-field-group',
				'post__not_in'           => [ $this->post->ID ],
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
				'meta_query'             => [
					[
						'key'   => self::DEFINITION_FILE_NAME_KEY,
						'value' => $this->definition_file_name(),
					]
				]
			] );
		}

		return $this->has_unique_file_name;
	}


	/**
	 * Checks if this field group already owns the table name. i.e; has the table saved in the DB.
	 *
	 * @param string $table_name Unprefixed table name
	 *
	 * @return bool
	 */
	public function owns_table_name( $table_name ) {

		if ( $stored = $this->stored_table_name() ) {
			return $stored === $table_name;
		}

		return false;
	}


}