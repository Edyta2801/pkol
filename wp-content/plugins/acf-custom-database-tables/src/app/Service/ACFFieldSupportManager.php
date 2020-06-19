<?php


namespace ACFCustomDatabaseTables\Service;


class ACFFieldSupportManager {


	/**
	 * Fields that we explicitly do not support...yet.
	 *
	 * @var array
	 */
	private $unsupported_fields = [
		'repeater',
		'group', // these come in as group-name_field-name meta entries, so we need to dig into this a bit more
		'flexible_content',
		'clone', // has a range of different configurations that all need to be accounted for
		// no data to save on any of these
		'message',
		'accordion',
		'tab',
	];


	/**
	 * ACF field types that don't need processing (simple, single-value fields)
	 *
	 * @var array
	 */
	private $unprocessed_fields = [
		'text',
		'textarea',
		'number',
		'range',
		'email',
		'url',
		'password',
		'wysiwyg',
		'oembed',
		'image',
		'file',
		'radio',
		'true_false',
		'color_picker',
		'date_picker',
		'date_time_picker',
		'time_picker',
		'button_group',
	];


	/**
	 * ACF field types that need processing (fields with more complexity – arrays)
	 *
	 * @var array
	 */
	private $processed_fields = [
		'gallery',
		'checkbox',
		'relationship',
		'google_map',
		'post_object',
		'page_link',
		'select',
		'user',
		'link',
		'map',
		'taxonomy',
	];


	/**
	 * ACF field types that result in a join table
	 *
	 * @var array
	 */
	private $join_table_fields = [
		'relationship',
		'post_object',
		'page_link',
		'user',
		'taxonomy',
	];


	/**
	 * @return array
	 */
	public function unprocessed_fields() {
		return $this->unprocessed_fields;
	}


	/**
	 * @return array
	 */
	public function processed_fields() {
		return $this->processed_fields;
	}


	/**
	 * Lists field types of all supported ACF fields
	 *
	 * @return array
	 */
	public function supported_fields() {
		return array_merge( $this->processed_fields, $this->unprocessed_fields );
	}


	/**
	 * Checks whether we currently support the ACF field being passed in
	 *
	 * @param array $field The ACF field object array
	 *
	 * @return bool
	 */
	public function is_supported( $field ) {

		if ( in_array( $field['type'], $this->unsupported_fields ) ) {
			return false;
		}

		$is_supported = in_array( $field['type'], $this->supported_fields() );

		/**
		 * Enables custom field types to register as supported.
		 * Enables the possibility of removing support for core field types.
		 *
		 * @param bool $is_supported Whether or not the provided field is supported.
		 * @param array $field The ACF field array
		 */
		return apply_filters( 'acfcdt/is_supported_field', $is_supported, $field );
	}


	/**
	 * @param $field
	 *
	 * @return bool
	 */
	public function field_eligible_for_join_table( $field ) {
		return in_array( $field['type'], $this->join_table_fields );
	}


	/**
	 * todo - the start of breaking field-specific handling out into separate objects
	 *
	 * Dynamic field filter methods. i.e; if a method exists, run it.
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return mixed
	 */
	public function maybe_process_field_value( $value, $field ) {

		$method = [ $this, "process_{$field['type']}_field_value" ];

		if ( method_exists( $method[0], $method[1] ) and is_callable( $method ) ) {
			$value = call_user_func( $method, $value, $field );
		}

		return $value;

	}


	/**
	 * todo - the start of breaking field-specific handling out into separate objects
	 *
	 * Dynamic field filter methods. i.e; if a method exists, run it.
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return mixed
	 */
	public function maybe_unprocess_field_value( $value, $field ) {

		$method = [ $this, "unprocess_{$field['type']}_field_value" ];

		if ( method_exists( $method[0], $method[1] ) and is_callable( $method ) ) {
			$value = call_user_func( $method, $field, $value );
		}

		return $value;

	}


	/**
	 * Processing method for inbound 'relationship' field data
	 *
	 * @param $value
	 * @param $field
	 *
	 * @return mixed
	 */
	private function process_relationship_field_value( $value, $field ) {

		if ( isset( $field['max'] ) and $field['max'] == 1 ) {
			if ( is_array( $value ) ) {
				return $value[0];
			}
		}

		return $value;
	}


	/**
	 * Processing method for inbound 'post object' field data
	 *
	 * @param $value
	 * @param $field
	 *
	 * @return mixed
	 */
	private function process_post_object_field_value( $value, $field ) {

		if ( ! isset( $field['multiple'] ) or ! $field['multiple'] ) {
			if ( is_array( $value ) ) {
				return $value[0];
			}
		}

		return $value;
	}


}