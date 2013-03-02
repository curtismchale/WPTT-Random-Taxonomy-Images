<?php
/*
Plugin Name: Random Taxonomy Images
Plugin URI:
Description: Allows you to set images for taxonomies
Version: 1.0
Author: WP Theme Tutorial, Curtis McHale
Author URI: http://wpthemetutorial.com
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class WPTT_Tax_Image{

	function __construct(){

		add_action( 'category_add_form_fields', array( $this, 'add_tax_image_field' ) );
		add_action( 'category_edit_form_fields', array( $this, 'edit_tax_image_field' ) );
		// saving
		add_action( 'edited_category', array( $this, 'save_tax_meta' ), 10, 2 );
		add_action( 'create_category', array( $this, 'save_tax_meta' ), 10, 2 );


	} // __construct

	/**
	 * Echos our header image.
	 *
	 * @since 1.1
	 * @author WP Theme Tutorial
	 * @access public
	 *
	 * @uses $this->get_header_image()     Gets the header image HTML
	 */
	public function show_header_image(){
		echo $this->get_header_image();
	} // show_header_image

	/**
	 * Returns the HTML to display our taxonomy image
	 *
	 * @since 1.1
	 * @author WP Theme Tutorial, Curtis McHale
	 * @access public
	 *
	 * @uses is_archive()                Returns true if we are on an archive page
	 * @uses $this->term_has_image()     Returns true if term has image
	 * @uses get_option()                Gets option from the DB given key
	 * @uses esc_url()                   Makes sure we have a safe URL
	 * @uses $this->get_random_image()   Gets a random image from our theme folder
	 */
	public function get_header_image(){

		// exit early if we don't have what we want
		if ( ! is_archive() ) return;

		if ( $this->term_has_image() ){

			global $wp_query;

			$term_id = $wp_query->queried_object_id;

			$term_meta = get_option( "taxonomy_$term_id" );

			$url = $term_meta['tax_image'];

		} else {
			$url = $this->get_random_image();
		}

		return '<img src="'. esc_url( $url ) .'" />';

	} // get_header_image

	/**
	 * Returns true if the term has a valid URL in the tax_image meta field
	 *
	 * @since 1.1
	 * @author WP Theme Tutorial, Curtis McHale
	 * @access private
	 *
	 * @uses get_option()     Gets option from the DB given key
	 */
	private function term_has_image(){

		global $wp_query;

		$term_id = $wp_query->queried_object_id;

		$term_meta = get_option( "taxonomy_$term_id" );

		if( ! empty( $term_meta['tax_image'] ) ) return true;

		return false;

	} // term_has_image

	/**
	 * Gets a random category header image
	 *
	 * @since 1.2
	 * @author  WP Theme Tutorial, Curtis McHale
	 * @access public
	 *
	 * @uses get_template_directory()		Returns the file path to the currently active parent theme
	 * @uses $this->make_file_path_uri()	Turns the file path in to a URI for the image HTML
	 */
	private function get_random_image(){

		$path = get_stylesheet_directory() . '/random/';

		$images =  glob( $path . '*.{jpg}', GLOB_BRACE );

		$random_image = $images[ array_rand( $images ) ];

		$random_image = $this->make_file_path_uri( $random_image );

		return $random_image;

	} // get_random_image

	/**
	 * Changes a filepath in to a URI
	 *
	 * @param   string  $file_path  req     The filepath for our image
	 * @return  string  $uri                The URI determined from the provided filepath
	 *
	 * @uses pathinfo()                         Returns array of information about our filepath
	 * @uses get_stylesheet_directory_uri()     Returns the URI for the stylesheet director
	 */
	private function make_file_path_uri( $file_path ){

		$path_info = pathinfo( $file_path );

		$uri = get_stylesheet_directory_uri() . '/random/' . $path_info["basename"];

		return $uri;

	} // make_file_path_uri

	/**
	 * Adds extra meta fields when you are adding a new taxonomy term
	 *
	 * @since 1.0
	 * @author WP Theme Tutorial, Curtis McHale
	 * @access public
	 */
	public function add_tax_image_field(){
	?>
		<div class="form-field">
			<label for="term_meta[tax_image]">Taxonomy Image</label>
			<input type="text" name="term_meta[tax_image]" id="term_meta[tax_image]" value="" />
			<p class="description">Add URL to image for the taxonomy image</p>
		</div><!-- /.form-field -->
	<?php
	} // add_tax_image_field

	/**
	 * Adds extra meta fields when you are editing a taxonomy term
	 *
	 * @since 1.0
	 * @author WP Theme Tutorial, Curtis McHale
	 * @access public
	 *
	 * @uses get_option()       Returns option from the DB given string
	 * @uses esc_url()          Makes sure I have a safe URL
	 */
	public function edit_tax_image_field( $term ){
		$term_id = $term->term_id;
		$term_meta = get_option( "taxonomy_$term_id" );
		$image = $term_meta['tax_image'] ? $term_meta['tax_image'] : '';
	?>
		<tr class="form-field">
			<th scope="row">
				<label for="term_meta[tax_image]">Taxonomy Image</label>
				<td>
					<input type="text" name="term_meta[tax_image]" id="term_meta[tax_image]" value="<?php echo esc_url( $image ); ?>" />
					<p class="description">Add URL to image for the taxonomy image</p>
				</td>
			</th>
		</tr><!-- /.form-field -->
	<?php
	} // edit_tax_image_field

	/**
	 * Does the saving for our extra taxonomy meta field
	 *
	 * @since 1.0
	 * @author WP Theme Tutorial, Curtis McHale
	 * @access public
	 *
	 * @param   int     $term_id    req     The id of the term we are saving
	 *
	 * @uses get_option()       Gets option from the DB given string
	 * @uses update_option()    Updates option given key and new value. Creates if !exist
	 */
	public function save_tax_meta( $term_id ){

		if ( isset( $_POST['term_meta'] ) ) {

			$t_id = $term_id;
			$term_meta = array();

			$term_meta['tax_image'] = isset ( $_POST['term_meta']['tax_image'] ) ? esc_url( $_POST['term_meta']['tax_image'] ) : '';

			// Save the option array.
			update_option( "taxonomy_$t_id", $term_meta );

		} // if isset( $_POST['term_meta'] )
	} // save_tax_meta

} // WPTT_Tax_Image

$wptt_tax_image = new WPTT_Tax_Image();

/**
 * Template tag that shows our header image
 *
 * @since 1.1
 * @author WP Theme Tutorial, Curtis McHale
 *
 * @uses WPTT_Tax_Image->show_header_image()     Shows the header image for given taxonomy
 */
function wptt_taxonomy_header_image(){
	$wptt_tax_image = new WPTT_Tax_Image();
	$wptt_tax_image->show_header_image();
} // wptt_taxonomy_header_image