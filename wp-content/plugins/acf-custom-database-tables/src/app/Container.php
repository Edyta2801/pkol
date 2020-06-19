<?php


namespace ACFCustomDatabaseTables;


use \ACFCustomDatabaseTables\Vendor\Pimple;


/**
 * Class Container
 * @package ACFCustomDatabaseTables
 */
class Container extends Pimple\Container {


	/**
	 * Returns a subset of all registered container keys that have a given prefix
	 *
	 * @param $prefix
	 *
	 * @return array
	 */
	public function get_keys_with_prefix( $prefix ) {
		return array_filter( $this->keys(), function ( $key ) use ( $prefix ) {
			return ( 0 === strpos( $key, $prefix ) );
		} );
	}


	/**
	 * Applies a callback to a subset of all registered container dependencies that have keys based on a given key
	 * prefix
	 *
	 * @param $prefix
	 * @param $callback
	 */
	public function each( $prefix, $callback ) {
		$keys = $this->get_keys_with_prefix( $prefix );
		foreach ( $keys as $k ) {
			call_user_func( $callback, $this[ $k ] );
		}
	}


}