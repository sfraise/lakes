/**
 * Created by Spencer on 8/5/2016.
 *
 * Use dealScript.pluginUrl for the plugin url path (set at the top of deal-search.php)
 *
 * zipcodeapi.com api key: xJKVOGOGIbFwtLcdCQNX0MnO5bfRtAT0jEW8lLO3WuAqgMESDrPDedskpHbSi5TC
 * zipcodeapi.com api client key: js-UimOwmxri3G4adcs9t66eQFvqdMEeOz96VXDhu4AyKFIYRb8kiez8U5z8RenGNwM
 */

var markers = [];
var printWindow;
jQuery(document).ready(function() {
    /*** MAP ***/
    if(dealScript.frontPage == 1) {
        jQuery(function ($) {
            // Asynchronously Load the map API
            var script = document.createElement('script');
            script.src = "//maps.googleapis.com/maps/api/js?key=AIzaSyDB7aPwOTxf2aUYCm2ooahyOnt0dAk__7o&callback=initialize";
            document.body.appendChild(script);
        });
    }

    // CHECK VISIBILITY STATUS OF BROWSER
    var hidden, state, visibilityChange, isHidden;
    if (typeof document.hidden !== "undefined") {
        hidden = "hidden";
        visibilityChange = "visibilitychange";
        state = "visibilityState";
    } else if (typeof document.mozHidden !== "undefined") {
        hidden = "mozHidden";
        visibilityChange = "mozvisibilitychange";
        state = "mozVisibilityState";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
        state = "msVisibilityState";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
        state = "webkitVisibilityState";
    }

    /*** IPHONE FIXED HEADER INPUT FOCUS FIX ***/
    if (jQuery('html').hasClass('touch')) {

        /* cache dom referencess */
        var $body = jQuery('body');

        /* bind events */
        jQuery(document)
            .on('focus', 'input', function(e) {
                console.log('focus on input');
                $body.addClass('fixfixed');
            })
            .on('blur', 'input', function(e) {
                console.log('blur out of input');
                $body.removeClass('fixfixed');
            });

    }

    /*** HIDE THE LIST VIEW IF UNDER 800PX ***/
    var windowWidth = jQuery(window).width();
    if(windowWidth <= 800) {
        jQuery('.coupon-tabs').find('[href="#grid-view"]').trigger('click');
        jQuery('#list-view').hide();
    }

    /*** TOOL TIPS ***/
    jQuery(document).on('click', '.deal-grid-detail-link', function () {
        var thisGridElm = jQuery(this).closest('.deal-grid-item');
        var detailsContentEle = thisGridElm.children('.deal-grid-details-content');
        var contactContentEle = thisGridElm.children('.deal-grid-contact-content');
        var allContentEle = jQuery('.deal-grid-details-content,.deal-grid-contact-content,.deal-grid-share-content');


        // CLOSE PREVIOUS TOOL TIPS IF OPEN
        if(allContentEle.is(':visible')) {
            allContentEle.fadeOut();
        }
        if(contactContentEle.is(':visible')) {
            contactContentEle.fadeOut();
        }
        // OPEN THIS TOOL TIP
        detailsContentEle.fadeIn();
    });
    jQuery(document).on('click', '.deal-grid-contact-link', function () {
        var thisGridElm = jQuery(this).closest('.deal-grid-item');
        var detailsContentEle = thisGridElm.children('.deal-grid-details-content');
        var contactContentEle = thisGridElm.children('.deal-grid-contact-content');
        var allContentEle = jQuery('.deal-grid-details-content,.deal-grid-contact-content,.deal-grid-share-content');


        // CLOSE PREVIOUS TOOL TIPS IF OPEN
        if(allContentEle.is(':visible')) {
         allContentEle.fadeOut();
         }
        if(detailsContentEle.is(':visible')) {
            detailsContentEle.fadeOut();
        }

        // OPEN THIS TOOL TIP
        contactContentEle.fadeIn();
    });

    jQuery(document).on('click', '.deal-share-button', function () {
        jQuery( 'body' ).trigger( 'post-load' );
        var thisGridElm = jQuery(this).closest('.deal-grid-item');
        var detailsContentEle = thisGridElm.children('.deal-grid-details-content');
        var contactContentEle = thisGridElm.children('.deal-grid-contact-content');
        var shareContentEle = thisGridElm.children('.deal-grid-share-content');
        var allContentEle = jQuery('.deal-grid-details-content,.deal-grid-contact-content,.deal-grid-share-content');


        // CLOSE PREVIOUS TOOL TIPS IF OPEN
        if(allContentEle.is(':visible')) {
            allContentEle.fadeOut();
        }

        // OPEN THIS TOOL TIP
        shareContentEle.fadeIn();
    });
    jQuery(document).on('click','.tt-close',function(){
        jQuery(this).parent().fadeOut();
    });

    var toolTipHandler = function(event) {
        // if the target is a descendent of container do nothing
        if(jQuery(event.target).is(".deal-grid-details-content > *,.deal-grid-info-button,.deal-grid-contact-content,.deal-grid-share-content,.deal-share-button *,.deal-grid-button-icon")) return;

        var toolTipsEle = jQuery('.deal-grid-details-content,.deal-grid-contact-content,.deal-grid-share-content');

        if(toolTipsEle.is(':visible')) {
            toolTipsEle.fadeOut();
        }
    };
    jQuery(document).on("click", toolTipHandler);

    // SET IS HIDDEN
    document.addEventListener(visibilityChange, function () {
        searchInputSize();
    }, false);

    jQuery(window).on('resize', function () {
        searchInputSize();

        // OPEN GRID VIEW AND HIDE LIST VIEW UNDER 800PX
        if(dealScript.frontPage == 1) {
            var windowWidth = jQuery(window).width();

            if(windowWidth <= 800) {
                jQuery('.coupon-tabs').find('[href="#grid-view"]').trigger('click');
                jQuery('#list-view').hide();
            } else {
                jQuery('#list-view').show();
            }
        }
    });

    // ADJUST INPUT/SELECT WIDTHS ON MOBILE DEVICE ORIENTATION CHANGE
    window.addEventListener("orientationchange", function () {
        searchInputSize();
    }, false);

    /*** DEAL SEARCH ***/
    jQuery('.deal_search_submit').on('click', function () {
        markers = [];
        var category = jQuery('.deal_search_cat_select').val();
        var catName = jQuery('.deal_search_cat_select option:selected').attr('data');
        var text = jQuery('#deal_search_text').val();
        var miles = jQuery('#deal_search_radius_miles').val();
        var zipcode = jQuery('#deal_search_radius_zipcode').val();
        var defaultState = jQuery('#deal_search_default_state').val();

        // RESET PAGE TO 1
        jQuery('#deal_search_page').val('1');

        updateDeals(category, catName, text, miles, zipcode, defaultState, 1);
    });

    jQuery('#deal_search_text, #deal_search_radius_zipcode').keyup(function(e){
        if(e.keyCode == 13) {
            jQuery('.deal_search_submit').trigger('click');
        }
    });

    /*** SAVE DEAL ***/
    jQuery(document).on('click', '.deal-save-button', function () {
        // GET THE POST ID
        var postId = jQuery(this).parent().parent().attr('data');

        // READ EXISTING COOKIE
        var deals = readCookie('deal');

        if (deals && deals.indexOf(postId) == -1) {
            // ADD THIS ITEM TO THE EXISTING COOKIE
            deals = deals + ',' + postId;
        } else if (!deals) {
            // SET THIS ITEM FOR NEW COOKIE
            deals = postId;
        }

        // CREATE/UPDATE COOKIE
        createCookie('deal', deals, 7);

        // COUNT ITEMS
        var dealsNum = deals.split(',').length;

        // UPDATE SAVED DEALS VALUE IN SEARCH BAR
        jQuery('#deal_search_saved_items').html(dealsNum);
    });

    /*** REMOVE SAVED DEAL ***/
    jQuery('body').on('click', '#remove-deal', function () {

        // READ EXISTING COOKIE
        var deals = readCookie('deal');

        // CONVERT THE STRING TO AN ARRAY
        var dealsArr = deals.split(',');

        // SET NEW DEALS ARRAY
        var newDeals = [];

        jQuery( ".saved-ckbox:not(':checked')").each(function() {
            var postId = jQuery(this).attr('data-postId');
            newDeals.push(postId);

        });

        //console.log(newDeals);
        // UPDATE THE SAVED DEALS COUNTER
        var newCount = newDeals.length;
        jQuery('#deal_search_saved_items').html(newCount);

        // CONVERT THE NEWDEALS ARRAY TO COMMA DELIMITED STRING
        newDeals = newDeals.join(',');

        // UPDATE COOKIE
        if(newCount > 0) {
            //alert('true');
            createCookie('deal', newDeals, 7);
        }else{
            //alert('false');
            createCookie('deal', null, -1);
        }

        // REFRESH SAVED DEALS
        get_saved_deals();
    });

    /*** OPEN SAVED DEALS MODAL ***/
    jQuery('.deal_search_cart_info_saved').click(function () {
       /* // TODO: OPEN SAVED DEALS MODAL WINDOW
        jQuery(document.body).append('<div id="modal-saved" class="modal"><div class="modal-content"><span class="close">X</span><div id="modal-saved-content"></div></div></div>');

        get_saved_deals();*/
        jQuery(document.body).append('<div id="modal-saved" class="modal"><div class="modal-content"><span class="close">X</span><div id="modal-saved-content-loading" style="text-align: center; padding: 50px"><img src="' + dealScript.pluginUrl + '/images/loading/loading11.gif" alt="loading" /></div><div id="modal-saved-content" style="display: none"></div></div></div>');
        get_saved_deals();
        setTimeout(function () {
            jQuery('#modal-saved-content-loading').hide();
            jQuery('#modal-saved-content').show();
        },3000);

    });

    function get_saved_deals(){
        // READ EXISTING COOKIE
        var deals = readCookie('deal');
        // CONVERT THE STRING TO AN ARRAY
        if(deals){
        //var dealsArr = deals.split(',');

            jQuery.ajax({
                url: ajax.ajax_url,
                data: {
                    'action': 'getSavedDeals',
                    'deals': deals
                },
                success: function (data) {
                    // This outputs the result of the ajax request
                    jQuery("#modal-saved-content").html(data);
                    //console.log(data);
                    jQuery("#modal-saved").css('display', 'block');
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        }else{
            jQuery("#modal-saved-content").html('No deals');
        }
    }

    /*** CLOSE SAVED DEALS MODAL ***/
    jQuery("body").on('click', '.close', function () {
        jQuery("#modal-saved").hide();
        jQuery("body #modal-saved").remove();
    });

    /*** CHECK OR UNCHECK ALL ***/
    jQuery("body").on('change', '#select-all', function() {

        if( jQuery( '#select-all' ).prop( "checked" ) ) {
            // Iterate each checkbox
            jQuery( "input[type='checkbox']").each(function() {
                this.checked = true;
            });
        }
        else {
            jQuery( "input[type='checkbox']").each(function() {
                this.checked = false;
            });
        }
    });

    /*** OPEN PRINT DEALS MODAL ***/
    jQuery('.deal_search_cart_print_button').click(function() {
        // OPEN PRINT SAVED DEALS MODAL WINDOW
        jQuery(document.body).append('<div id="modal-saved" class="modal"><div class="modal-content"><span class="close">X</span><div id="modal-saved-content-loading" style="text-align: center; padding: 50px"><img src="' + dealScript.pluginUrl + '/images/loading/loading11.gif" alt="loading" /></div><div id="modal-saved-content" style="display: none"></div></div></div>');
        get_saved_deals();
        setTimeout(function () {
            jQuery('#modal-saved-content-loading').hide();
            jQuery('#modal-saved-content').show();
            jQuery("input:checkbox.saved-ckbox, #select-all").each(function () {
                this.checked = true;
            });

        },3000);
    });

    jQuery('body').on('click', '#print-saved', function () {
        // SET NEW DEALS ARRAY
        var printDealsArray = [];

        jQuery( ".saved-ckbox:checked").each(function() {
            var postId = jQuery(this).attr('data-postId');
            printDealsArray.push(postId);

        });
        printDeals(printDealsArray);

    });

    jQuery('body').on('click', '.deal-print-button', function () {
        // SET NEW DEALS ARRAY
        var printDealsArray = [];
        var postId = jQuery(this).closest('.deal-grid-item').attr('data');
        printDealsArray.push(postId);
        printDeals(printDealsArray);

    });

    /*** IF HOME PAGE ***/
    if(dealScript.frontPage == 1) {
        /*** CHANGE SEARCH BAR TO FIXED TOP ON SCROLL ***/
        var searchEle = jQuery('.deal_search_input_wrapper');

        jQuery(window).on('scroll', function(e) {
            var scrollPos = jQuery(window).scrollTop();

            if (scrollPos > 228) {
                searchEle.appendTo('.title-area');
                searchInputSize();
            } else {
                searchEle.appendTo('.deal_search');
                searchInputSize();
            }
        });

        /*** PAGINATION ***/
        jQuery(window).on("scroll", function () {
            var scrollHeight = jQuery(document).height();
            var scrollPosition = jQuery(window).height() + jQuery(window).scrollTop();
            if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
                var category = jQuery('.deal_search_cat_select').val();
                var catName = jQuery('.deal_search_cat_select option:selected').attr('data');
                var text = jQuery('#deal_search_text').val();
                var miles = jQuery('#deal_search_radius_miles').val();
                var zipcode = jQuery('#deal_search_radius_zipcode').val();
                var defaultState = jQuery('#deal_search_default_state').val();
                var resultsFound = jQuery('#deal_search_results_found').val();
                var page = jQuery('#deal_search_page').val();
                page = ++page;

                appendDeals(category, catName, text, miles, zipcode, defaultState, page, resultsFound);
            }
        });

        /*** REINITIALIZE MAP WHEN CLICKING ON MAP TAB ***/
        jQuery('.coupon-tabs a').click(function() {
            var tabId = jQuery(this).attr('href');

            if(tabId == '#map-view') {
                initialize();
            }
        });
    }

    // LOCATION AUTO SUGGEST
    jQuery(document).on('keyup', '#deal_search_radius_zipcode', function() {
        var city = jQuery(this).val();

        //if(city.length >= 3) {
            jQuery.ajax({
                url: ajax.ajax_url,
                data: {
                    'action': 'location_suggest',
                    'city': city
                },
                success: function (data) {
                    jQuery('.location-suggestion-wrapper').html(data);
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        //}
    });

    // SELECT LOCATION SUGGESTION
    jQuery(document).on('click', '.location-suggestion', function() {
        var suggestion = jQuery(this).attr('data');

        jQuery('#deal_search_radius_zipcode').val(suggestion);

        // CLEAR SUGGESTIONS
        jQuery('.location-suggestion-wrapper').html('');
    });

    // CLEAR SUGGESTIONS WHEN CLICKING OFF ELEMENT
    jQuery(document).mouseup(function (e) {
        var container = jQuery(".location-suggestion-wrapper");

        if (!container.is(e.target) // if the target of the click isn't the container...
            && container.has(e.target).length === 0) // ... nor a descendant of the container
        {
            container.html('');
        }
    });

    // PREPARE TITLE AREA TO HOLD SEARCH
    jQuery('.title-area').html('');

    // MOVE SAVED DEALS TO MENU BAR
    jQuery('.deal_search_saved_menu').html('');
    jQuery('.deal_search_cart_info').appendTo('.deal_search_saved_menu');

    // MOVE RADIUS OPTIONS ABOVE RESULTS
    jQuery('.deal_search_radius_wrapper').appendTo('.deal-grid-offer-radius');
});

/*** ADJUST INPUT/SELECT WIDTHS, SHOW FEATURED DEALS AND SET INITIAL SAVED DEALS COUNT ON WINDOW LOAD ***/
jQuery(window).load(function() {
    var inputWrapper = jQuery('.deal_search_input_wrapper');
    jQuery('.deal_search_loading_placeholder').hide();
    inputWrapper.show();
    jQuery('.deal_search_radius_wrapper').show();

    searchInputSize();

    // READ DEALS FROM COOKIE
    var deals = readCookie('deal');

    // COUNT SAVED DEAL ITEMS
    var dealsNum = 0;
    if(deals) {
        dealsNum = deals.split(',').length;
    }

    // UPDATE SAVED DEALS VALUE IN SEARCH BAR
    jQuery('#deal_search_saved_items').html(dealsNum);

    // LOAD DEALS ON PAGE LOAD
    if(dealScript.frontPage == 1) {
        var redirCategory = $_GET('category');
        var redirCatName = $_GET('catname');
        var redirText = $_GET('text');
        var redirMiles = $_GET('miles');
        var redirZipcode = $_GET('zipcode');

        var category = jQuery('.deal_search_cat_select').val();
        var catName = jQuery('.deal_search_cat_select option:selected').attr('data');
        var text = jQuery('#deal_search_text').val();
        var miles = jQuery('#deal_search_radius_miles').val();
        var zipcode = jQuery('#deal_search_radius_zipcode').val();
        var defaultState = jQuery('#deal_search_default_state').val();
        var page = jQuery('#deal_search_page').val();

        if(redirCategory || redirCatName || redirText || redirMiles || redirZipcode) {
            // UPDATE THE SEARCH BAR VALUES
            jQuery('[id=deal_search_cat_select] option').filter(function() {
                return (jQuery(this).val() == redirCategory); //To select Blue
            }).prop('selected', true);
            jQuery('#deal_search_text').val(redirText);
            jQuery('[id=deal_search_radius_miles] option').filter(function() {
                return (jQuery(this).val() == redirMiles); //To select Blue
            }).prop('selected', true);
            jQuery('#deal_search_radius_zipcode').val(redirZipcode);

            updateDeals(redirCategory, redirCatName, redirText, redirMiles, redirZipcode, defaultState, 1);

            // Remove Ugly URL Parameters from Address Bar
            if (window.parent.location.href.match(/catname/)){
                if (typeof (history.pushState) != "undefined") {
                    var obj = { Title: document.title, Url: window.parent.location.pathname };
                    history.pushState(obj, obj.Title, obj.Url);
                } else {
                    window.parent.location = window.parent.location.pathname;
                }
            }
        } else {
            updateDeals(category, catName, text, miles, zipcode, defaultState, page);
        }
    }
});

/*** SEARCH INPUT SIZE ***/
function searchInputSize() {
    var windowWidth = jQuery(window).width();
    var selectEle = jQuery('.deal_search_cat_select');

    // SET ELEMENTS
    var textSearch = jQuery('#deal_search_text');

    // SET VALUES
    var parentWidth = jQuery('.deal_search_input_wrapper').width();
    var selectWidth = 0;
    var i = 0;

    if(windowWidth > 768) {
        // ADJUST INPUT/SELECT WIDTHS
        selectEle.each(function () {
            var widthTmp = jQuery('#width_tmp');
            widthTmp.html(jQuery('option:selected', this).text());
            jQuery(this).width(widthTmp.width() + 30); // 30 : the size of the down arrow of the select box
            selectWidth = +jQuery(this).width() + +selectWidth;
            i++;
        });
    }

    var wrapper = jQuery('.deal_search_cat_select_wrapper').width();
    var newInputWidth = parentWidth - wrapper - 50;

    textSearch.outerWidth(newInputWidth);
}

/*** UPDATE DEALS FUNCTION ***/
function updateDeals(category, catName, text, miles, zipcode, defaultState, page) {
    if(dealScript.frontPage == 1) {
        // GET CURRENT PAGE IF NO VALUE
        if(!page) {
            page = 1;
        }

        // GET ZIPCODES IN RADIUS
        var radiusZips;
        if(miles && zipcode) {
            getRadius(zipcode, miles).then(function (response) {
                // WAIT FOR PROMISE RESPONSE BEFORE CONTINUING
                radiusZips = response;
                updateDealsAjax(category, catName, text, miles, zipcode, radiusZips, page, 'update');
            });
        } else {
            updateDealsAjax(category, catName, text, miles, zipcode, '', page, 'update');
        }
    } else {
        window.location = dealScript.siteUrl + '?category=' + category + '&catname=' + catName + '&text=' + text + '&miles=' + miles + '&zipcode=' + zipcode + '&defaultState=' + defaultState;
    }
}

/*** APPEND DEALS FUNCTION ***/
function appendDeals(category, catName, text, miles, zipcode, defaultState, page, resultsFound) {
    // GET CURRENT PAGE IF NO VALUE
    if(!page) {
        page = 1;
    }

    // GET ZIPCODES IN RADIUS
    var radiusZips;
    if(miles && zipcode) {
        getRadius(zipcode, miles).then(function (response) {
            // WAIT FOR PROMISE RESPONSE BEFORE CONTINUING
            radiusZips = response;

            updateDealsAjax(category, catName, text, miles, zipcode, radiusZips, page, 'append', resultsFound);
        });
    } else {
        updateDealsAjax(category, catName, text, miles, zipcode, '', page, 'append', resultsFound);
    }
}

/*** UPDATE DEALS AJAX CALL ***/
function updateDealsAjax(category, catName, text, miles, zipcode, radiusZips, page, type, resultsFound) {
    // This does the ajax request
    if(type == 'update') {
        jQuery('.deal-list-view .vc_tta-panel-body').html('<div class="ajax-loading"><img src="' + dealScript.pluginUrl + '/images/loading/loading7.gif" alt="loading" /></div>');
        jQuery('.deal-grid-view .vc_tta-panel-body').html('<div class="ajax-loading"><img src="' + dealScript.pluginUrl + '/images/loading/loading7.gif" alt="loading" /></div>');
    }
    jQuery.ajax({
        url: ajax.ajax_url,
        data: {
            'action':'update_deals',
            'category' : category,
            'catname' : catName,
            'text' : text,
            'miles' : miles,
            'zipcode' : zipcode,
            'zipcodes' : radiusZips,
            'page' : page,
            'resultsFound' : resultsFound
        },
        success:function(data) {
            if(!catName) {
                catName = 'Recommended';
            }

            var dealData = JSON.parse(data);

            // UPDATE DEALS
            if(type == 'append') {
                jQuery('.deal-list-view .vc_tta-panel-body').append(dealData.deals);
                jQuery('.deal-grid-view .vc_tta-panel-body').append(dealData.deals);
            } else {
                jQuery('.deal-list-view .vc_tta-panel-body').html(dealData.deals);
                jQuery('.deal-grid-view .vc_tta-panel-body').html(dealData.deals);
            }

            // UPDATE PAGE NUMBER
            jQuery('#deal_search_page').val(page);

            // UPDATE NOTICE AND DEAL TYPE
            if(dealData.notice) {
                jQuery('#deal_search_results_found').val('0');
            } else {
                jQuery('#deal_search_results_found').val('1');
            }
            jQuery('.deal-grid-notice').html(dealData.notice);
            jQuery('.deal-grid-offer-type').html(catName + ' Offers');

            initialize(dealData.mapData);
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
}

/*** COOKIE FUNCTIONS ***/
function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name,"",-1);
}

function $_GET(param) {
    var vars = {};
    window.location.href.replace( location.hash, '' ).replace(
        /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
        function( m, key, value ) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );

    if ( param ) {
        return vars[param] ? vars[param] : null;
    }
    return vars;
}

/*** RADIUS FUNCTION ***/
function getRadius(zipcode, miles) {
    // SET PROMISE
    return new Promise(function(resolve, reject) {
        /*
         We store the zipcodes in a hidden input to reuse if miles or zipcode has not changed since last search
         to prevent unneeded api calls. This is importante as we are limited to 50 requests per hour on the free
         subscription with zipcodeapi.com.
         */
        var lastZipEle = jQuery('#deal_search_radius_zipcode_last');
        var lastMilesEle = jQuery('#deal_search_radius_miles_last');
        var zipArrayEle = jQuery('#deal_search_radius_zipcode_array');
        var defaultCity = jQuery('#deal_search_default_city').val();
        var defaultState = jQuery('#deal_search_default_state').val();
        var apiKey = 'xJKVOGOGIbFwtLcdCQNX0MnO5bfRtAT0jEW8lLO3WuAqgMESDrPDedskpHbSi5TC';
        var clientKey = 'js-UimOwmxri3G4adcs9t66eQFvqdMEeOz96VXDhu4AyKFIYRb8kiez8U5z8RenGNwM';
        var zipcodes = [];

        if(lastZipEle.val() == zipcode && lastMilesEle.val() == miles) {
            resolve(zipArrayEle.val().split(','));
        } else {
            jQuery.ajax({
                url: ajax.ajax_url,
                data: {
                    'action' : 'get_zips',
                    'zipcode' : zipcode,
                    'defaultCity' : defaultCity,
                    'defaultState' : defaultState,
                    'miles' : miles
                },
                success:function(data) {
                    // UPDATE HIDDEN INPUTS TO COMPARE AGAINST NEXT TIME
                    lastZipEle.val(zipcode);
                    lastMilesEle.val(miles);
                    zipcodes = data;
                    zipArrayEle.val(zipcodes);

                    resolve(zipcodes);
                },
                error: function(errorThrown){
                    reject(errorThrown);
                }
            });
        }
    });
}

/*** MAP FUNCTION ***/
function initialize(mapData) {
    var addresses = [];

    jQuery.each(mapData, function() {
        var company;
        var address;
        var city;
        var state;
        var zipcode;

        jQuery.each(this, function(index, value) {
            if(index == 'company') {
                company = value;
            } else if(index == 'address') {
                address = value;
            } else if(index == 'city') {
                city = value;
            } else if(index == 'state') {
                state = value;
            } else if(index == 'zipcode') {
                zipcode = value;
            }
        });

        var thisAddress = address + ' ' + city + ', ' + state + ' ' + zipcode;
        addresses.push(thisAddress);

        var markerInfo = '<div class="info_content"><h3>' + company + '</h3><p>' + thisAddress + '</p></div>';

        /*** CONVERT ADDRESS TO LAT AND LONG FUNCTION ***/
        var geocoder = new google.maps.Geocoder();

        geocoder.geocode( { 'address': thisAddress}, function(results, status) {

            if (status == google.maps.GeocoderStatus.OK) {
                var latitude = results[0].geometry.location.lat();
                var longitude = results[0].geometry.location.lng();

                markers.push({'address' : thisAddress, 'latitude' : latitude, 'longitude' : longitude, 'markerinfo' : markerInfo});
            }
        });
    });

    setTimeout(function() {
        var map;
        var bounds = new google.maps.LatLngBounds();
        var mapOptions = {
            mapTypeId: 'roadmap'
        };
        // Display a map on the page
        map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        map.setTilt(45);

        // Display multiple markers on a map
        var infoWindow = new google.maps.InfoWindow(), marker, i;

        // Loop through our array of marker objects & place each one on the map
        i = 0;
        jQuery.each(markers, function() {
            var address;
            var latitude;
            var longitude;
            var markerInfo;

            jQuery.each(this, function(index, value) {
                if(index == 'address') {
                    address = value;
                } else if(index == 'latitude') {
                    latitude = value;
                } else if(index == 'longitude') {
                    longitude = value;
                } else if(index == 'markerinfo') {
                    markerInfo = value;
                }
            });

            var position = new google.maps.LatLng(latitude,longitude);
            bounds.extend(position);
            marker = new google.maps.Marker({
                position: position,
                map: map,
                title: address
            });
            // Allow each marker to have an info window
            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                return function () {
                    infoWindow.setContent(markerInfo);
                    infoWindow.open(map, marker);
                }
            })(marker, i));
            // Automatically center the map fitting all markers on the screen
            map.fitBounds(bounds);

            i++;
        });

        // Override our map zoom level once our fitBounds function runs (Make sure it only runs once)
        var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function (event) {
            this.setZoom(9);
            google.maps.event.removeListener(boundsListener);
        });
    },500);
}

function printDeals(printDealsArray){
    if(printDealsArray){
        jQuery.ajax({
            url: ajax.ajax_url,
            data: {
                'action': 'printDeals',
                'deals': printDealsArray
            },
            success: function (data) {
                // This outputs the result of the ajax request
                printWindow = window.open("", "printWindow");
                printWindow.document.write("<!DOCTYPE html>");
                printWindow.document.write("<html>");
                printWindow.document.write("<head>");
                printWindow.document.write("<title>www.CouponsandBeyond.com</title>");
                printWindow.document.write("<link rel='stylesheet' id='print-css' href='"+dealScript.pluginUrl+"css/print.css' type='text/css' />");
                //printWindow.document.write("<link rel='stylesheet' id='print-css' href='"+dealScript.pluginUrl+"css/print.css' type='text/css' media='all' />");

                printWindow.document.write("</head>");
                printWindow.document.write("<body>");
                printWindow.document.write('<div id="print-wrapper">');
                printWindow.document.write(data);
                printWindow.document.write("</div>");
                printWindow.document.write("</body>");
                printWindow.document.write("</html>");

                printWindow.setTimeout(function(){
                    printWindow.stop();
                    printWindow.focus();
                    printWindow.print();
                    printWindow.close();
                },1000);
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    }
}
