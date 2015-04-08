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

//  Create custom post type for poems
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

// Transform xml in post meta to html in post_content when meta is updated
add_action( 'updated_post_meta', 'prosody_xml_transform');

function prosody_xml_transform ($post)
{
    global $post;

    if ( $post ) {


        $tei = get_post_meta( $post->ID, 'Original Document', true );

        $xml_doc = new DOMDocument();
        $xml_doc->loadXML( $tei );

        $xsl_doc = new DOMDocument();
        $xsl_doc->load( plugins_url( 'prosody.xsl', __FILE__ ) );

        $proc = new XSLTProcessor();
        $proc->importStylesheet( $xsl_doc );
        $newdom = $proc->transformToDoc( $xml_doc );

        $my_post = array(
            'ID' => $post->ID,
            'post_content' => $newdom->saveXML(),
        );

        if ( ! wp_is_post_revision( $post->ID ) ) {

            // unhook this function to prevent infinite loop
            remove_action( 'updated_post_meta', 'prosody_xml_transform');

            // update the post, which calls save_post again
            wp_update_post( $my_post, true );

            // re-hook this function
            add_action( 'updated_post_meta', 'prosody_xml_transform');

        }
    }
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
    '" size="50" />';
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

