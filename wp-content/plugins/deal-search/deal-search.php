<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 8/5/2016
 * Time: 2:22 PM
 *
 * Plugin Name: Deal Search
 * Plugin URI: http://codemonkeysllc.com
 * Description: Custom deal search plugin for Visual Composer Grid Builder
 * Version: 1.0.0
 * Author: Spencer Fraise
 * Author URI: http://codemonkeysllc.com
 * License: GPL2
 */

//* ENQUEUE SCRIPTS AND STYLES
function dealsearch_enqueue_scripts() {
    // ENQUEUE CSS
    wp_enqueue_style( 'style', plugin_dir_url(__FILE__) . '/css/style.css' );

    // ENQUEUE SCRIPT
    wp_enqueue_script( 'script', plugin_dir_url(__FILE__) . '/js/script.js', array('jquery'), '2.0', true );

    // CHECK IF HOMEPAGE
    if(is_front_page()) {
        $frontpage = 1;
    } else {
        $frontpage = 0;
    }

    // PASS PHP DATA TO SCRIPT FILE
    wp_localize_script('script', 'dealScript', array(
        'pluginUrl' => plugin_dir_url(__FILE__),
        'siteUrl' =>  get_site_url(),
        'frontPage' => $frontpage,
        'geoIpZip' => do_shortcode('[geoip_detect2 property="postal.code"]')
    ));

    // LOCALIZE CUSTOM JS FILE TO USE WITH AJAX
    wp_localize_script( 'script', 'ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));

}
add_action( 'wp_enqueue_scripts', 'dealsearch_enqueue_scripts' );

function searchBar() {
    //* GET COUPON CATEGORIES
    $coupon_categories = array();
    $terms = get_terms('category');
    $default_city = do_shortcode('[geoip_detect2 property="city.name"]');
    $default_state = do_shortcode('[geoip_detect2 property="mostSpecificSubdivision.isoCode"]');
    $default_zipcode = do_shortcode('[geoip_detect2 property="postal.code"]');
    $default_location = '';
    if($default_city && $default_state && $default_city !== null && $default_state !== null && !empty($default_city) && !empty($default_state)) {
        $default_location = $default_city . ', ' . $default_state;
    } else if($default_zipcode) {
        $default_location = $default_zipcode;
    } else {
        $default_location = 'Maple Grove, MN';
    }

    if ($terms && !is_wp_error($terms)) {
        foreach($terms as $term) {
            /*** CURRENTLY WANT TO SHOW ALL, NOT JUST RECOMMENDED ON LOAD
            // IF PARENT ID IS 41 (DEAL CATEGORIES)
            if($term->parent == 41) {
                if($term->term_id == 53) {
                    // SET FEATURED CATEGORY AS SELECTED
                    $coupon_categories[] = '<option value="' . $term->term_id . '" data="' . $term->name . '" selected="selected">' . $term->name . '</option>';
                } else {
                    $coupon_categories[] = '<option value="' . $term->term_id . '" data="' . $term->name . '">' . $term->name . '</option>';
                }
            }
            ***/
            if($term->name !== 'Uncategorized' && $term->name !== 'Deal Categories') {
                $coupon_categories[] = '<option value="' . $term->term_id . '" data="' . $term->name . '">' . $term->name . '</option>';
            }
        }
    }
    $category_options = implode('', $coupon_categories);

    /*
     * Get geiop city name do_shortcode('[geoip_detect2 property="city.name"]')
     * Get geoip zipcode do_shortcode('[geoip_detect2 property="postal.code"]')
     * Get geoip state abbreviation do_shortcode('[geoip_detect2 property="mostSpecificSubdivision.isoCode"]')
     */
    echo '
        <div class="deal_search_wrapper">
            <div class="deal_search">
                <div class="deal_search_top">
                    <div class="deal_search_title">
                        Search Deals
                    </div>
                    <div class="deal_search_cart_info">
                        <div class="deal_search_cart_info_item deal_search_cart_info_saved">
                            <span class="deal_search_saved_items">0</span> saved deals
                        </div>
                        </div>
                    <div style="clear:both;"></div>
                </div>
                <div class="deal_search_loading_placeholder">
                    <div class="ajax-loading"><img src="' . plugin_dir_url(__FILE__) . '/images/loading/loading47.gif" alt="loading" /></div>
                </div>
                <div class="deal_search_input_wrapper">
                    <div class="deal_search_cat_select_wrapper">
                        <select id="deal_search_cat_select" class="deal_search_cat_select">
                            <option value="">Categories</option>
                            ' . $category_options . '
                        </select>
                    </div>
                    <span id="width_tmp"></span>
                    <input type="text" id="deal_search_text" value="" />
                    <div class="deal_search_submit">
                        GO
                </div>
                </div>
                <div class="deal_search_radius_wrapper">
                    Within
                    <select id="deal_search_radius_miles">
                        <option value="">Any Distance</option>
                        <option value="1">1 Mile</option>
                        <option value="5">5 Miles</option>
                        <option value="10">10 Miles</option>
                        <option value="15">15 Miles</option>
                        <option value="20" selected="selected">20 Miles</option>
                        <option value="40">40 Miles</option>
                    </select>
                    of <input type="text" id="deal_search_radius_zipcode" value="' . $default_location . '" placeholder="City, St or Zip" />
                    <div class="location-suggestion-wrapper"></div>
                    <input type="hidden" id="deal_search_radius_zipcode_last" value="" />
                    <input type="hidden" id="deal_search_radius_miles_last" value="" />
                    <input type="hidden" id="deal_search_radius_zipcode_array" value="" />
                    <input type="hidden" id="deal_search_my_zipcode" value="' . $default_location . '" />
                    <input type="hidden" id="deal_search_default_city" value="' . $default_city . '" />
                    <input type="hidden" id="deal_search_default_state" value="' . $default_state . '" />
                    <input type="hidden" id="deal_search_default_zipcode" value="' . $default_zipcode . '" />
                    <div class="deal_search_submit">
                        GO
                    </div>
                </div>
            </div>
            <input type="hidden" id="deal_search_results_found" value="" />
            <input type="hidden" id="deal_search_page" value="1" />
        </div>
    ';
}
add_shortcode('show_deal_search', 'searchBar');

//* REQUIRE AJAX FUNCTIONS
require(plugin_dir_path(__FILE__) . '/ajax-functions.php');
