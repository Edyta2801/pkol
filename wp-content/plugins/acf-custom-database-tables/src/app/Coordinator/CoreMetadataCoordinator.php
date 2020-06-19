<?php


namespace ACFCustomDatabaseTables\Coordinator;


/**
 * Class CoreMetadataCoordinator
 * @package ACFCustomDatabaseTables\Coordinator
 */
class CoreMetadataCoordinator {


	/**
	 * @var array Meta keys that should not make their way into core meta tables
	 */
	private $bypassed_keys = [];


	/**
	 * Mark a meta key for core meta table bypass
	 *
	 * @param $meta_key
	 */
	function bypass_core_meta_tables( $meta_key ) {
		$this->bypassed_keys[] = $meta_key;
	}


	/**
	 * Check whether a meta key has been marked for core meta table bypass
	 *
	 * @param $meta_key
	 *
	 * @return bool
	 */
	function field_is_bypassed_from_core_meta_tables( $meta_key ) {
		$in_bypass_list = ( false !== array_search( $meta_key, $this->bypassed_keys ) );

		return $in_bypass_list;
	}


}