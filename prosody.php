<?php

/**
 * Plugin Name: Prosody
 * Plugin URI:
 * Description:
 * Version: 0.0.1
 * Author: Scholars' Lab, University of Virginia
 * Author URI: http://scholarslab.org
 * Text Domain: Optional.
 * Domain Path: Optional.
 * Network: Optional.
 * License:
 */

add_action( 'init', 'prosody_create_post_type' );

function prosody_create_post_type ()
{
    register_post_type( 'prosody_poem',
        array(
            'labels' => array(
                'name' => __( 'Poems' ),
                'singular_name' => __( 'Poem' )
            ),
            'public' => true,
            'has_archive' => true,
        )
    );
}

// Will using the save action actually be a problem due to autosaving in WP?
add_action( 'save_post_prosody_poem', 'prosody_xml_transform');

function prosody_xml_transform ($post)
{
    global $post;

    $tei = $post->post_content;

    // Add check to see if there is post meta on field, if so update post meta, otherwise add post meta

    add_post_meta( $post, 'key', $tei );

    $original = get_post_meta( $post, 'key' );
    echo $original;


    // $post_url = post_permalink( $post );
    // wp_redirect( $post_url ); exit;

}

// Process:
// Option 1:
//      Create custom post type for Poem
//      Put TEI into Post content field
//      On Save action for Poem type:
//          Create XSLT Process, read in post content as xml file (can this be done?),
//          Read in XSL file
//          Transform to html
//          Attach post content (TEI) as post meta data (will need to define meta field)
//          Replace post content with generated html
//          Resave the post (For the second save, will need to check if html or tei - if tei, run xslt, if html, don't)

// Option 2:
//  Enable file upload with xml and xsl types for custom post type
//  Use XSLT Process, load in both files and transform
//  Set post content equal to generated html on save? Or is there an action that could be fired as soon the two files are uploade?

// Option 3:
// Instead of working on the server, just input TEI into post content field
// Use Saxon CE to transform in the client







