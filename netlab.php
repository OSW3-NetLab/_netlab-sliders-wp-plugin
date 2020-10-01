<?php 
//======================================================================
// NetLab Sliders Plugin
//======================================================================

/**
 * Plugin Name:       NetLab Sliders Plugin
 * Plugin URI:        https://osw3.net/netlab/plugins/
 * Description:       Create a simple sliders.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            OSW3
 * Author URI:        https://osw3.net/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       netlab-slider-plugin
 * Domain Path:       /languages
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) 
{
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'NETLAB_SLIDER__PLUGIN_FILE', __FILE__ );
define( 'NETLAB_SLIDER__PLUGIN_DIR', plugin_dir_path( NETLAB_SLIDER__PLUGIN_FILE ) );
define( 'NETLAB_SLIDER__PLUGIN_URL', plugin_dir_url( NETLAB_SLIDER__PLUGIN_FILE ) );

register_activation_hook( NETLAB_SLIDER__PLUGIN_FILE, array( 'NetlabSlider', 'plugin_activation' ) );
register_deactivation_hook( NETLAB_SLIDER__PLUGIN_FILE, array( 'NetlabSlider', 'plugin_deactivation' ) );

require_once( NETLAB_SLIDER__PLUGIN_DIR . 'config.php' );
require_once( NETLAB_SLIDER__PLUGIN_DIR . 'class.NetlabSlider.php' );

add_action( 'init', array( 'NetlabSlider', 'plugin_init' ) );