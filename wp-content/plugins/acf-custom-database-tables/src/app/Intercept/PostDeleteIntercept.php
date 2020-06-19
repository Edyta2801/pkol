<?php


namespace ACFCustomDatabaseTables\Intercept;


class PostDeleteIntercept extends InterceptBase {


	private $post_type;


	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		add_action( 'before_delete_post', [ $this, 'get_post_type' ] );
		add_action( 'deleted_post', [ $this, 'delete_post_data' ] );
	}


	/**
	 * Establishes necessary data before any post data is deleted
	 *
	 * @param $post_id
	 */
	public function get_post_type( $post_id ) {

		if ( $post_type = get_post_type( $post_id ) ) {
			$this->post_type = $post_type;
		}

	}


	/**
	 * Deletes post data only after post object has been successfully deleted
	 *
	 * @param $post_id
	 */
	public function delete_post_data( $post_id ) {

		if ( $this->post_type ) {
			$this->coordinator->delete_all_data_for_post( $post_id, $this->post_type );
		}
	}


}