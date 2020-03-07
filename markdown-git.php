<?php
/**
 * Plugin Name: Markdown Git
 * Description: Add rendered Markdown files to any of your posts/pages directly from a remote Git repository of your favorite platform via shortcodes.
 * Version:     0.1
 * Author:      Nils Nolde
 * Author URI:  https://github.com/nilsnolde
 * Text Domain: markdown-git
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

# Add shortcodes for registered providers automatically from $providers
foreach($providers as $provider) {
    require_once MARKDOWNGIT_PLUGIN_PATH . '/includes/providers/class-' . $provider . '-loader.php';
    $class = ucfirst($provider) . 'Loader';
    $instance = new $class();
}

# Enqueue Github and plugin stylesheet
add_action('wp_enqueue_style', wp_enqueue_style( 'github_markdown', plugins_url( 'css/github-markdown.css', __FILE__ )));
add_action('wp_enqueue_style', wp_enqueue_style( 'markdown_git', plugins_url( 'css/markdown-git.css', __FILE__ )));

# Add git-classes shortcode to be used as enclosing
add_shortcode('git-add-css', 'add_enclosing_classes');
function add_enclosing_classes($sc_attrs, $content) {
    $sc_attrs = array_change_key_case((array)$sc_attrs, CASE_LOWER);
    $sc_attrs_parsed = shortcode_atts([
        'classes' => '',
    ], $sc_attrs);

    $new_content = '';
    $new_content .= '<div id="git-add-css" class="' . $sc_attrs_parsed['classes'] . '">';
    $new_content .= do_shortcode($content);
    $new_content .= '</div>';

    return $new_content;
}
