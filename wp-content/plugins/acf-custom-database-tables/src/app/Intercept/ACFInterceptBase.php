<?php


namespace ACFCustomDatabaseTables\Intercept;


use ACFCustomDatabaseTables\Coordinator\CoreMetadataCoordinator;
use ACFCustomDatabaseTables\Service\ACFFieldSupportManager;


abstract class ACFInterceptBase extends InterceptBase {


	/** @var ACFFieldSupportManager */
	protected $field_support_manager;


	/** @var CoreMetadataCoordinator */
	protected $core_metadata_coordinator;


	/**
	 * ACFInterceptBase constructor.
	 *
	 * @param ACFFieldSupportManager $field_support_manager
	 * @param CoreMetadataCoordinator $core_metadata_coordinator
	 */
	public function __construct( ACFFieldSupportManager $field_support_manager, CoreMetadataCoordinator $core_metadata_coordinator ) {
		$this->field_support_manager     = $field_support_manager;
		$this->core_metadata_coordinator = $core_metadata_coordinator;
	}


	/**
	 * todo - candidate for abstraction to an ACF Field specific system where each field has its own object
	 *
	 * Processes complex fields before input. At the moment, this just JSON encodes data. We will, however, add the
	 * option to serialise data if preferred, or even break some fields out into join tables (such as repeaters).
	 *
	 * @param $value
	 * @param array $field The ACF field object array
	 *
	 * @return string
	 */
	public function maybe_process_field_value( $value, $field ) {

		$value = $this->field_support_manager->maybe_process_field_value( $value, $field );

		if ( is_object( $value ) ) {
			$value = (array) $value;
		}

		return $this->maybe_encode( $value, $field );
	}


	/**
	 * todo - candidate for abstraction to an ACF Field specific system where each field has its own object
	 *
	 * Processes complex fields before output.
	 *
	 * See \ACFCustomDatabaseTables\Intercept\ACFInterceptBase::maybe_process_field_value()
	 *
	 * @param $value
	 * @param $field
	 *
	 * @return array|mixed|object
	 */
	public function maybe_unprocess_field_value( $value, $field ) {

		$value = $this->field_support_manager->maybe_unprocess_field_value( $value, $field );

		if ( ! is_string( $value ) or $value === '' ) {
			return $value;
		}

		$decoded = $this->maybe_decode( $value, $field );

		$value = is_object( $decoded )
			? (array) $decoded
			: $decoded;

		return $value;
	}


	/**
	 * Calls on ACFs acf_get_post_id_info() to process the selector and pads out additional info we need to query for
	 * custom tables.
	 *
	 * @param $acf_selector
	 *
	 * @return array
	 */
	public function get_selector_info( $acf_selector ) {

		$info            = acf_get_post_id_info( $acf_selector );
		$info['context'] = $this->get_object_context( $info['type'], $info['id'] );

		return $info;
	}


	/**
	 * @param $value
	 * @param $field
	 *
	 * @return string
	 */
	private function maybe_encode( $value, $field ) {

		/**
		 * Filters the value before it is encoded and saved in the database. This filter makes it possible to:
		 *  1. Convert string-based ints to actual integers for cleaner JSON encoded data, when $value is an array. e.g; [1,2,3] instead of ["1","2","3"]
		 *  2. Control how non-scalar values are actually stored in the database by converting them to a string other than the default encoded JSON.
		 *
		 * @param mixed $value The value to be saved in the database
		 * @param array $field The ACF field array
		 */
		$value = apply_filters( 'acfcdt/filter_value_before_encode', $value, $field );

		if ( is_string( $value ) ) {
			return $value;
		}

		$encoded = false;

		if ( is_array( $value ) || is_object( $value ) ) {
			$encoded = json_encode( $value, JSON_UNESCAPED_SLASHES );
		}

		return ( $encoded !== false )
			? $encoded
			: $value;
	}


	/**
	 * @param $value
	 *
	 * @return array|mixed
	 *
	 */
	private function maybe_decode( $value, $field ) {

		/**
		 * Filters the value before after it is read from the database and before it is decoded and returned. This filter makes it possible to decode any custom encoding applied
		 * via the `acfcdt/filter_value_before_encode` filter.
		 *
		 * @param string $value The value that was read from the database
		 * @param array $field The ACF field array
		 */
		$value = apply_filters( 'acfcdt/filter_value_before_decode', $value, $field );

		if ( is_string( $value ) ) {
			$decoded     = json_decode( $value, true );
			$was_encoded = is_array( $decoded ) && ( json_last_error() === JSON_ERROR_NONE );
			if ( $was_encoded ) {
				$value = $decoded;
			}
		}

		return $value;
	}


}