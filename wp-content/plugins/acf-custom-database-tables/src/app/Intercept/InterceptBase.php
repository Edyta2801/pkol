<?php


namespace ACFCustomDatabaseTables\Intercept;


use ACFCustomDatabaseTables\Contract\InterceptInterface;
use ACFCustomDatabaseTables\Coordinator\InterceptCoordinator;


abstract class InterceptBase implements InterceptInterface {


	/** @var  InterceptCoordinator */
	protected $coordinator;


	/**
	 * This dependency needs to be injected via a method at this time as intercepts are registered with the
	 * InterceptCoordinator which injects itself in order to act a little like a mediator.
	 *
	 * @see \ACFCustomDatabaseTables\Coordinator\InterceptCoordinator::register_intercept()
	 *
	 * @param InterceptCoordinator $coordinator
	 */
	public function set_intercept_coordinator( InterceptCoordinator $coordinator ) {
		$this->coordinator = $coordinator;
	}


	/**
	 * todo - we should really make the context an object
	 *
	 * @param string $object_type post|user
	 * @param int $object_id
	 *
	 * @return string
	 */
	public function get_object_context( $object_type, $object_id ) {

		$context = '';

		switch ( $object_type ) {
			case 'user':
				$context = 'user';
				break;
			case 'post':
				$context = 'post:' . get_post_type( $object_id );
		}

		return $context;
	}


}