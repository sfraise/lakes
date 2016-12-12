<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 8/5/2016
 * Time: 8:07 PM
 */

//enqueues external promise.js
function enqueue_promisejs(){
    wp_enqueue_script('promisejs', '//www.promisejs.org/polyfills/promise-7.0.4.min.js');
}
add_action('wp_enqueue_scripts','enqueue_promisejs');

//* AJAX UPDATE DEALS
function update_deals() {
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
        global $wpdb;
        $category = $_REQUEST['category'];
        $catname = $_REQUEST['catname'];
        $text = $_REQUEST['text'];
        $text = stripslashes($text);
        $page = $_REQUEST['page'];
        $perPage = 21;
        $miles = $_REQUEST['miles'];
        $defaultState = $_REQUEST['defaultState'];
        $zipcode = $_REQUEST['zipcode'];
        $zipcodes = $_REQUEST['zipcodes'];
        $resultsFound = $_REQUEST['resultsFound'];
        $mapData = array();

        // CHECK IF ZIPCODE IS ACTUALLY CITY, STATE
        if(!is_numeric($zipcode)) {
            if (strpos($zipcode, ',') !== false) {
                $location = explode(',', $zipcode);
                $city = $location[0];
                $state = trim($location[1]);
            } else {
                $city = $zipcode;
                $state = $defaultState;
            }
        }

        if($city && $state) {
            $querystr = "
                SELECT " . $wpdb->prefix . "geo_usa.postal_code
                FROM " . $wpdb->prefix . "geo_usa
                WHERE (" . $wpdb->prefix . "geo_usa.name = '$city'
                OR find_in_set('$city', " . $wpdb->prefix . "geo_usa.name_other_long)
                OR find_in_set('$city', " . $wpdb->prefix . "geo_usa.name_other_abr))
                AND (" . $wpdb->prefix . "geo_usa.adm_name1 = '$state'
                OR " . $wpdb->prefix . "geo_usa.adm_code1 = '$state')
                LIMIT 1
            ";

            $geoResults = $wpdb->get_results($querystr, OBJECT);

            if($geoResults) {
                foreach ($geoResults as $geoResult) {
                    $zipcode = $geoResult->postal_code;
                }
            }
        }

        if(!$category) {
            $category = '';
        }
        $tags = ''; // must be array of tag id's

        require plugin_dir_path( __FILE__ ) . 'includes/class-deal-search.php';
        $z = new Zipcode;

        // SET QUERY ARGS
        $metaQuery = array('relation' => 'AND');
        $taxQuery = array('relation' => 'AND');

        if($zipcodes) {
            $metaQuery[] = array(
                'key' => 'zipcode',
                'value' => $zipcodes,
                'compare' => 'IN'
            );
        }

        if($text) {
            $tags = explode(" ", $text); // split text string by space for tag slug array
            foreach($tags as $key => $value) {
                // THIS SEEMS HORRIBLY REDUNDANT AND INEFFICIENT BUT IT WORKS
                $metaQuery[] = array(
                    'relation' => 'OR',
                    'company_clause' => array(
                        'key' => 'company',
                        'value' => $text,
                        'compare' => 'LIKE'
                    ),
                    'primary_clause' => array(
                        'key' => 'primary',
                        'value' => $text,
                        'compare' => 'LIKE'
                    ),
                    'secondary_clause' => array(
                        'key' => 'secondary',
                        'value' => $text,
                        'compare' => 'LIKE'
                    ),
                    'tags_clause' => array(
                        'key' => 'tags',
                        'value' => $value,
                        'compare' => 'LIKE'
                    )
                );
            }
        }

        $args = array(
            'post_type' => 'coupons',
            'meta_query' => $metaQuery,
            'post_status' => 'publish',
            'posts_per_page' => $perPage,
            'paged' => $page,
            'meta_key' => 'company',
            'orderby' => 'company',
            'order' => 'ASC'
        );

        if($category) {
            $args['cat'] = $category;
        }

        $query = new WP_Query( $args );
        $queryCount = $query->found_posts;

        /*** IF NO RESULTS ON INITIAL SEARCH RERUN QUERY WITHOUT LOCATION ***/
        if($queryCount == 0 && $resultsFound == 0) {
            // SET QUERY ARGS
            $metaQuery = array('relation' => 'AND');
            $taxQuery = array('relation' => 'AND');

            if($text) {
                $tags = explode(" ", $text); // split text string by space for tag slug array
                foreach($tags as $key => $value) {
                    // THIS SEEMS HORRIBLY REDUNDANT AND INEFFICIENT BUT IT WORKS
                    $metaQuery[] = array(
                        'relation' => 'OR',
                        array(
                            'key' => 'company',
                            'value' => $text,
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => 'primary',
                            'value' => $text,
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => 'secondary',
                            'value' => $text,
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => 'tags',
                            'value' => $value,
                            'compare' => 'LIKE'
                        )
                    );
                }
            }

            $args = array(
                'post_type' => 'coupons',
                'meta_query' => $metaQuery,
                'post_status' => 'publish',
                'posts_per_page' => $perPage,
                'paged' => $page,
                'meta_key' => 'company',
                'orderby' => 'company',
                'order' => 'ASC'
            );

            if($category) {
                $args['cat'] = $category;
            }

            $query = new WP_Query( $args );
            $queryCount = $query->found_posts;

            $notice = 'No ' . $catname . ' deals found within ' . $miles . ' miles of ' . $zipcode . ', try broadening your search criteria';
        }

        /*** IF STILL NO RESULTS ON INITIAL SEARCH RERUN QUERY WITH JUST RECOMMENDED DEALS ***/
        if($queryCount == 0 && $resultsFound == 0) {
            // SET QUERY ARGS
            $args = array(
                'post_type' => 'coupons',
                'post_status' => 'publish',
                'posts_per_page' => $perPage,
                'paged' => $page,
                'meta_key' => 'company',
                'orderby' => 'company',
                'order' => 'ASC'
            );

            //$args['cat'] = 53;

            $query = new WP_Query( $args );
            $queryCount = $query->found_posts;

            if($text) {
                $notice = 'No ' . $catname . ' deals found for ' . $text . ', try broadening your search criteria';
            } else {
                $notice = 'No ' . $catname . ' deals found, try broadening your search criteria';
            }
        }

        $deals = array();
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post_company = get_post_meta($post_id, $key = 'company', true);
            $post_title = get_the_title();
            $post_permalink = get_post_permalink( $post_id, $leavename = false, $sample = false );
            $post_thumb = wp_get_attachment_image_src(get_post_thumbnail_id( $post_id ), $size = 'full', $icon = false);
            $post_image = get_the_post_thumbnail();
            if(!$post_image) {
                $post_image = '<img src="' . plugins_url() . '/deal-search/images/no-image-300x300.gif" alt="No Logo Available" />';
            }
            $post_address = get_post_meta($post_id, $key = 'address', true);
            $post_city = get_post_meta($post_id, $key = 'city', true);
            $post_state = get_post_meta($post_id, $key = 'state', true);
            $post_zipcode = get_post_meta($post_id, $key = 'zipcode', true);
            $post_phone = get_post_meta($post_id, $key = 'phone', true);
            $post_tertiary = get_post_meta($post_id, $key = 'tertiary', true);
            $post_website = get_post_meta($post_id, $key = 'website', true);
            $post_primary = get_post_meta($post_id, $key = 'primary', true);
            $post_secondary = get_post_meta($post_id, $key = 'secondary', true);
            $post_disclaimer = get_post_meta($post_id, $key = 'disclaimer', true);
            $post_expiration = get_post_meta($post_id, $key = 'expiration', true);
            $distance = round($z->get_distance($zipcode, $post_zipcode));

            if (!preg_match("~^(?:f|ht)tps?://~i", $post_website)) {
                $post_website = "http://" . $post_website;
            }

            $mapData[] = array(
                'company' => '<a href="' . get_permalink() . '">' . $post_company . '</a>',
                'address' => $post_address,
                'city' => $post_city,
                'state' => $post_state,
                'zipcode' => $post_zipcode
            );

            $deals[] = '
                    <div class="deal-grid-item" data="' . $post_id . '">
                        <div class="deal-grid-details-content">
                            <img class="tt-close" src="' . plugins_url() . '/deal-search/images/tt-close.png" alt="Close" />
                            <div class="deal-grid-details-primary">'. $post_primary .'</div>
                            <div class="deal-grid-details-secondary">'. $post_secondary .'</div>
                            <div class="deal-grid-details-tertiary">'. $post_tertiary .'</div>
                            <div class="deal-grid-details-disclaimer">'. $post_disclaimer .'</div>
                            <div class="deal-grid-details-expiration">Expires on '. $post_expiration .'</div>
                        </div>
                        <div class="deal-grid-contact-content">
                            <img class="tt-close" src="wp-content/plugins/deal-search/images/tt-close.png" alt="Close" />
                            <div class="deal-grid-contact-phone">'. $post_phone .'</div>
                            <div class="deal-grid-contact-address">'. $post_address .'</div>
                            <div class="deal-grid-contact-citystate">'. $post_city .',  '.$post_state.'  '.$post_zipcode.'</div>
                        </div>
                        <div class="deal-grid-share-content">
                            <img class="tt-close" src="wp-content/plugins/deal-search/images/tt-close.png" alt="Close" />
                            '.(function_exists('ADDTOANY_SHARE_SAVE_KIT') ? ADDTOANY_SHARE_SAVE_KIT(array('output_later' => true, 'linkname' => $post_primary.' '.$post_secondary.' - '.$post_company,'linkurl'  => $post_permalink, 'linkmedia' => $post_thumb[0])) : '').'
                        </div>
                        <div class="deal-grid-logo">
                            ' . $post_image . '
                        </div>
                        <div class="deal-grid-details">
                            <div class="deal-grid-deal">
                                <div class="deal-grid-primary">
                                    ' . $post_primary . '
                                </div>
                                <div class="deal-grid-secondary">
                                    ' . $post_secondary . '
                                </div>
                            </div>
                            <div class="deal-grid-tertiary-visible">
                                ' . $post_tertiary . '
                            </div>
                            <div class="deal-grid-tertiary">
                                <div class="deal-grid-detail-link deal-grid-info-button">
                                    Show Details
                                </div>
                            </div>
                            <div class="deal-grid-city">
                                ' . $post_city . '
                            </div>
                            <div class="deal-grid-address">
                                ' . $post_address . '
                            </div>
                            <div class="deal-grid-contact">
                                <div class="deal-grid-contact-link deal-grid-info-button">
                                    Contact
                                </div>
                            </div>
                        </div>
                        <div class="deal-grid-buttons">
                            <div class="deal-grid-button deal-print-button">
                                <div class="deal-grid-button-icon">
                                    <i class="vc_btn3-icon fa fa-bullseye"></i>
                                </div>
                                <div class="deal-grid-button-text">
                                    PRINT
                                </div>
                            </div>
                            <div class="deal-grid-button deal-save-button">
                                <div class="deal-grid-button-icon">
                                    <i class="vc_btn3-icon fa fa-heart"></i>
                                </div>
                                <div class="deal-grid-button-text">
                                    SAVE
                                </div>
                            </div>
                            <div class="deal-grid-button deal-share-button">
                                <div class="deal-grid-button-icon">
                                    <i class="vc_btn3-icon fa fa-share-alt"></i>
                                </div>
                                <div class="deal-grid-button-text">
                                    SHARE
                                </div>
                            </div>
                            <div class="deal-grid-button deal-web-button">
                                <a href="' . $post_website . '" target="_blank">
                                <div class="deal-grid-button-icon">
                                    <i class="fa fa-globe" aria-hidden="true"></i>
                                </div>
                                <div class="deal-grid-button-text">
                                    WWW
                                </div>
                                </a>
                            </div>
                        </div>
                    </div>
                ';
        }

        $remainingCount = $perPage * $page;

        wp_reset_query();

        $deals = implode('', $deals);

        $newDeals =  $deals;

        if($queryCount > 0) {
            $data = array(
                "deals" => $newDeals,
                "notice" => $notice,
                "categoryName" => $catname,
                "mapData" => $mapData,
                "zipcodes" => $zipcodes
            );

            echo json_encode($data);
        } else {
            echo json_encode('');
    }
    }

    // Always die in functions echoing ajax content
    die();
}
add_action( 'wp_ajax_update_deals', 'update_deals' );
add_action( 'wp_ajax_nopriv_update_deals', 'update_deals' );

function location_suggest() {
    if(isset($_REQUEST)) {
        global $wpdb;
        $city = $_REQUEST['city'];

        $state = '';
        if (strpos($city, ',') !== FALSE) {
            $location = explode(',', $city);
            $city = $location[0];
            $state = trim($location[1]);
        }

        if(strlen($city) > 2) {
            $querystr = "
                SELECT " . $wpdb->prefix . "geo_usa.name, " . $wpdb->prefix . "geo_usa.adm_code1, " . $wpdb->prefix . "geo_usa.postal_code
                FROM " . $wpdb->prefix . "geo_usa
                WHERE (" . $wpdb->prefix . "geo_usa.name LIKE '$city%'
                OR find_in_set('$city', " . $wpdb->prefix . "geo_usa.name_other_long)
                OR find_in_set('$city', " . $wpdb->prefix . "geo_usa.name_other_abr))
                AND " . $wpdb->prefix . "geo_usa.adm_code1 LIKE '$state%'
                ORDER BY " . $wpdb->prefix . "geo_usa.name, " . $wpdb->prefix . "geo_usa.adm_code1
                LIMIT 5
            ";

            $geoResults = $wpdb->get_results($querystr, OBJECT);

            if ($geoResults) {
                $suggestion = array();
                foreach ($geoResults as $geoResult) {
                    $suggestion[] = '
                        <div class="location-suggestion" data="' . $geoResult->name . ', ' . $geoResult->adm_code1 . '">
                            ' . $geoResult->name . ', ' . $geoResult->adm_code1 . ' ' . $geoResult->postal_code . '
                        </div>
                    ';
                }

                echo implode('', $suggestion);
            }
        } else {
            echo '';
        }
    }

    die();
}
add_action( 'wp_ajax_location_suggest', 'location_suggest' );
add_action( 'wp_ajax_nopriv_location_suggest', 'location_suggest' );

//* AJAX GET SAVED DEALS
function getSavedDeals() {
    if ( isset($_REQUEST) ) {
        //var_dump($_REQUEST);
        $deals = explode(',',$_REQUEST['deals']);
        //var_dump($deals);
        $formatted_deals = array();
        if(count($deals) > 0 ){
            foreach($deals as $deal){
                $post_id = $deal;
                if(isset($post_id)) {
                    $post_company = get_post_meta($post_id, $key = 'company', true);
                    $post_title = get_the_title();
                    $post_thumbnail_imgs = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'featured_preview');
                    //$post_image = get_the_post_thumbnail();
                    $post_image =$post_thumbnail_imgs[0];

                    if(!$post_image) {
                        $post_image =  plugins_url() . '/deal-search/images/no-image-300x300.gif';
                    }
                    $post_address = get_post_meta($post_id, $key = 'address', true);
                    $post_city = get_post_meta($post_id, $key = 'city', true);
                    $post_state = get_post_meta($post_id, $key = 'state', true);
                    $post_zipcode = get_post_meta($post_id, $key = 'zipcode', true);
                    $post_phone = get_post_meta($post_id, $key = 'phone', true);
                    $post_tertiary = get_post_meta($post_id, $key = 'tertiary', true);
                    $post_website = get_post_meta($post_id, $key = 'website', true);
                    $post_primary = get_post_meta($post_id, $key = 'primary', true);
                    $post_secondary = get_post_meta($post_id, $key = 'secondary', true);
                    $post_disclaimer = get_post_meta($post_id, $key = 'disclaimer', true);
                    $post_expiration = get_post_meta($post_id, $key = 'expiration', true);


                    $formatted_deals[] = "
                         <div class='saved-deal'>
                            <div class='col_1 tb-cell'>
                                <input class='saved-ckbox' type='checkbox' name='deal' data-postId='" . $post_id . "'>
                            </div>
                            <div class='col_2 tb-cell'>
                                <img style='width:75px' src='".$post_image."'>
                            </div>
                            <div class='col_3 tb-cell' >
                                <div class='saved-deal_name'>
                                    " . $post_company . "
                                </div>
                                <div class='saved-deal_details'>
                                    " . $post_primary . " " . $post_secondary . "<br />
                                    ".$post_tertiary."
                                </div>
                                <div class='saved-deal_disclaimer'>
                                    ".$post_disclaimer."<br />
                                </div>
                            </div>
                            <div class='col_4 tb-cell'>
                                <div class='saved-deal_contact'>
                                    ".$post_phone."<br />
                                    ".$post_city.", ".$post_state."
                                </div>
                                <div class='saved-deal_expiration'>
                                    Expires on ".$post_expiration."
                                </div>
                            </div>
                        </div>";
                }
            }
        }
        // TODO: POPULATE SAVED DEALS IN A MODAL WINDOW WITH ABILITY TO REMOVE/PRINT/SHARE SELECTED DEALS (AND SELECT ALL OPTION)
        //var_dump($formatted_deals);
        $saved_deals = implode('', $formatted_deals);
        if(count($formatted_deals) > 0 ) {
            echo "
            <div class='saved-deals-content-area'>
                <div class='header_row'>
                    <div class='col_1 tb-cell'>
                        <input type='checkbox' name='select-all' id='select-all' >
                    </div>
                    <div class='col_2 tb-cell'>
                        Select All
                    </div>
                    <div class='col_3 tb-cell'>
                    </div>
                    <div class='col_4 tb-cell'>
                       <div id='print-saved' class='button'>Print</div>
                       <div id='remove-deal' class='button'>Remove</div>
                    </div>
                </div>
                " . $saved_deals ."
            </div>";
        }else{
            echo "No deals saved";
        }
        die();
    }
}
add_action( 'wp_ajax_getSavedDeals', 'getSavedDeals' );
add_action( 'wp_ajax_nopriv_getSavedDeals', 'getSavedDeals' );

//* AJAX PRINT SELECTED DEALS
function printDeals(){
    if ( isset($_REQUEST) ) {
        $deals = $_REQUEST['deals'];
        //var_dump($deals);
        $print_deals = array();
        if(count($deals) > 0 ) {
            foreach ($deals as $deal) {
                $post_id = $deal;
                if (isset($post_id)) {
                    $post_company = get_post_meta($post_id, $key = 'company', true);
                    $post_title = get_the_title();
                    $post_thumbnail_imgs = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'featured_preview');
                    //$post_image = get_the_post_thumbnail();
                    $post_image = $post_thumbnail_imgs[0];

                    if (!$post_image) {
                        $post_image = plugins_url() . '/deal-search/images/no-image-300x300.gif';
                    }
                    $post_address = get_post_meta($post_id, $key = 'address', true);
                    $post_city = get_post_meta($post_id, $key = 'city', true);
                    $post_state = get_post_meta($post_id, $key = 'state', true);
                    $post_zipcode = get_post_meta($post_id, $key = 'zipcode', true);
                    $post_phone = get_post_meta($post_id, $key = 'phone', true);
                    $post_tertiary = get_post_meta($post_id, $key = 'tertiary', true);
                    $post_website = get_post_meta($post_id, $key = 'website', true);
                    $post_primary = get_post_meta($post_id, $key = 'primary', true);
                    $post_secondary = get_post_meta($post_id, $key = 'secondary', true);
                    $post_disclaimer = get_post_meta($post_id, $key = 'disclaimer', true);
                    $post_expiration = get_post_meta($post_id, $key = 'expiration', true);

                    $print_deals[] = '
                        <div class="deal-wrapper" data="' . $post_id . '">
                            <div class="print-grid-top-row">
                                <div class="print-grid-company">' . $post_company. '</div>
                                <div class="print-grid-website">' . $post_website. '</div>
                            </div>
                            <div class="print-grid-logo">
                                 <img src="' . $post_image . '">
                            </div>
                            <div class="print-grid-details-content">
                                <div class="print-grid-details-content-inner">
                                    <div class="print-grid-details-primary">' . $post_primary . '</div>
                                    <div class="print-grid-details-secondary">' . $post_secondary . '</div>
                                    <div class="print-grid-details-tertiary">' . $post_tertiary . '</div>
                                </div>
                            </div>
                            <div class="print-grid-contact-content">
                                <div class="print-grid-contact-content-inner">
                                    <div class="print-grid-contact-phone">' . $post_phone . '</div>
                                    <div class="print-grid-contact-address">' . $post_address . '</div>
                                    <div class="print-grid-contact-citystate">' . $post_city . ',  ' . $post_state . '  ' . $post_zipcode . '</div>
                                </div>
                            </div>
                            <div class="print-grid-disclaimer-wrapper">
                                <div class="print-grid-details-disclaimer">*' . $post_disclaimer . '</div>
                            </div>
                            <div class="print-grid-bottom-row">
                                <div class="print-grid-www">CouponsAndBeyond.com</div>
                                <div class="print-grid-expiration">Expires on ' . $post_expiration . '</div>
                            </div>
                        </div>
                    ';
                }// end if
            }//end foreach
        }// end if count > 0
        $print_deals = implode('', $print_deals);
        /*echo'
            <html>
                <head>
                    <title>www.CouponsandBeyond.com</title>
                    <link rel="stylesheet" id="print-css" href='.plugins_url().'/deal-search/css/print.css " type="text/css" media="all" </link>
                </head>
                <body>
                    <div id="print-wrapper">
                        '.$print_deals.'
                    </div>
                </body>
            </html>';*/
        echo $print_deals;
        die();
    }
}
add_action( 'wp_ajax_printDeals', 'printDeals' );
add_action( 'wp_ajax_nopriv_printDeals', 'printDeals' );

/* AJAX GET ZIPCODES WITHIN RADIUS */
function get_zips() {
    global $wpdb;
    $zipcode = $_REQUEST['zipcode'];
    $miles = $_REQUEST['miles'];
    $defaultCity = $_REQUEST['defaultCity'];
    $defaultState = $_REQUEST['defaultState'];

    // CHECK IF ZIPCODE IS ACTUALLY CITY, STATE
    if(!is_numeric($zipcode)) {
        if (strpos($zipcode, ',') !== false) {
            $location = explode(',', $zipcode);
            $city = $location[0];
            $state = trim($location[1]);
        } else {
            $city = $zipcode;
            $state = $defaultState;
        }
    }

    $querystr = '';
    if($city && $state) {
        $querystr = "
            SELECT " . $wpdb->prefix . "geo_usa.postal_code
            FROM " . $wpdb->prefix . "geo_usa
            WHERE (" . $wpdb->prefix . "geo_usa.name = '$city'
            OR find_in_set('$city', " . $wpdb->prefix . "geo_usa.name_other_long)
            OR find_in_set('$city', " . $wpdb->prefix . "geo_usa.name_other_abr))
            AND (" . $wpdb->prefix . "geo_usa.adm_name1 = '$state'
            OR " . $wpdb->prefix . "geo_usa.adm_code1 = '$state')
            LIMIT 1
        ";
    } else if($city) {
        $querystr = "
            SELECT " . $wpdb->prefix . "geo_usa.postal_code
            FROM " . $wpdb->prefix . "geo_usa
            WHERE " . $wpdb->prefix . "geo_usa.name = '$city'
            LIMIT 1
        ";
    } else {
        $querystr = "
            SELECT " . $wpdb->prefix . "geo_usa.postal_code
            FROM " . $wpdb->prefix . "geo_usa
            WHERE " . $wpdb->prefix . "geo_usa.postal_code = '$zipcode'
            LIMIT 1
        ";
    }

    require plugin_dir_path( __FILE__ ) . 'includes/class-deal-search.php';

    $z = new Zipcode;
    $geoResults = $wpdb->get_results($querystr, OBJECT);

    if($geoResults) {
        foreach($geoResults as $geoResult) {
            $citytozip = $geoResult->postal_code;
        }

        $zips = $z->get_zips_in_range($citytozip, $miles, _ZIPS_SORT_BY_DISTANCE_ASC, true);
        $delimiter = ",";
        if ($zips == false) {
            echo 'error';
        } else {
            $elements = array();
            foreach ($zips as $key => $value) {
                $elements[] = "$key$delimiter";
            }
        }

        $zipper = implode("", $elements);
        $zipcodes = substr($zipper, 0, -1);

        echo $zipcodes;
    }

    die();
}
add_action( 'wp_ajax_get_zips', 'get_zips' );
add_action( 'wp_ajax_nopriv_get_zips', 'get_zips' );