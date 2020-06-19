<?php


namespace ACFCustomDatabaseTables\Intercept;


/**
 * Class ACFGetFieldIntercept
 * @package ACFCustomDatabaseTables\Intercept
 */
class ACFGetFieldIntercept extends ACFInterceptBase {


	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		add_filter( 'acf/load_reference', [ $this, 'maybe_get_local_field_reference' ], 15, 3 );
		add_filter( 'acf/pre_load_value', [ $this, 'fetch_value' ], 10, 3 );
	}


	/**
	 * On get_field(), ACF doesn't go on to check local JSON files in order to get a field_key. This hooked filter
	 * handles that by explicitly checking local JSON files in the acf_get_field_reference() function where a field_key
	 * hasn't already been established from the DB.
	 *
	 * @param $field_key
	 * @param $field_name
	 * @param $post_id
	 *
	 * @return mixed
	 */
	function maybe_get_local_field_reference( $field_key, $field_name, $post_id ) {

		if ( $field_key ) {
			return $field_key;
		}

		if ( acf_is_local_field( $field_name ) and $field = acf_get_local_field( $field_name ) ) {
			return $field['key'];
		}

		return $field_key;
	}


	/**
	 * Intercepts the acf/pre_load_value filter and pulls data from custom tables, if a table exists containing the
	 * field.
	 *
	 * @param $null
	 * @param $selector
	 * @param $field
	 *
	 * @return array|mixed|null|object|\WP_Error
	 */
	public function fetch_value( $null, $selector, $field ) {

		if ( ! $this->field_support_manager->is_supported( $field ) ) {
			return $null;
		}

		$info = $this->get_selector_info( $selector );

		if ( ! $this->coordinator->has_table( $field['name'], $info['context'] ) ) {
			return $null;
		}

		$value = $this->coordinator->find( $field['name'], $info['context'], $info['id'] );

		if ( $value === null ) {
			return $null;
		}

		if ( is_wp_error( $value ) ) {
			trigger_error( $value->get_error_message() );

			return $null;
		}

		$value = $this->coordinator->is_join_table( $field['name'], $info['context'] )
			? $value
			: $this->maybe_unprocess_field_value( $value, $field );

		$value = $this->apply_acf_filters( $value, $selector, $field );

		return $value;
	}


	/**
	 * Supporting ACFs built in 3rd party extension filters. This will allow developers to make the same modifications
	 * to the outbound data after it is retrieved from custom DB tables.
	 *
	 * @see \acf_get_value()
	 *
	 * @param $value
	 * @param $selector
	 * @param $field
	 *
	 * @return mixed
	 */
	private function apply_acf_filters( $value, $selector, $field ) {

		$value = maybe_unserialize( $value );

		// no value? try default_value
		if ( $value === null && isset( $field['default_value'] ) ) {
			$value = $field['default_value'];
		}

		$allow_filters = $this->coordinator->settings()->get( 'allow_acf_load_value_filters' );

		if ( ! $allow_filters ) {
			return $value;
		}

		// filter for 3rd party customization
		$value = apply_filters( "acf/load_value", $value, $selector, $field );
		$value = apply_filters( "acf/load_value/type={$field['type']}", $value, $selector, $field );
		$value = apply_filters( "acf/load_value/name={$field['_name']}", $value, $selector, $field );
		$value = apply_filters( "acf/load_value/key={$field['key']}", $value, $selector, $field );

		return $value;
	}


}