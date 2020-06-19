<?php


namespace ACFCustomDatabaseTables\Utils;


class Arr {


	/**
	 * Return the values from a single column in the input array, identified by the $column_key.
	 *
	 * Optionally, an $index_key may be provided to index the values in the returned array by the
	 * values from the $index_key column in the input array.
	 *
	 * This supports objects which was added in PHP 7.0. This method can be dropped when support for PHP 5.x is dropped.
	 *
	 * @param array $input A list of arrays or objects from which to pull a column of values.
	 * @param string|int $column_key The column of values to return.
	 * @param string|int|null $index_key The column to use as the index/keys for the returned array.
	 *
	 * @return array
	 */
	public static function array_column( array $input, $column_key, $index_key = null ) {
		if ( PHP_MAJOR_VERSION > 5 ) {
			return array_column( $input, $column_key, $index_key );
		}
		$output = [];
		foreach ( $input as $row ) {
			$key     = $value = null;
			$key_set = $value_set = false;
			if ( $index_key !== null ) {
				if ( is_array( $row ) && array_key_exists( $index_key, $row ) ) {
					$key_set = true;
					$key     = (string) $row[ $index_key ];
				} elseif ( is_object( $row ) && isset( $row->{$index_key} ) ) {
					$key_set = true;
					$key     = (string) $row->{$index_key};
				}
			}
			if ( $column_key === null ) {
				$value_set = true;
				$value     = $row;
			} elseif ( is_array( $row ) && array_key_exists( $column_key, $row ) ) {
				$value_set = true;
				$value     = $row[ $column_key ];
			} elseif ( is_object( $row ) && isset( $row->{$column_key} ) ) {
				$value_set = true;
				$value     = $row->{$column_key};
			}
			if ( $value_set ) {
				if ( $key_set ) {
					$output[ $key ] = $value;
				} else {
					$output[] = $value;
				}
			}
		}

		return $output;
	}


}