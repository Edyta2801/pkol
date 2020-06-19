<?php


namespace ACFCustomDatabaseTables\DB;


class DynamicColumnBigint extends DynamicColumnBase {


	protected $format = '%d';


	/**
	 * Outputs column schema
	 *
	 * Any specific type of column that extends this object needs to define a schema method that returns SQL that makes
	 * up the `CREATE TABLE …` syntax
	 *
	 * @return string
	 */
	function schema() {

		$name   = $this->name();
		$schema = "`$name` bigint(20)";
		$schema = $this->maybe_append_unsigned( $schema );
		$schema = $this->maybe_append_null( $schema );
		$schema = $this->maybe_append_unique( $schema );
		$schema = $this->maybe_append_auto_increment( $schema );
		$schema = $this->maybe_append_default( $schema );

		return $schema;
	}


}