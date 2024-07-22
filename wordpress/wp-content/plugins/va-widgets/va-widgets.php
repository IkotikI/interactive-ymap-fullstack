<?php
/**
 *
 *
 * Plugin Name: VA Widgets
 * Description: Custumable, half-developer plugin for making widgets for all purpoes.
 * Plugin URI:
 * Author: Vladislav Artyukhov
 * Version: 0.0.1
 * Author URI: https://vladislav-artyukhov.s-d-i.space/
 *
 * Text Domain: va-widgets
 * Domain Path: /languages
 * @package           VA Price Calculator
 *
 * @author            Vladislav Artyukhov
 * @copyright         Vladislav Artyukhov 2024
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'VA_PLUGIN_PASS', __DIR__ );

require VA_PLUGIN_PASS . '/assemble.php';
