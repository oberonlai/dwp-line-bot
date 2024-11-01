<?php

/**
 * DWP LINE Bot
 *
 * @link              https://oberonlai.blog
 * @since             0.0.0
 * @package           dwp-line-bot
 *
 * @wordpress-plugin
 * Plugin Name:       DWP LINE Bot
 * Plugin URI:        https://dailywp.dev
 * Description:       Connect the Chatbase API and LINE offcial account with WordPress
 * Version:           1.0.0
 * Author:            Daily WPdev.
 * Author URI:        https://dailywp.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dwp-line-bot
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'DWPLINEBOT_VERSION', '1.0.0' );
define( 'DWPLINEBOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DWPLINEBOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DWPLINEBOT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'DWPLINEBOT_PLUGIN_FILE', __FILE__ );

require_once DWPLINEBOT_PLUGIN_DIR . 'vendor/autoload.php';
\A7\autoload( DWPLINEBOT_PLUGIN_DIR . 'src' );
