<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 2/23/2016
 * Time: 2:41 PM
 */

/**
 * Functions
 *
 * @package Lakes
 * @since lakes 1.0
 * @author Code Monkeys LLC <contact@codemonkeysllc.com>
 * @copyright Copyright (c) 2016, Code Monkeys LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 */
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );
/**
 * Theme Setup
 *
 * This setup function attaches all of the site-wide functions
 * to the correct actions and filters. All the functions themselves
 * are defined below this setup function.
 *
 */



//* add_action('genesis_before_header','top_bar',5);

/* function top_bar(){
	$site_title = sprintf( '<div class="top_bar"><a href="%s"><img src="/wp-content/uploads/2016/02/logo_wheel.png" /><span>International Association of Wellness Professionals</span></a></div>', trailingslashit( home_url() ) );
	echo $site_title;
}*/

//* Add header image
//add_theme_support( 'genesis-custom-header');

//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add support for custom background
add_theme_support( 'custom-background' );

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 3 );

//* Add short code support to text widgets
add_filter('widget_text', 'do_shortcode');

//* Add short code support to posts
add_filter('widget_text', 'do_shortcode');
//add_filter('the_excerpt', 'do_shortcode');

//* REMOVE ANNOYING ADMIN BAR
function remove_admin_login_header() {
	remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action('get_header', 'remove_admin_login_header');

add_filter( 'vc_grid_item_shortcodes', 'my_module_add_grid_shortcodes' );
function my_module_add_grid_shortcodes( $shortcodes ) {
	$shortcodes['vc_post_id'] = array(
		'name' => __( 'Post ID', 'my-text-domain' ),
		'base' => 'vc_post_id',
		'category' => __( 'Content', 'my-text-domain' ),
		'description' => __( 'Show current post ID', 'my-text-domain' ),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	);

	return $shortcodes;
}
// output function
add_shortcode( 'vc_post_id', 'vc_post_id_render' );
function vc_post_id_render() {
	return '<div data-post-id="{{ post_data:ID }}" style="display:none"></div>'; // usage of template variable post_data with argument "post_id"
}


//* LOAD GOOGLE FONTS
add_action('wp_enqueue_scripts', 'google_fonts');
function google_fonts() {
    $query_args = array(
        'family' => 'Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i',
        'subset' => 'latin,latin-ext'
    );
    wp_enqueue_style( 'google_fonts', add_query_arg( $query_args, "//fonts.googleapis.com/css" ), array(), null );
}


//enqueues external font awesome stylesheet
function enqueue_our_required_stylesheets(){
	wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
	wp_enqueue_style('custom', get_stylesheet_directory_uri() . '/custom.css');
}
add_action('wp_enqueue_scripts','enqueue_our_required_stylesheets', 99);

//* Enqueue Custom Admin CSS
function enqueue_admin_stylesheets()
{
	wp_enqueue_style('admin-style', get_stylesheet_directory_uri() . '/admin-style.css');
}

add_action('admin_enqueue_scripts', 'enqueue_admin_stylesheets');

function ajax_enqueue_scripts() {
	// ENQUEUE CUSTOM JS
	wp_enqueue_script( 'custom', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), '2.0', true );

	// LOCALIZE CUSTOM JS FILE TO USE WITH AJAX
	wp_localize_script( 'custom', 'ajax', array(
		'ajax_url' => admin_url( 'admin-ajax.php' )
	));

}
add_action( 'wp_enqueue_scripts', 'ajax_enqueue_scripts' );

/*** ADD PERMALINK SLUGS
 * Use: <a href="[permalink id=49]">Basic Usage</a>
 * OR
 * [permalink id=49 text='providing text']
 ***/
function do_permalink($atts) {
	extract(shortcode_atts(array(
		'id' => 1,
		'text' => ""  // default value if none supplied
	), $atts));

	if ($text) {
		$url = get_permalink($id);
		return "<a href='$url'>$text</a>";
	} else {
		return get_permalink($id);
	}
}
add_shortcode('permalink', 'do_permalink');

//* Remove page titles from all single posts & pages (requires HTML5 theme support)
add_action( 'get_header', 'child_remove_titles' );
function child_remove_titles() {
	if ( is_singular() ){
		remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
	}
}

//add custom menus
function register_my_menus() {
	register_nav_menus(
		array(
			'footer-menu1' => __( 'Footer Menu1' ),
			'footer-menu2' => __( 'Footer Menu2' )
		)
	);
}
add_action( 'init', 'register_my_menus' );

// add menu short code example [menu name="main-menu"]
function print_menu_shortcode($atts, $content = null) {
    extract(shortcode_atts(array( 'name' => null, ), $atts));
    return wp_nav_menu( array( 'menu' => $name, 'echo' => false ) );
}
add_shortcode('menu', 'print_menu_shortcode');

//* Remove the site footer
//remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
//remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

//* Customize the site footer
/*
function bg_custom_footer() { ?>

	<div class="ft-left">
		<div class="ft-menu1">
			<?php wp_nav_menu( array( 'theme_location' => 'footer-menu1', 'fallback_cb' => '' ) ); ?>
		</div>
		<div class="copyright">
			<div class="copy-text">Copyright&copy; <?php print date('Y') ?> Lakes Marketing All Rights Reserved.</div>
			<div class="copy-text">DEVELOPED BY <a href="http://www.e-mode.com">EMOD, LLC</a></div>
		</div>
	</div>
	<div class="ft-menu2">
		<?php wp_nav_menu( array( 'theme_location' => 'footer-menu2', 'fallback_cb' => '' ) ); ?>
	</div>

	<?php
}
add_action( 'genesis_footer', 'bg_custom_footer' );
*/

function company_init() {
	// create a new taxonomy
	register_taxonomy(
		'company',
		'post',
		array(
			'label' => __( 'Companies' ),
			'rewrite' => array( 'slug' => 'company' ),
		)
	);
}
add_action( 'init', 'company_init' );

add_action( 'init', 'create_coupon_post_type' );
function create_coupon_post_type() {

	$labels = array(
		'name'               => __( 'Coupons' ),
		'singular_name'      => __( 'Coupon' ),
		'all_items'          => __( 'All Coupons' ),
		'add_new'            => _x( 'Add new Coupon', 'Coupons' ),
		'add_new_item'       => __( 'Add new Coupon' ),
		'edit_item'          => __( 'Edit Coupon' ),
		'new_item'           => __( 'New Coupon' ),
		'view_item'          => __( 'View Coupon' ),
		'search_items'       => __( 'Search in Coupons' ),
		'not_found'          => __( 'No Coupons found' ),
		'not_found_in_trash' => __( 'No Coupons found in trash' ),
		'parent_item_colon'  => ''
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => true,
		'menu_icon'          => 'dashicons-tickets-alt', //pick one here ~> https://developer.wordpress.org/resource/dashicons/
		'rewrite'            => array( 'slug' => 'coupon' ),
		'taxonomies'         => array( 'category', 'post_tag', 'coupon' ),
		'query_var'          => true,
		'menu_position'      => 5,
		'supports'           => array( 'genesis-cpt-archives-settings', 'thumbnail' , 'custom-fields', 'excerpt', 'comments', 'title', 'editor')
	);

	register_post_type( 'coupons', $args);
}

add_action( 'init', 'create_team_post_type' );
function create_team_post_type() {

	$labels = array(
		'name'               => __( 'Team' ),
		'singular_name'      => __( 'Team Member' ),
		'all_items'          => __( 'All Team Members' ),
		'add_new'            => _x( 'Add new Team Member', 'Team' ),
		'add_new_item'       => __( 'Add new Team Member' ),
		'edit_item'          => __( 'Edit Team Member' ),
		'new_item'           => __( 'New Team Member' ),
		'view_item'          => __( 'View Team Member' ),
		'search_items'       => __( 'Search Team' ),
		'not_found'          => __( 'No Team Members found' ),
		'not_found_in_trash' => __( 'No Team Members found in trash' ),
		'parent_item_colon'  => ''
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => true,
		'menu_icon'          => 'dashicons-groups', //pick one here ~> https://developer.wordpress.org/resource/dashicons/
		'rewrite'            => array( 'slug' => 'team' ),
		'taxonomies'         => array( 'team' ),
		'query_var'          => true,
		'menu_position'      => 5,
		'supports'           => array( 'genesis-cpt-archives-settings', 'thumbnail' , 'custom-fields', 'excerpt', 'comments', 'title', 'editor')
	);

	register_post_type( 'team', $args);
}

add_image_size('featured_preview', 55, 55, true);
// GET FEATURED IMAGE
function coupon_get_featured_image($post_ID) {
    $post_thumbnail_id = get_post_thumbnail_id($post_ID);
    if ($post_thumbnail_id) {
        $post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');
        return $post_thumbnail_img[0];
    }
}

// add custom post columns
add_action('manage_coupons_posts_custom_column', 'columns_content_only_coupons', 10, 2);
add_filter( 'manage_edit-coupons_columns', 'columns_head_only_coupons' );

// CREATE TWO FUNCTIONS TO HANDLE THE COLUMN

function columns_head_only_coupons($defaults) {
    $defaults['logo'] = __( 'Logo', 'your_text_domain' );
    $defaults['listing_id'] = __( 'Listing ID', 'your_text_domain' );
    $defaults['post_id'] = __( 'Post ID', 'your_text_domain' );
    $defaults['expiration'] = __( 'Expires', 'your_text_domain' );

    $customOrder = array('cb', 'logo','title','listing_id','post_id','expiration');
    foreach ($customOrder as $colname)
        $new[$colname] = $defaults[$colname];

    return $new;
}
function columns_content_only_coupons( $column, $post_id ) {
    switch ( $column ) {

        case 'logo' :
            $post_featured_image = coupon_get_featured_image($post_id);
            if ($post_featured_image) {
                echo '<img style="width:100px" src="' . $post_featured_image . '" />';
            }
            break;

        /*case 'listing_id' :
            echo get_post_meta( $post_id , 'listing_id' , true );
            break;*/

        case 'post_id' :
            echo $post_id;
            break;

        /*case 'expiration' :
            echo get_post_meta( $post_id , 'expiration' , true );
            break;*/
    }
}

add_shortcode('feature_image', 'thumbnail_in_content');

function thumbnail_in_content($atts) {
    global $post;
    return get_the_post_thumbnail($post->ID);
}

add_filter($if_shortcode_filter_prefix.'has_featured-image','my_condition_evaluator');

function my_condition_evaluator($value) {
    global $post;

    if(get_the_post_thumbnail($post->ID)){
        $evaluate = true;
    }else{
        $evaluate = false;
    }
    return $evaluate;
}

function set_post_status_acf($post_id){
    if ( ! wp_is_post_revision( $post_id ) ) {

        if (get_post_meta($post_id, 'visibility')) {
            $visibility = get_post_meta($post_id, 'visibility', true);

            switch ($visibility) {
                case 0:
                    $status = "draft";
                    break;
                case 1:
                    $status = "publish";
                    break;
                default:
                    add_post_meta($post_id, 'visibility', 1);
                    $status = "publish";
            }

            // unhook this function so it doesn't loop infinitely
            remove_action('save_post', 'set_post_status_acf', 10,1);

            // update the post, which calls save_post again
            wp_update_post(array('ID' => $post_id, 'post_status' => $status));

            // re-hook this function
            add_action('save_post', 'set_post_status_acf',10,1);
        }
    }
}


function set_expiration($post_id) {

    //Remove the default expiration date
    remove_action('save_post', 'expirationdate_update_post_meta');
    //Pull the date we need
    if(get_post_meta( $post_id , 'expiration', true )){
        $date = get_post_meta( $post_id , 'expiration', true );
        expirationdate_update_post_meta_acf($post_id, $date);
    } else{
        return;
    }
}

//modified update_post_meta function
function expirationdate_update_post_meta_acf($post_id, $date) {
    // don't run the echo if this is an auto save

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return;

    // don't run the echo if the function is called for saving revision.
    $posttype = get_post_type($post_id);
    if ( $posttype == 'revision' )
    {
        return;
    } else {
        if($date) {
            //'m/d/Y' is the format my ACF date field is outputting - can be differ from each setup!
            $formatted_date = DateTime::createFromFormat('m/d/Y', $date);

            $month = intval($formatted_date->format('m'));
            $day = intval($formatted_date->format('d'));
            $year = intval($formatted_date->format('y'));

            //I am not using time in my ACF field, so I am setting it manually to the end of the day.
            $hour = 23;
            $minute = 59;

            $opts = array();
            $ts = get_gmt_from_date("$year-$month-$day $hour:$minute:0", 'U');

            // Schedule/Update Expiration
            $opts['expireType'] = 'draft';
            $opts['id'] = $post_id;

            _scheduleExpiratorEvent($post_id, $ts, $opts);
        }else{
            _unscheduleExpiratorEvent($post_id);
        }
    }
}
add_action('acf/save_post', 'set_post_status_acf', 10, 1);
add_action('pmxi_saved_post', 'set_post_status_acf', 10, 1);
add_action('acf/save_post', 'set_expiration', 10, 1);
add_action('pmxi_saved_post', 'set_expiration', 10, 1);