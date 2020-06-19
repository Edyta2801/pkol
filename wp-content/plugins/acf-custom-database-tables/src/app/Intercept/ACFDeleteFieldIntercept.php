<?php


namespace ACFCustomDatabaseTables\Intercept;


class ACFDeleteFieldIntercept extends ACFInterceptBase {


	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		add_action( 'acf/delete_value', [ $this, 'delete_value' ], 10, 3 );
	}


	/**
	 * @param $selector
	 * @param $field_name
	 * @param $field
	 */
	public function delete_value( $selector, $field_name, $field ) {

		if ( ! $this->field_support_manager->is_supported( $field ) ) {
			return;
		}

		$info = $this->get_selector_info( $selector );
		if ( ! $this->coordinator->has_table( $field['name'], $info['context'] ) ) {
			return;
		}

		$this->coordinator->update( $field['name'], '', $info['context'], $info['id'] );

	}


}