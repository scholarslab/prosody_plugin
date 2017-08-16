<?php

/**
 * Plugin Name: Prosody
 * Plugin URI: https://github.com/scholarslab/prosody_plugin
 * Description: Provides functionality for For Better for Verse
 * Version: 1.0.0
 * Author: Scholars' Lab, University of Virginia
 * Author URI: http://scholarslab.org
 * License: http://www.apache.org/licenses/LICENSE-2.0.html
 * Copyright: 2015 Rector and Board of Visitors, University of Virginia
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
            'taxonomies' => array('category'),
            'supports' => array('title', 'editor', 'revisions')
        )
    );
}

// Transform xml in post meta to html in post_content when meta is updated
add_action( 'updated_post_meta', 'prosody_xml_transform');

function prosody_xml_transform ($post)
{
    global $post;

    if ( $post->post_type == 'prosody_poem' ) {

        $tei = get_post_meta( $post->ID, 'Original Document', true );
        $xslt = file_get_contents( plugin_dir_path( __FILE__ ) . 'preprocess.xsl' );
        $xslt = str_replace('[PLUGIN_DIR]', plugins_url( 'images', __FILE__), $xslt);

        $xml_doc = new DOMDocument();
        $xml_doc->loadXML( $tei );

        $xsl_doc = new DOMDocument();
        $xsl_doc->loadXML( $xslt );

        $proc = new XSLTProcessor();
        $proc->importStylesheet( $xsl_doc );
        $newdom = $proc->transformToDoc( $xml_doc );

        $my_post = array(
            'ID' => $post->ID,
            'post_content' => $newdom->saveXML(),
        );

        wp_update_post($my_post, true);

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

// Adds meta box for TEI source of poem
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

// Prints meta box for TEI source of poem
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
 * When the post is saved, saves the original TEI.
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

    // Sanitize user input. In this case we don't so the transform will work.
    // $my_data = sanitize_text_field( $_POST['prosody_source_poem'] );
    $my_data = $_POST['prosody_source_poem'];

    // Update the meta field in the database.
    update_post_meta( $post_id, 'Original Document', $my_data );
}

// Adds meta box for poem author
// Author will be left as a text box - whoever inputs the poems will need to make sure to keep spelling and capitalization consistent for any given author
add_action( 'add_meta_boxes', 'prosody_poem_author' );

function prosody_poem_author ()
{
    add_meta_box(
        'prosody_poem_author',
        __('Author (Lastname, Firstname):', 'prosody'),
        'prosody_poem_author_meta_box',
        'prosody_poem'
    );
}

// Prints meta box for poem author
function prosody_poem_author_meta_box($post=null)
{
    $post = (is_null($post)) ? get_post() : $post;

    //Add a nonce field so we can check for it later
    wp_nonce_field(
        'prosody_poem_author_meta_box',
        'prosody_poem_author_meta_box_nonce'
    );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'Author', true);

    echo '<label for="prosody_poem_author">';
    __( 'Author:', 'prosody' );
    echo '</label> ';
    echo '<input type="text" id="prosody_poem_author" '
    . 'name="prosody_poem_author" value="' . esc_attr( $value ) .
    '" size="50" />';
}


// Saves meta data
add_action( 'save_post_prosody_poem', 'prosody_poem_author_save_meta_box_data');

/**
 * When the post is saved, saves the poem author.
 *
 * @param int $post_id The ID of the post being saved.
 */
function prosody_poem_author_save_meta_box_data ($post_id=null)
{
    $post_id = (is_null($post_id)) ? get_post()->ID : $post_id;

    /*
     * We need to verify this came from our screen and with proper
     * authorization, because the save_post action can be triggered at other
     * times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['prosody_poem_author_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    $nonce = $_POST['prosody_poem_author_meta_box_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'prosody_poem_author_meta_box' ) ) {
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
    if ( ! isset( $_POST['prosody_poem_author'] ) ) {
        return;
    }

    // Sanitize user input. In this case we don't so the transform will work.
    $my_data = sanitize_text_field( $_POST['prosody_poem_author'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'Author', $my_data );
}

// Adds meta box for poem difficulty
// Difficulty categories: Warming Up, Moving Along, Special Challenge
add_action( 'add_meta_boxes', 'prosody_poem_difficulty' );

function prosody_poem_difficulty ()
{
    add_meta_box(
        'prosody_poem_difficulty',
        __('Difficulty:', 'prosody'),
        'prosody_poem_difficulty_meta_box',
        'prosody_poem'
    );
}

// Prints meta box for poem difficulty
function prosody_poem_difficulty_meta_box($post=null)
{
    $post = (is_null($post)) ? get_post() : $post;

    //Add a nonce field so we can check for it later
    wp_nonce_field(
        'prosody_poem_difficulty_meta_box',
        'prosody_poem_difficulty_meta_box_nonce'
    );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'Difficulty', true);

    echo '<label for="prosody_poem_difficulty">';
    __( 'Difficulty:', 'prosody' );
    echo '</label> ';
    echo '<select name="prosody_poem_difficulty" id="prosody_poem_difficulty">';
    echo '<option value="warming_up" ' . selected( $value, 'warming_up', false ) . '>Warming Up</option>';
    echo '<option value="moving_along" ' . selected( $value, 'moving_along', false ) . '>Moving Along</option>';
    echo '<option value="special_challenge" ' . selected( $value, 'special_challenge', false ) . '>Special Challenge</option>';
    echo '</select>';

}

// Saves meta data
add_action( 'save_post_prosody_poem', 'prosody_poem_difficulty_save_meta_box_data');

/**
 * When the post is saved, saves the poem difficulty.
 *
 * @param int $post_id The ID of the post being saved.
 */
function prosody_poem_difficulty_save_meta_box_data ($post_id=null)
{
    $post_id = (is_null($post_id)) ? get_post()->ID : $post_id;

    /*
     * We need to verify this came from our screen and with proper
     * authorization, because the save_post action can be triggered at other
     * times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['prosody_poem_difficulty_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    $nonce = $_POST['prosody_poem_difficulty_meta_box_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'prosody_poem_difficulty_meta_box' ) ) {
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
    if ( ! isset( $_POST['prosody_poem_difficulty'] ) ) {
        return;
    }

    // Sanitize user input. We don't need this since it's a dropdown.
    // $my_data = sanitize_text_field( $_POST['prosody_poem_difficulty'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'Difficulty', $_POST['prosody_poem_difficulty'] );
}

// Adds meta box for poem type
// Poem types: Ballad, Blank Verse, Cinquain, Couplet, Quatrain, Sixain, Song, Sonnet, Spenserian Stanza
add_action( 'add_meta_boxes', 'prosody_poem_type' );

function prosody_poem_type ()
{
    add_meta_box(
        'prosody_poem_type',
        __('Type:', 'prosody'),
        'prosody_poem_type_meta_box',
        'prosody_poem'
    );
}

// Prints meta box for poem type
function prosody_poem_type_meta_box($post=null)
{
    $post = (is_null($post)) ? get_post() : $post;

    //Add a nonce field so we can check for it later
    wp_nonce_field(
        'prosody_poem_type_meta_box',
        'prosody_poem_type_meta_box_nonce'
    );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'Type', true);

    echo '<label for="prosody_poem_type">';
    __( 'Type:', 'prosody' );
    echo '</label> ';
    echo '<select name="prosody_poem_type" id="prosody_poem_type">';
    echo '<option value="ballad" ' . selected( $value, 'ballad', false ) . '>Ballad</option>';
    echo '<option value="blank_verse" ' . selected( $value, 'blank_verse', false ) . '>Blank Verse</option>';
    echo '<option value="cinquain" ' . selected( $value, 'cinquain', false ) . '>Cinquain</option>';
    echo '<option value="couplet" ' . selected( $value, 'couplet', false ) . '>Couplet</option>';
    echo '<option value="octave" ' . selected( $value, 'octave', false ) . '>Octave</Ode>';
    echo '<option value="ode" ' . selected( $value, 'ode', false ) . '>Ode</Ode>';
    echo '<option value="quatrain" ' . selected( $value, 'quatrain', false ) . '>Quatrain</option>';
    echo '<option value="roundel" ' . selected( $value, 'roundel', false ) . '>Roundel</Ode>';
    echo '<option value="sixain" ' . selected( $value, 'sixain', false ) . '>Sixain</option>';
    echo '<option value="song" ' . selected( $value, 'song', false ) . '>Song</option>';
    echo '<option value="sonnet" ' . selected( $value, 'sonnet', false ) . '>Sonnet</option>';
    echo '<option value="spenserian_stanza" ' . selected( $value, 'spenserian_stanza', false ) . '>Spenserian Stanza</option>';
    echo '<option value="tercet" ' . selected( $value, 'tercet', false ) . '>Tercet</option>';
    echo '<option value="none" ' . selected( $value, 'none', false ) . '>None</option>';
    echo '</select>';
}


// Saves meta data
add_action( 'save_post_prosody_poem', 'prosody_poem_type_save_meta_box_data');

/**
 * When the post is saved, saves the poem type.
 *
 * @param int $post_id The ID of the post being saved.
 */
function prosody_poem_type_save_meta_box_data ($post_id=null)
{
    $post_id = (is_null($post_id)) ? get_post()->ID : $post_id;

    /*
     * We need to verify this came from our screen and with proper
     * authorization, because the save_post action can be triggered at other
     * times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['prosody_poem_type_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    $nonce = $_POST['prosody_poem_type_meta_box_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'prosody_poem_type_meta_box' ) ) {
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
    if ( ! isset( $_POST['prosody_poem_type'] ) ) {
        return;
    }

    // Sanitize user input. In this case we don't so the transform will work.
    $my_data = sanitize_text_field( $_POST['prosody_poem_type'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'Type', $my_data );
}

// Adds meta box for poem resources
add_action( 'add_meta_boxes', 'prosody_poem_resources' );

function prosody_poem_resources ()
{
    add_meta_box(
        'prosody_poem_resources',
        __('Resources:', 'prosody'),
        'prosody_poem_resources_meta_box',
        'prosody_poem'
    );
}

// Prints meta box for poem resources
function prosody_poem_resources_meta_box($post=null)
{
    $post = (is_null($post)) ? get_post() : $post;

    //Add a nonce field so we can check for it later
    wp_nonce_field(
        'prosody_poem_resources_meta_box',
        'prosody_poem_resources_meta_box_nonce'
    );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'Resources', true);

    echo '<label for="prosody_poem_resources_editor">';
    __( 'Resources:', 'prosody' );
    echo '</label> ';
    $settings = array( 'textarea_rows' => '15', 'tinymce' => true, 'media_buttons' => true, 'quicktags' => true, 'wpautop' => true );
    wp_editor( $value, "prosody_poem_resources_editor", $settings );
}


// Saves meta data
add_action( 'save_post_prosody_poem', 'prosody_poem_resources_save_meta_box_data');

/**
 * When the post is saved, saves the poem resources.
 *
 * @param int $post_id The ID of the post being saved.
 */
function prosody_poem_resources_save_meta_box_data ($post_id=null)
{
    $post_id = (is_null($post_id)) ? get_post()->ID : $post_id;

    /*
     * We need to verify this came from our screen and with proper
     * authorization, because the save_post action can be triggered at other
     * times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['prosody_poem_resources_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    $nonce = $_POST['prosody_poem_resources_meta_box_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'prosody_poem_resources_meta_box' ) ) {
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
    if ( ! isset( $_POST['prosody_poem_resources_editor'] ) ) {
        return;
    }

    // Sanitize user input. In this case we don't so the html will work.
    $my_data = $_POST['prosody_poem_resources_editor'];

    // Update the meta field in the database.
    update_post_meta( $post_id, 'Resources', $my_data );
}

// Enqeue the js and css
function prosody_plugin_queue_scripts ()
{

    wp_enqueue_style(
        'poem-css',
        plugin_dir_url( __FILE__ ) . 'css/poem.css',
        array(),
        null,
        false
        );

    wp_register_script(
        'handlers.js',
        plugins_url('js/handlers.js', __FILE__),
        array(),
        null,
        true
        );
    // Localize the script to pass in the siteurl
    wp_localize_script('handlers.js', 'WPURLS', array( 'siteurl' => home_url() ));
    wp_enqueue_script( 'handlers.js' );
}

add_action('wp_enqueue_scripts', 'prosody_plugin_queue_scripts');
