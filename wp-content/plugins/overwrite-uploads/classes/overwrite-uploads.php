<?php

/*  
 * Copyright 2011 Ian Dunn (email : ian@iandunn.name)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( basename( $_SERVER['SCRIPT_FILENAME'] ) == basename( __FILE__ ) )
	die( "Access denied." );

/**
 * Overwrites uploaded files that already exist, instead of storing multiple copies.
 */
class OverwriteUploads {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'remove_existing_attachment' ) );    // Not really the appropriate hook, but there isn't an action that fits
	}

	/**
	 * Remove a existing attachment when uploading a new one with the same name in the same folder
	 * 
	 * @param array $file
	 * @return array The unmodified file
	 */
	public function remove_existing_attachment( $file ) {
		$uploads_dir = wp_upload_dir();
		
		if ( file_exists( $uploads_dir['path'] . DIRECTORY_SEPARATOR . $file['name'] ) ) {
			$params = array(
				'numberposts'   => 1,
				'post_type'     => 'attachment',
				'meta_query'    => array(
					array(
						'key'   => '_wp_attached_file',
						'value' => trim( $uploads_dir['subdir'] . DIRECTORY_SEPARATOR . $file['name'], DIRECTORY_SEPARATOR )
					)
				)
			);

			$existing_file = get_posts( $params );
			
			if ( isset( $existing_file[0]->ID ) ) {
				wp_delete_attachment( $existing_file[0]->ID, true );
			}
		}
		
		return $file;
	}
} // end OverwriteUploads
