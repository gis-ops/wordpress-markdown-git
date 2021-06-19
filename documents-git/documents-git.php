<?php
/**
 * Plugin Name: Documents from Git
 * Plugin URI: https://github.com/gis-ops/wordpress-markdown-git
 * Description: Render and cache various document formats in any post or page directly from a remote Git repository of your favorite platform via shortcodes. Currently supported: Markdown, Jupyter Notebooks.
 * Version:     2.1.0
 * Author:      GIS-OPS UG
 * Author URI:  https://gis-ops.com
 * Text Domain: documents-git
 * License: GPLv3
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require('includes/providers/class-base-loader.php');

# Add any additional providers here
$providers = ['github', 'bitbucket', 'gitlab'];

define('MARKDOWNGIT_PLUGIN_PATH', dirname( __FILE__ ));

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

    $classes = ($classes === '') ? (get_option("git_general", array())['classes']) : ($classes);

    $new_content = '';
    $new_content .= '<div id="git-add-css" class="' . $classes . '">';
    $new_content .= do_shortcode($content);
    $new_content .= '</div>';

    return $new_content;
}

# Enqueue Github, nbconvert and plugin stylesheet
add_action('wp_enqueue_scripts', 'add_styles');
function add_styles() {
    wp_enqueue_style( 'markdown_git', plugins_url( 'css/markdown-git.css', __FILE__ ));
    wp_enqueue_style( 'github_markdown', plugins_url( 'css/github-markdown.css', __FILE__ ));
    wp_enqueue_style( 'nbconvert_git', plugins_url( 'css/nbconvert.css', __FILE__ ), 'markdown-git');
}

# Add and set up the Page Builder class for the settings UI
require_once(MARKDOWNGIT_PLUGIN_PATH . '/includes/RationalOptionPages.php');
$pages = array(
    'settings_markdowngit' => array(
        'parent_slug' => 'options-general.php',
        'page_title' => __( 'Settings - Documents from Git', 'documents-from-git' ),
        'menu_title' => __( 'Documents from Git', 'documents-from-git' ),
        'sections' => array(
            'git_general' => array(
                'title' => __( 'General Settings', 'documents-from-git' ),
                'text' => __( 'Find the official documentation on <a href="https://github.com/gis-ops/wordpress-markdown-git" target="_blank">Github</a>. Contact us on <a href="mailto:enquiry@gis-ops.com">enquiry@gis-ops.com</a>.', 'documents-from-git' ),
                'fields' => array(
                    'limit' => array(
                        'title' => __( 'History Limit', 'documents-from-git' ),
                        'id' => 'limit',
                        'type' => 'number',
                        'text' => __( 'Set the number of commit messages shown with the <code>git-xxx-history</code> shortcode(s).', 'documents-from-git' ),
                        'value' => 5,
                        'attributes' => array(
                            'required' => true
                        )
                    ),
                    'classes' => array(
                        'title' => __( 'CSS classes', 'documents-from-git' ),
                        'id' => 'classes',
                        'text' => __( 'Set (optional) CSS class names which can be wrapped with the <code>git-add-css</code> shortcode, see the <a href="https://github.com/gis-ops/wordpress-markdown-git#use-additional-css-classes-to-style" target="_blank">documentation</a> for usage examples.', 'documents-from-git' ),
                        'placeholder' => 'my-css-class'
                    ),
                    'cache_ttl' => array(
                        'title' => __( 'Cache TTL', 'documents-from-git' ),
                        'id' => 'cache_ttl',
                        'type' => 'number',
                        'text' => __( 'The Time To Live (TTL) for cached documents, <b>in seconds</b>. Defaults to 1 week. To manually flush the case, see the <a href="https://github.com/gis-ops/wordpress-markdown-git#static-caching-cache_strategystatic" target="_blank">documentation</a>.', 'documents-from-git' ),
                        'value' => 604800,
                        'attributes' => array(
                            'required' => true
                        )
                    ),
                )
            ),
            'git_github' => array(
                'title' => __( 'Github', 'documents-from-git' ),
                'text' => __( 'Regardless if you host any documents on Github, it\'s wise to provide the credentials. The plugin will render Markdown documents via Github\'s <code>/markdown</code> endpoint.', 'documents-from-git' ),
                'fields' => array(
                    'user' => array(
                        'title' => __( 'Github User', 'documents-from-git' ),
                        'id' => 'github_user',
                        'text' => __( 'Your Github <b>user name</b>', 'documents-from-git' )
                    ),
                    'secret' => array(
                        'title' => __( 'Github Token', 'documents-from-git' ),
                        'id' => 'github_secret',
                        'type' => 'password',
                        'text' => __( 'The Github <a href="https://docs.github.com/en/github/authenticating-to-github/keeping-your-account-and-data-secure/creating-a-personal-access-token" target="_blank">access token</a>. Needs only <code>repo</code> access.', 'documents-from-git' ),
                    )
                )
            ),
            'git_gitlab' => array(
                'title' => __( 'Gitlab', 'documents-from-git' ),
                'fields' => array(
                    'user' => array(
                        'title' => __( 'Gitlab User', 'documents-from-git' ),
                        'id' => 'gitlab_user',
                        'text' => __( 'Your Gitlab <b>user name</b>', 'documents-from-git' )
                    ),
                    'secret' => array(
                        'title' => __( 'Gitlab Token', 'documents-from-git' ),
                        'id' => 'gitlab_secret',
                        'type' => 'password',
                        'text' => __( 'The Gitlab <a href="https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html#creating-a-personal-access-token" target="_blank">access token</a>. Needs only <code>repo</code> access.', 'documents-from-git' ),
                    )
                )
            ),
            'git_bitbucket' => array(
                'title' => __( 'BitBucket', 'documents-from-git' ),
                'fields' => array(
                    'user' => array(
                        'title' => __( 'BitBucket User', 'documents-from-git' ),
                        'id' => 'bitbucket_user',
                        'text' => __( 'Your BitBucket <b>user name</b>', 'documents-from-git' )
                    ),
                    'secret' => array(
                        'title' => __( 'BitBucket Token', 'documents-from-git' ),
                        'id' => 'bitbucket_secret',
                        'type' => 'password',
                        'text' => __( 'The BitBucket <a href="https://support.atlassian.com/bitbucket-cloud/docs/app-passwords/#Apppasswords-Createanapppassword" target="_blank">access token</a>. Needs only <code>repo</code> access.', 'documents-from-git' ),
                    )
                )
            )
        )
    )
);
$option_page = new RationalOptionPages( $pages );
