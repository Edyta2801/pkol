<?php


namespace ACFCustomDatabaseTables\Data;


use WP_Error;


class TableMap {


	/**
	 * @var TableValidator
	 */
	protected $table_validator;


	/**
	 * @var array Table definitions
	 */
	protected $tables = [];


	/**
	 * @var array Table name list e.g;
	 *  [
	 *      0 => 'table_name_1',
	 *      1 => 'table_name_2',
	 *      2 => 'table_name_3',
	 *      4 => 'table_name_4',
	 *  ]
	 */
	protected $table_names = [];


	/**
	 * @var array objects and their associated tables. e.g;
	 *  [
	 *      'user' => [ 0, 1, 2 ],
	 *      'post' => [ 3 ],
	 *  ]
	 */
	protected $types = [];


	/**
	 * @var array Post types and their associated tables. e.g;
	 *  [
	 *      'post'        => [ 0, 2 ],
	 *      'page'        => [ 1, 4 ],
	 *      'custom_type' => [ 3 ]
	 *  ]
	 */
	protected $post_types = [];


	/**
	 * @var array ACF field names and their associated tables. This map needs a little more complexity to allow for the
	 *            same ACF field names to be used in multiple places. e.g;
	 *  [
	 *      'user' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:post' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:page' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ],
	 *      'post:post_type' => [
	 *          'field_name1' => [ 2 ],
	 *          'field_name2' => [ 3, 4 ]
	 *      ]
	 *  ]
	 */
	protected $acf_field_names = [];


	/**
	 * @var array ACF Field Names e.g;
	 *  [
	 *      'table1_name__field1_name' => 'column1_name',
	 *      'table1_name__field2_name' => 'column2_name',
	 *      'table2_name__field1_name' => 'column1_name',
	 *      'book_meta__author' => 'author_name',
	 *  ]
	 */
	protected $acf_field_column_names = [];


	/**
	 * @var array join table indexes and their meta tables. e.g;
	 *  [
	 *      1 => [ 0 ],
	 *      2 => [ 0 ],
	 *  ]
	 */
	protected $join_tables = [];


	/**
	 * Note: meta tables are the main tables we create – sub and join are related to meta tables.
	 *
	 * @var array meta table indexes and their child tables. e.g;
	 *  [
	 *      0 => [ 1, 2 ],
	 *      3 => [ 4 ],
	 *  ]
	 */
	protected $meta_tables = [];


	/**
	 * TableMap constructor.
	 *
	 * @param TableValidator $table_validator
	 */
	public function __construct( TableValidator $table_validator ) {
		$this->table_validator = $table_validator;
	}


	/**
	 * Adds/updates table to the map. This also validates and normalises table args first.
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error
	 */
	public function add_table( Array $args ) {

		$args       = $this->table_validator->normalise_args( $args );
		$validation = $this->table_validator->validate_args( $args );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$this->parse_table_args( $args );

		return true;
	}


	/**
	 * Returns the index of the required table name for reference in other data points
	 *
	 * @param $name
	 *
	 * @return int|WP_Error
	 */
	public function get_table_name_index( $name ) {
		$flipped = array_flip( $this->table_names );
		if ( isset( $flipped[ $name ] ) ) {
			return $flipped[ $name ];
		} else {
			return new WP_Error( 'acfcdt', "Missing TableMap::table_names entry for table $name" );
		}
	}


	/**
	 * Picks through the table args and assigns relevant data to properties for easy lookup
	 *
	 * Note: if the table def already exists and is being registered again, some issues may arise where the table name
	 * index appears where it should no longer be. e.g; if a table array is registered with one post type and then the
	 * array is changed and passed through here again, the original post_type array will still contain the table name
	 * index. This should really be a problem for us, but if it comes up, we'll need a mechanism to rebuild all reference
	 * properties on this object. Easiest way would be to simply take a copy of the tables prop, clear all props, then
	 * run all tables through this method again. Could be room for a refactor here, but not essential at this stage.
	 *
	 * @param array $args
	 */
	public function parse_table_args( Array $args ) {
		$this->register_table_config( $args );
		$this->register_table_name( $args );
		$this->register_objects( $args );
		$this->register_post_types( $args );
		$this->register_acf_field_names( $args );
		$this->register_join_tables( $args );
	}


	/**
	 * Loops through the 'join_tables' param of a table object, registers the join tables within, and sets up join table
	 * related mappings.
	 *
	 * @param array $args
	 */
	public function register_join_tables( Array $args ) {

		if ( isset( $args['join_tables'] ) ) {
			foreach ( $args['join_tables'] as $join_table_args ) {

				// todo - think this through properly and make sure this is fine
				$join_table_args['relationship'] = $args['relationship'];

				// todo - consider abstracting out into $this->add_join_table();
				$join_table_args['type'] = 'join'; // maybe move this to table normalisation instead?
				$this->add_table( $join_table_args );

				// register the relationship between tables
				$parent_index = $this->get_table_name_index( $args['name'] );
				$child_index  = $this->get_table_name_index( $join_table_args['name'] );

				// map child to parent
				isset( $this->join_tables[ $child_index ] ) or $this->join_tables[ $child_index ] = [];
				$this->join_tables[ $child_index ][] = $parent_index;

				// map parent to child
				isset( $this->meta_tables[ $parent_index ] ) or $this->meta_tables[ $parent_index ] = [];
				$this->meta_tables[ $parent_index ][] = $child_index;

			}
		}
	}


	/**
	 * Returns entire table map
	 *
	 * @param null|string $property If provided and if property exists, returns just the value of the property instead
	 *                              of the whole map.
	 *
	 * @return array
	 */
	public function get_map( $property = null ) {

		if ( $property ) {
			return property_exists( $this, $property )
				? $this->$property :
				[];
		}

		return [
			'tables'                 => $this->tables,
			'table_names'            => $this->table_names,
			'types'                  => $this->types,
			'post_types'             => $this->post_types,
			'join_tables'            => $this->join_tables,
			'meta_tables'            => $this->meta_tables,
			'acf_field_names'        => $this->acf_field_names,
			'acf_field_column_names' => $this->acf_field_column_names,
		];
	}


	/**
	 * Accepts a multi-dimensional array containing all map keys and data. This is for use when we want to bypass
	 * parsing data. e.g; when picking up a cached map array.
	 *
	 * @param array $map
	 *
	 * @return $this
	 */
	public function set_map( Array $map ) {
		$this->tables                 = isset( $map['tables'] ) ? $map['tables'] : [];
		$this->table_names            = isset( $map['table_names'] ) ? $map['table_names'] : [];
		$this->types                  = isset( $map['types'] ) ? $map['types'] : [];
		$this->post_types             = isset( $map['post_types'] ) ? $map['post_types'] : [];
		$this->join_tables            = isset( $map['join_tables'] ) ? $map['join_tables'] : [];
		$this->meta_tables            = isset( $map['meta_tables'] ) ? $map['meta_tables'] : [];
		$this->acf_field_names        = isset( $map['acf_field_names'] ) ? $map['acf_field_names'] : [];
		$this->acf_field_column_names = isset( $map['acf_field_column_names'] ) ? $map['acf_field_column_names'] : [];

		return $this;
	}


	/**
	 * Resets the map for rebuild
	 */
	public function reset() {
		$this->tables                 = [];
		$this->table_names            = [];
		$this->types                  = [];
		$this->post_types             = [];
		$this->join_tables            = [];
		$this->meta_tables            = [];
		$this->acf_field_names        = [];
		$this->acf_field_column_names = [];
	}


	/**
	 * @param array $args
	 *
	 * @return bool
	 */
	public function is_already_registered( Array $args ) {
		return isset( $this->tables[ $args['name'] ] );
	}


	/**
	 * Adds table config args to the tables prop
	 *
	 * @param array $args
	 */
	public function register_table_config( Array $args ) {
		$name                  = $args['name'];
		$this->tables[ $name ] = $this->is_already_registered( $args )
			? wp_parse_args( $args, $this->tables[ $name ] )
			: $args;
	}


	/**
	 * Adds the table name to the table_names prop if not already there
	 *
	 * @param array $args
	 *
	 * @return int The index of the table name
	 */
	public function register_table_name( Array $args ) {
		$name            = $args['name'];
		$already_in_list = false;

		if ( $this->table_names ) {
			$flipped         = array_flip( $this->table_names );
			$already_in_list = isset( $flipped[ $name ] );
		}

		if ( ! $already_in_list ) {
			$this->table_names[] = $name;
		}

		return $this->get_table_name_index( $name );
	}


	/**
	 * Adds date to the objects prop
	 *
	 * @param array $args
	 */
	public function register_objects( Array $args ) {
		$object = $args['relationship']['type'];
		isset( $this->types[ $object ] ) or $this->types[ $object ] = [];
		$this->types[ $object ][] = $this->get_table_name_index( $args['name'] );
	}


	/**
	 * Adds data to the post_types prop
	 *
	 * @param array $args
	 */
	public function register_post_types( Array $args ) {
		$object    = $args['relationship']['type'];
		$post_type = isset( $args['relationship']['post_type'] )
			? $args['relationship']['post_type']
			: '';

		if ( $object === 'post' and $post_type ) {
			isset( $this->post_types[ $post_type ] ) or $this->post_types[ $post_type ] = [];
			$this->post_types[ $post_type ][] = $this->get_table_name_index( $args['name'] );
		}
	}


	/**
	 * Adds data to the acf_field_names prop
	 *
	 * @param array $args
	 */
	public function register_acf_field_names( Array $args ) {
		$object = $args['relationship']['type'];

		if ( $object === 'post' ) {

			$post_type = ( isset( $args['relationship']['post_type'] ) and $args['relationship']['post_type'] )
				? $args['relationship']['post_type']
				: 'post';

			$object = "post:$post_type";

		}

		isset( $this->acf_field_names[ $object ] ) or $this->acf_field_names[ $object ] = [];

		foreach ( $args['columns'] as $column_args ) {

			if ( ! isset( $column_args['map']['type'] ) ) {
				continue;
			}

			if ( $column_args['map']['type'] === 'acf_field_name' ) {

				$table_name     = $args['name'];
				$column_name    = $column_args['name'];
				$acf_field_name = $column_args['map']['identifier'];

				$this->acf_field_names[ $object ][ $acf_field_name ][] = $this->get_table_name_index( $table_name );

				// if col.name doesn't match col.map.identifier, map the identifier
				if ( $column_name !== $acf_field_name ) {

					$key = $this->build_acf_field_column_name_key( $table_name, $acf_field_name );

					$this->acf_field_column_names[ $key ] = $column_name;
				}
			}
		}
	}


	/**
	 * @param array $indexes
	 *
	 * @return array
	 */
	private function get_all_table_names_for_index_array( Array $indexes ) {

		$table_names = [];

		foreach ( $indexes as $index ) {
			if ( isset( $this->table_names[ $index ] ) ) {
				$table_names[] = $this->table_names[ $index ];
			}
		}

		return $table_names;
	}


	/**
	 * @param string $post_type
	 *
	 * @return array table names
	 */
	public function locate_all_tables_by_post_type( $post_type ) {

		return isset( $this->post_types[ $post_type ] )
			? $this->get_all_table_names_for_index_array( $this->post_types[ $post_type ] )
			: [];
	}


	/**
	 * @return array
	 */
	public function locate_all_user_tables() {

		return isset( $this->types['user'] )
			? $this->get_all_table_names_for_index_array( $this->types['user'] )
			: [];

	}


	/**
	 * This takes an ACF field name and some optional context information to find the name of the table that is storing
	 * that field.
	 *
	 * USAGE:
	 *  - $table_map->locate_table_by_acf_field_name( $field_name ); // assume post
	 *  - $table_map->locate_table_by_acf_field_name( $field_name, 'post' ); // explicit post object type (default)
	 *  - $table_map->locate_table_by_acf_field_name( $field_name, 'user' ); // explicit post object type (default)
	 *  - $table_map->locate_table_by_acf_field_name( $field_name, 'post:my_post_type' ); // explicit post object type with custom post type (default)
	 *
	 * @param string $field_name The ACF Field name
	 * @param string $context Contextual helper
	 *
	 * @return bool|string False if not found, table name on success
	 */
	public function locate_table_by_acf_field_name( $field_name, $context = 'post' ) {
		$return  = false;
		$context = $this->normalise_context( $context );

		// if field is registered under context
		if ( isset( $this->acf_field_names[ $context ][ $field_name ] ) ) {
			/**
			 * Get the table index. Let's just get the first for now. We've allowed for multiple, but I don't believe
			 * ACF supports multiple fields with the same name in the same context, so this should be fine (I think).
			 */
			$i = $this->acf_field_names[ $context ][ $field_name ][0];

			// return table name, if it is registered
			if ( isset( $this->table_names[ $i ] ) ) {
				$return = $this->table_names[ $i ];
			}
		}

		return $return;
	}


	/**
	 * Finds the column name for an ACF field name in a given table, if an alternative column name has been mapped. If
	 * no mapping can be found, the field name is returned as that will be the name of the column.
	 *
	 * @param $table_name
	 * @param $acf_field_name
	 *
	 * @return mixed
	 */
	public function locate_column_name_by_acf_field_name( $table_name, $acf_field_name ) {

		$key = $this->build_acf_field_column_name_key( $table_name, $acf_field_name );

		if ( isset( $this->acf_field_column_names[ $key ] ) ) {
			return $this->acf_field_column_names[ $key ];
		}

		// no mapping? Just return the field name
		return $acf_field_name;
	}


	private function build_acf_field_column_name_key( $table_name, $acf_field_name ) {
		return "{$table_name}.{$acf_field_name}";
	}


	/**
	 * TODO - needs reviewing and testing – VERY ROUGHLY SMASHED IN HERE
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function is_join_table( $table_name ) {
		if ( is_wp_error( $index = $this->get_table_name_index( $table_name ) ) ) {
			return false;
		}

		return isset( $this->join_tables[ $index ] );
	}


//	public function locate_table_by_column_name( $column_name, $context = 'post' ) {
//		$return = false;
//
//		$context = $this->normalise_context( $context );
//
//	    // …
//
//		return $return;
//	}


	// maybe
	//public function locate_table_by_acf_field_key( $field_key ) {
	//}


	/**
	 * @param $context
	 *
	 * @return string
	 */
	private function normalise_context( $context ) {
		if ( $context === 'post' ) {
			$context = 'post:post';
		} elseif ( $context === 'page' ) {
			$context = 'post:page';
		}

		return $context;
	}


}