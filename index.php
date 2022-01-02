<?php
/**
 * Plugin Name: Restoration Timeline Wikidata Scraper
 * Plugin URI: https://restorationtimeline.com
 * Description: Enhance items using wikidata.
 * Version: 0.0.1
 * Author: David Petersen
 */

require 'vendor/autoload.php';

/**
 * Set up and add the meta box.
 */
function add_enhance_boxes() {
    add_meta_box(
        'enhance_box', // Unique ID
        'Enhance with Linked Data', // Box title
        'enhance_box_html', // Content callback, must be of type callable
        'post' // Post type
    );
}

/**
 * Display the meta box HTML to the user.
 * @param \WP_Post $post   Post object.
 */
function enhance_box_html($post) {
    $value = get_field('wikidata_qid', $post->ID, true);
    echo "<button id='enhance' type='button' class='button button-primary button-large' style='width:100%;'><span class='fusion-builder-button-text'>Enhance $value</span></button>";
}

/**
 * Enqueue a script in the WordPress admin on edit.php.
 * @param int $hook Hook suffix for the current admin page.
 */
function enqueue_admin_script($hook) {
    if ('post.php' != $hook) {
        return;
    }
    wp_enqueue_script('rt-wikidata', plugin_dir_url(__FILE__) . 'main.js', array(), '1.0');
}

function search_wiki($query) {
    if ($query === null || $query === '') {
        return false;
    }

    $url = "https://www.wikidata.org/w/api.php?search=" . urlencode($query) . "&action=wbsearchentities&language=en&type=item&limit=5&format=json&origin=*&sites=wikidata";
    $response = file_get_contents($url);
    $json = json_decode($response);
    return $json;
}
function get_wikidata_entity($qid) {
    if ($qid === null || $qid === '') {
        return false;
    }
    $url = 'https://wikidata.org/entity/' . $qid . '.json';
    $json = file_get_contents($url);
    $obj = json_decode($json);
    return $obj;
}

function get_claims($entity, $qid) {
    return $entity->entities->{$qid}->claims;
}

function get_string_claim($claim, $property_id) {
    $datavalue = $claim[0]->mainsnak->datavalue;
    if ($datavalue->type == 'wikibase-entityid') { 
        $qid = $datavalue->value->id;
        $entity = get_wikidata_entity($qid);
        return $entity->entities->{$qid}->labels->en->value;
    }

    return '';
}

/** Enhance action. */
function enhance_action()
{
    $post_id = $_POST['post_id'];
    $qid = get_field('wikidata_qid', $post_id);
    $entity = get_wikidata_entity($qid);
    $claims = get_claims($entity, $qid);
    var_dump(json_encode($claims));
    foreach ($claims as $property => $claim) {        
        update_field($property, get_string_claim($claim, $property), $post_id);
    }
    wp_die();
}


add_action('add_meta_boxes', 'add_enhance_boxes');
add_action('wp_ajax_enhance_action', 'enhance_action');
add_action('admin_enqueue_scripts', 'enqueue_admin_script');
