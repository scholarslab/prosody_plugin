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

    $tei = get_post_meta( $post->ID, 'Original Document', true );

    $xml_doc = new DOMDocument();
    $xml_doc->loadXML( $tei );

    $xsl_doc = new DOMDocument();
    $xsl_doc->load( plugins_url( 'prosody.xsl', __FILE__ ) );

    $proc = new XSLTProcessor();
    $proc->importStylesheet( $xsl_doc );
    $newdom = $proc->transformToDoc( $xml_doc );
    // $newhtml = $newdom->saveHTML();

    $my_post = array(
        'ID' => $post->ID,
        'post_content' => $newdom->saveXML(),
    );

    wp_update_post( $my_post, true );


    // print $newdom->saveXML();
    // print $tei;

    // $post->post_content = $newdom->saveXML();


    // $post_url = post_permalink( $post );
    // wp_redirect( $post_url ); exit;

    // Consider adding initial xml into post meta field, then transforming and filling in post content

}

// Adds meta box
add_action( 'add_meta_boxes', 'prosody_source_poem' );

function prosody_source_poem ()
{
    add_meta_box(
        'prosody_source_poem',
        __('Original Document:', 'prosody'),
        'prosody_source_poem_meta_box',
        'prosody_poem'
    );
}

// Prints meta box
function prosody_source_poem_meta_box($post=null)
{
    $post = (is_null($post)) ? get_post() : $post;

    //Add a nonce field so we can check for it later
    wp_nonce_field(
        'prosody_source_poem_meta_box',
        'prosody_source_poem_meta_box_nonce'
    );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'Original Document', true);

    echo '<label for="prosody_source_poem">';
    __( 'Original Document:', 'prosody' );
    echo '</label> ';
    echo '<input type="text" id="prosody_source_poem" '
    . 'name="prosody_source_poem" value="' . esc_attr( $value ) .
    '" size="100" />';
}


// Saves meta data
add_action( 'save_post_prosody_poem', 'prosody_source_poem_save_meta_box_data');
/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function prosody_source_poem_save_meta_box_data ($post_id=null)
{
    $post_id = (is_null($post_id)) ? get_post()->ID : $post_id;

    /*
     * We need to verify this came from our screen and with proper
     * authorization, because the save_post action can be triggered at other
     * times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['prosody_source_poem_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    $nonce = $_POST['prosody_source_poem_meta_box_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'prosody_source_poem_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't
    // want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) &&
       'prosody_poem' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, its safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['prosody_source_poem'] ) ) {
        return;
    }

    // Sanitize user input.
    // $my_data = sanitize_text_field( $_POST['prosody_source_poem'] );
    $my_data = $_POST['prosody_source_poem'];

    // Update the meta field in the database.
    update_post_meta( $post_id, 'Original Document', $my_data );
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







