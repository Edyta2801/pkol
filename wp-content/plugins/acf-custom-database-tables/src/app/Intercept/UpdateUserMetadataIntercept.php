<?php


namespace ACFCustomDatabaseTables\Intercept;


class UpdateUserMetadataIntercept extends UpdateMetadataInterceptBase {


	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		add_filter( "update_user_metadata", [ $this, 'update_user_metadata' ], 10, 5 );
	}


	/**
	 * @param null|bool $bypass_core_meta Default 'null', allows meta to hit meta table. Any non null value here will
	 *                                    prevent the data from being stored in the core meta table.
	 * @param $user_id
	 * @param $meta_key
	 * @param $meta_value
	 * @param $prev_value
	 *
	 * @return mixed
	 */
	public function update_user_metadata( $bypass_core_meta, $user_id, $meta_key, $meta_value, $prev_value ) {

		if ( $this->is_exempt_field( $meta_key ) ) {
			return $bypass_core_meta;
		}

		if ( $this->core_metadata_coordinator->field_is_bypassed_from_core_meta_tables( $meta_key ) ) {
			$bypass_core_meta = true;
		}

		return $bypass_core_meta;
	}


}