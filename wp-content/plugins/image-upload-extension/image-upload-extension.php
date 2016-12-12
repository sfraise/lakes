<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 4/11/2016
 * Time: 12:55 PM
 *
 * Plugin Name: Image Upload Extension
 * Plugin URI: http://www.codemonkeysllc.com/
 * Description: Adds custom fields to media upload manager and extracts data from images.
 * Version: 1.0
 * Author: Code Monkeys LLC
 * Author URI: http://www.codemonkeysllc.com
 * License: GPL
 *
 * Add Custom fields to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

/*
function be_attachment_field_credit( $form_fields, $post ) {
    $form_fields['be-company-name'] = array(
        'label' => 'Company Name',
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'be_company_name', true )
    );

    $form_fields['be-zip-code'] = array(
        'label' => 'Zip-Code',
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'be_zip_code', true )
    );

    $form_fields['be-categories'] = array(
        'label' => 'Categories',
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'be_categories', true )
    );

    return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'be_attachment_field_credit', 10, 2 );

function be_attachment_field_credit_save( $post, $attachment ) {
    if( isset( $attachment['be-company-name'] ) )
        update_post_meta( $post['ID'], 'be_company_name', $attachment['be-company-name'] );

    if( isset( $attachment['be-zip-code'] ) )
        update_post_meta( $post['ID'], 'be_zip_code', esc_url( $attachment['be-zip-code'] ) );

    if( isset( $attachment['be-categories'] ) )
        update_post_meta( $post['ID'], 'be_categories', esc_url( $attachment['be-categories'] ) );

    return $post;
}

add_filter( 'attachment_fields_to_save', 'be_attachment_field_credit_save', 10, 2 );


add_filter('wp_handle_upload_prefilter', 'custom_upload_filter' );

function custom_upload_filter( $file ){
    $image = new Imagick($file['tmp_name']);
    $format = $image->getImageFormat();

    if($format == 'PDF') {
        // EXTRACT ADOBE META DATA FUNCTION
        function getXmpData($filename, $tag)
        {
            $chunk_size = 50000;
            $buffer = NULL;

            if (($file_pointer = fopen($filename, 'r')) === FALSE) {
                throw new RuntimeException('Could not open file for reading');
            }

            $chunk = fread($file_pointer, $chunk_size);
            if (($posStart = strpos($chunk, '<' . $tag . '>')) !== FALSE) {
                $buffer = substr($chunk, $posStart);
                $posEnd = strpos($buffer, '</' . $tag . '>');
                $buffer = substr($buffer, 0, $posEnd + 12);
            }
            fclose($file_pointer);
            return $buffer;
        }

        // GET ADOBE META DATA
        $title = html_entity_decode(strip_tags(getXmpData($file['tmp_name'], 'dc:title')));
        $description = html_entity_decode(strip_tags(getXmpData($file['tmp_name'], 'dc:description')));
        $author = html_entity_decode(strip_tags(getXmpData($file['tmp_name'], 'dc:creator')));


    }

    return $file;
}
*/