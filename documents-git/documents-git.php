<?php
/**
 * Plugin Name: Documents from Git
 * Plugin URI: https://github.com/gis-ops/wordpress-markdown-git
 * Description: Render and cache various document formats in any post/page directly from a remote Git repository of your favorite platform via shortcodes. Currently supported: Markdown, Jupyter Notebooks.
 * Version:     1.1.0
 * Author:      GIS-OPS UG
 * Author URI:  https://gis-ops.com
 * Text Domain: documents-git
 * License: GPLv3
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

include('includes/providers/class-base-loader.php');

# Add any additional providers here
$providers = ['github', 'bitbucket', 'gitlab'];

define('MARKDOWNGIT_PLUGIN_PATH', dirname( __FILE__ ));
define("MARKDOWNGIT_CONFIG", json_decode(file_get_contents(MARKDOWNGIT_PLUGIN_PATH . '/includes/config.json'), True));

# Add shortcodes for registered providers automatically from $providers
foreach($providers as $provider) {
    require_once MARKDOWNGIT_PLUGIN_PATH . '/includes/providers/class-' . $provider . '-loader.php';
    $class = ucfirst($provider) . 'Loader';
    $instance = new $class();
}

# Add git-classes shortcode to be used as enclosing
add_shortcode('git-add-css', 'add_enclosing_classes');
function add_enclosing_classes($sc_attrs, $content) {
    $sc_attrs = array_change_key_case((array)$sc_attrs, CASE_LOWER);
    extract(shortcode_atts([
        'classes' => '',
    ], $sc_attrs));

    $classes = ($classes === '') ? (MARKDOWNGIT_CONFIG["classes"]) : ($classes);

    $new_content = '';
    $new_content .= '<div id="git-add-css" class="' . $classes . '">';
    $new_content .= do_shortcode($content);
    $new_content .= '</div>';

    return $new_content;
}

# Enqueue Github, nbconvert and plugin stylesheet
add_action('wp_enqueue_style', wp_enqueue_style( 'markdown_git', plugins_url( 'css/markdown-git.css', __FILE__ )));
add_action('wp_enqueue_style', wp_enqueue_style( 'github_markdown', plugins_url( 'css/github-markdown.css', __FILE__ ), 'markdown-git'));
add_action('wp_enqueue_style', wp_enqueue_style( 'nbconvert_git', plugins_url( 'css/nbconvert.css', __FILE__ ), 'markdown-git'));
