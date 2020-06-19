<?php


namespace ACFCustomDatabaseTables\Factory;


use ACFCustomDatabaseTables\Model\ACFFieldGroup;


class ACFFieldGroupFactory {


	public function make_from_post_id( $post_id ) {
		return new ACFFieldGroup( get_post( $post_id ) );
	}


	public function make_from_post_object( \WP_Post $post ) {
		return new ACFFieldGroup( $post );
	}


	// TODO - consider whether we would ever need to throw or trigger here (could get_post() fail?)
	public function make_from_field_group_array( Array $field_group ) {
		return new ACFFieldGroup( get_post( $field_group['ID'] ) );
	}


}