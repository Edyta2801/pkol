<?php


namespace ACFCustomDatabaseTables\Intercept;


/**
 * Class ACFUpdateFieldIntercept
 * @package ACFCustomDatabaseTables\Intercept
 *
 * Hooks into ACFs acf/update_value filter to intercept the meta data.
 */
class ACFUpdateFieldIntercept extends ACFInterceptBase {


	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		add_filter( "acf/pre_update_value", [ $this, 'update_value' ], 11, 4 );
	}


	/**
	 * Hooked method that intercepts the data being processed by ACF and saves it into a custom table if a custom table
	 * has been defined for the data. If no table found, the data is passed through and left to be handled by ACF as
	 * per usual.
	 *
	 * @param $null
	 * @param $value
	 * @param $selector
	 * @param $field
	 *
	 * @return null
	 */
	public function update_value( $null, $value, $selector, $field = [] ) {

		if ( ! $this->field_support_manager->is_supported( $field ) ) {
			return $null;
		}

		$value = $this->apply_acf_filters( $value, $selector, $field );

		$updated                = $this->_update_value( $value, $selector, $field );
		$stored_in_custom_table = ( false !== $updated );

		if ( $stored_in_custom_table ) {

			$store_values_in_core = $this->coordinator->settings()->get( 'store_acf_values_in_core_meta' );
			$store_keys_in_core   = $this->coordinator->settings()->get( 'store_acf_keys_in_core_meta' );

			if ( ! $store_values_in_core ) {
				$this->core_metadata_coordinator->bypass_core_meta_tables( $field['name'] );
			}

			// note: a check for whether local JSON is in use could be a good thing here
			if ( ! $store_keys_in_core ) {
				$this->core_metadata_coordinator->bypass_core_meta_tables( '_' . $field['name'] );
			}
		}

		// always allow through
		return $null;
	}


	/**
	 * The bulk of the intercept handling. This has been pulled into a separate method to save on duplicated code while
	 * supporting earlier versions of ACF.
	 *
	 * @param $value
	 * @param $selector
	 * @param $field
	 *
	 * @return bool TRUE on success, FALSE on failure
	 */
	private function _update_value( $value, $selector, $field ) {

		$info = $this->get_selector_info( $selector );

		if ( ! $this->coordinator->has_table( $field['name'], $info['context'] ) ) {
			return false;
		}

		$input = $this->coordinator->is_join_table( $field['name'], $info['context'] )
			? $value
			: $this->maybe_process_field_value( $value, $field );

		$input = wp_unslash( $input );

		$updated = $this->coordinator->update( $field['name'], $input, $info['context'], $info['id'] );

		if ( is_wp_error( $updated ) ) {
			trigger_error( 'Custom table was not updated. Error: ' . $updated->get_error_message() ); // todo - consider adding admin notifier support here

			return false;
		}

		return true;
	}


	/**
	 * Supporting ACFs built in 3rd party extension filters. This will allow developers to make the same modifications
	 * to the inbound data before it hits custom tables.
	 *
	 * @see \acf_update_value()
	 *
	 * @param $value
	 * @param $selector
	 * @param $field
	 *
	 * @return mixed
	 */
	private function apply_acf_filters( $value, $selector, $field ) {

		$allow_filters = $this->coordinator->settings()->get( 'allow_acf_update_value_filters' );

		if ( ! $allow_filters ) {
			return $value;
		}

		// filter for 3rd party customization
		$value = apply_filters( "acf/update_value", $value, $selector, $field );
		$value = apply_filters( "acf/update_value/type={$field['type']}", $value, $selector, $field );
		$value = apply_filters( "acf/update_value/name={$field['_name']}", $value, $selector, $field );
		$value = apply_filters( "acf/update_value/key={$field['key']}", $value, $selector, $field );

		return $value;
	}


}