<?php


namespace ACFCustomDatabaseTables\Factory;


use ACFCustomDatabaseTables\Data\ColumnValidator;
use ACFCustomDatabaseTables\DB\DynamicColumnBase;
use ACFCustomDatabaseTables\DB\DynamicColumnBigint;
use ACFCustomDatabaseTables\DB\DynamicColumnLongtext;
use WP_Error;
use wpdb;


class DynamicColumnFactory {


	/** @var wpdb */
	protected $wpdb;

	/** @var  ColumnValidator */
	protected $validator;


	/**
	 * DynamicColumnFactory constructor.
	 *
	 * @param wpdb $wpdb
	 * @param ColumnValidator $column_validator
	 */
	public function __construct( wpdb $wpdb, ColumnValidator $column_validator ) {
		$this->wpdb      = $wpdb;
		$this->validator = $column_validator;
	}


	/**
	 * Makes a DynamicColumn object from an args array
	 *
	 * @param array $args
	 *
	 * @return DynamicColumnBase|WP_Error
	 */
	public function make( Array $args ) {

		$validation = $this->validator->validate_args( $args );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$args = $this->validator->normalise_args( $args );

		switch ( $args['format'] ) {
			case '%d':
				$column = new DynamicColumnBigint( $this->wpdb, $args['name'], $args['format'] );
				break;
			case '%s':
			default:
				$column = new DynamicColumnLongtext( $this->wpdb, $args['name'], $args['format'] );
		}

		! isset( $args['unique'] ) or $column->set_unique( $args['unique'] );
		! isset( $args['auto_increment'] ) or $column->set_auto_increment( $args['auto_increment'] );
		! isset( $args['null'] ) or $column->set_null( $args['null'] );
		! isset( $args['unsigned'] ) or $column->set_unsigned( $args['unsigned'] );
		! isset( $args['default'] ) or $column->set_default_value( $args['default'] );

		return $column;
	}


}