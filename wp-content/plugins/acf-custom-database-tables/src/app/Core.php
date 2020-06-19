<?php


namespace ACFCustomDatabaseTables;


use ACFCustomDatabaseTables\Contract\ControllerInterface;
use ACFCustomDatabaseTables\Contract\ModuleInterface;
use ACFCustomDatabaseTables\Coordinator\TableCoordinator;
use ACFCustomDatabaseTables\Utils\View;


class Core {


	/** @var  Container */
	protected $container;


	/**
	 * Core constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}


	/**
	 * Runs all init routines
	 */
	public function init() {
		View::$view_dir = $this->container['view_dir'];
		$this->init_modules();
		$this->init_controllers();
		$this->init_table_map();
		add_action( 'admin_init', [ $this, 'init_updater' ], 0 );
	}


	/**
	 * Loops through all modules defined in our DI container, initialising those that are set to active
	 */
	public function init_modules() {

		/** @var Settings $settings */
		$settings       = $this->container['settings'];
		$active_modules = array_filter( $settings->get( 'activate_modules' ) );

		if ( ! $active_modules ) {
			return;
		}

		$this->container->each( 'module', function ( $object ) use ( $active_modules ) {
			if ( $object instanceof ModuleInterface and isset( $active_modules[ $object->name() ] ) ) {
				$object->init();
			}
		} );
	}


	/**
	 * Loops through all controllers defined in our DI container and calls their init method
	 */
	public function init_controllers() {
		$this->container->each( 'controller', function ( $object ) {
			if ( $object instanceof ControllerInterface ) {
				$object->init();
			}
		} );
	}


	public function init_table_map() {
		/** @var TableCoordinator $coord */
		$coord = $this->container['coordinator.table'];
		$coord->fetch_map_from_cache();
	}


	public function init_updater() {
		$this->container['plugin_updater'];
	}


	/**
	 * @return Container
	 */
	public function container() {
		return $this->container;
	}


}