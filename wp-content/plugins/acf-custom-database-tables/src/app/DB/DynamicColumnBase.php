<?php


namespace ACFCustomDatabaseTables\DB;


use wpdb;


// TODO - consider validating/formatting name either on constructor or name() method
abstract class DynamicColumnBase {


	protected $name;
	protected $format = '%s';
	protected $default_value;
	protected $has_default_value = false;
	protected $unsigned;
	protected $null;
	protected $auto_increment;
	protected $unique;


	/** @var wpdb */
	protected $wpdb;


	/**
	 * Outputs column schema
	 *
	 * Any specific type of column that extends this object needs to define a schema method that returns SQL that makes
	 * up the `CREATE TABLE …` syntax.
	 *
	 * This method should also pass schema to the @see maybe_append_default($schema) default method so that default
	 * value can be set, if required.
	 *
	 * @return string
	 */
	abstract function schema();


	/**
	 * DynamicColumnBase constructor.
	 *
	 * @param string $name Column name
	 * @param string $format WP Supported data format; %s|%d|%f
	 */
	public function __construct( wpdb $wpdb, $name, $format = null ) {
		$this->wpdb = $wpdb;
		$this->name = $name;
		if ( $format ) {
			$this->format = $format;
		}
	}


	/**
	 * @return bool
	 */
	public function has_default_value() {
		return (bool) $this->has_default_value;
	}


	/**
	 * @param $default
	 */
	public function set_default_value( $default ) {
		$this->has_default_value = true;
		$this->default_value     = strval( $default );
	}


	public function set_unsigned( $bool = true ) {
		$this->unsigned = $bool;
	}


	public function set_null( $bool_or_null = null ) {
		$this->null = $bool_or_null;
	}


	public function set_auto_increment( $bool = true ) {
		$this->auto_increment = $bool;
	}


	public function set_unique( $bool = true ) {
		$this->unique = $bool;
	}


	/**
	 * @return mixed
	 */
	public function default_value() {
		return $this->default_value;
	}


	/**
	 * @return string
	 */
	public function format() {
		return $this->format;
	}


	/**
	 * @return string
	 */
	public function name() {
		return $this->name;
	}


	protected function maybe_append_unsigned( $schema ) {
		if ( $this->unsigned === true ) {
			$schema .= " unsigned";
		}

		return $schema;
	}


	protected function maybe_append_null( $schema ) {
		if ( $this->null === true ) {
			$schema .= " NULL";
		} elseif ( $this->null === false ) {
			$schema .= " NOT NULL";
		}

		return $schema;
	}


	protected function maybe_append_unique( $schema ) {
		if ( $this->unique === true ) {
			$schema .= " UNIQUE";
		}

		return $schema;
	}


	protected function maybe_append_auto_increment( $schema ) {
		if ( $this->auto_increment === true ) {
			$schema .= " auto_increment";
		}

		return $schema;
	}


	protected function maybe_append_default( $schema ) {

		if ( $this->auto_increment === true or $this->unique === true ) {
			return $schema;
		}

		if ( $this->has_default_value() ) {
			$f      = $this->format();
			$d      = $this->default_value();
			$schema .= $this->wpdb->prepare( " default $f", $d );
		}

		return $schema;
	}


}