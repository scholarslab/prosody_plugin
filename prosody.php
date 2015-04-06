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

add_action( 'save_post_prosody_poem', 'prosody_xml_transform');

function prosody_xml_transform ($post)
{

}
