<?php


namespace ACFCustomDatabaseTables\Intercept;


use ACFCustomDatabaseTables\Coordinator\CoreMetadataCoordinator;


abstract class UpdateMetadataInterceptBase extends InterceptBase {


	/** @var CoreMetadataCoordinator */
	protected $core_metadata_coordinator;


	/**
	 * Any WP core meta field keys that we shouldn't even look at go here.
	 *
	 * @var array
	 */
	private $exempt_fields = [
		'_edit_lock',
		'_edit_last'
	];


	/**
	 * UpdateMetadataInterceptBase constructor.
	 *
	 * @param CoreMetadataCoordinator $core_metadata_coordinator
	 */
	public function __construct( CoreMetadataCoordinator $core_metadata_coordinator ) {
		$this->core_metadata_coordinator = $core_metadata_coordinator;
	}


	/**
	 * Checks whether or not a meta key is exempt from processing.
	 *
	 * @param $meta_key
	 *
	 * @return bool
	 */
	public function is_exempt_field( $meta_key ) {
		return in_array( $meta_key, $this->exempt_fields );
	}


}