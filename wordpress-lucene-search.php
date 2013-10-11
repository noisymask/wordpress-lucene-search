<?php
/*
Plugin Name: Wordpress Lucene Search
Plugin URI:
Description: Simple index & search of wordpress posts using Zend Lucene library
Version: 0.1
Author: Thom Kelly
Author URI: https://github.com/noisymask/wordpress-lucene-search
Uses: Zend_Lucene_Search (http://framework.zend.com/manual/1.12/en/zend.search.lucene.html)
*/

define('LUCENE_SEARCH_VERSION', '0.1');
define('LUCENE_SEARCH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LUCENE_SEARCH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LUCENE_SEARCH_INDEX_PATH', LUCENE_SEARCH_PLUGIN_PATH . 'data/docindex');
define('LUCENE_SEARCH_SETTINGS_KEY', 'lucene_search_options');
define('LUCENE_SEARCH_CHARSET', 'UTF-8');
define('LUCENE_CACHE_TTL', 3600); // seconds

/**
 *
 */
function wpls_search_init()
{
    new LuceneSearch_Search();
}


/**
 *
 */
function wpls_search_init_activation()
{
    if (!wp_next_scheduled('lucene_search_daily')) {
        wp_schedule_event(strtotime('midnight'), 'daily', 'lucene_search_daily');
    }
}


/**
 *
 */
function wpls_search_init_deactivation()
{
    wp_clear_scheduled_hook('lucene_search_daily');
}

// Add initialization and activation hooks
add_action('init', 'wpls_search_init');
register_activation_hook(LUCENE_SEARCH_PLUGIN_PATH . '/' . __FILE__, 'wpls_search_init_activation');
register_deactivation_hook(LUCENE_SEARCH_PLUGIN_PATH . '/' . __FILE__, 'wpls_search_init_deactivation');

set_include_path(
    LUCENE_SEARCH_PLUGIN_PATH . '/src/'
    . PATH_SEPARATOR . LUCENE_SEARCH_PLUGIN_PATH . '/library/'
    . PATH_SEPARATOR . get_include_path()
);

/**
 * @param $class
 */
function wpls_search_autoload($class)
{
    $file = str_replace('_', '/', $class) . '.php';
    $include_paths = explode(PATH_SEPARATOR, get_include_path());
    foreach ($include_paths as $path) {
        if (@file_exists($path . $file)) {
            @include_once($file);
            break;
        }
    }
}

spl_autoload_register('wpls_search_autoload');


/**
 * Retrieve plugin options
 * @return array
 */
function wpls_settings()
{
    $return = array();
    $options = get_option(LUCENE_SEARCH_SETTINGS_KEY);
    foreach (wpls_get_settings_array() as $key) {
        $return[$key] = $options[$key];
    }
    return $return;
}


/**
 * New plugin options must be added here first
 * @return array
 */
function wpls_get_settings_array()
{
    $options = array(
        'post_types',
        'batch_size',
        'relevance'
    );
    return (array)apply_filters('lucene_search_options', $options);
}


if (!function_exists('wpls_log')) {
    /**
     * @param $message
     */
    function wpls_log($message)
    {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}


if (!function_exists('wpls_debug')) {
    /**
     * @param $var
     */
    function wpls_debug($var)
    {
        ob_start();
        var_dump($var);
        $out = ob_get_clean();
        echo sprintf('<pre><code>%s</code></pre>', $out);
    }
}