<?php
/*
 * Main assembling class of the plugin.
 * New units iincluding directly in "Includes units" section
 *
 */

/* ----- VA_Map_Handler ------ */
require_once 'widgets/VA_Map_Handler/widget.php';

class VA_Widgets
{

    const VA_WIDGETS = 'va_widgets';

    public $widgets = [  ];

    public function __construct()
    {

        // ---- Includes units section start ----
        require __DIR__ . '/config.php';

        foreach ( $array_args as $key => $args ) {
            $this->widgets[ 'VA_Map_Handler' ][ $key ] = new VA_Map_Handler( $args );
        }
        // ---- Includes units section end ----
        $GLOBALS[ $this::VA_WIDGETS ] = &$this->widgets;

        // Add shortcodes.
        add_action( 'init', function () {
            add_shortcode( 'VA_Map_Handler', function ( $atts ) {
                $name = $atts[ 'name' ] ?? array_key_first( $this->widgets[ 'VA_Map_Handler' ] );
                return isset( $this->widgets[ 'VA_Map_Handler' ][ $name ] )
                ? $this->widgets[ 'VA_Map_Handler' ][ $name ]->get_map()
                : '[ERR] No such map handler registered';
            } );

            add_shortcode( 'VA_Map_City_Selector', function ( $atts ) {
                $name = $atts[ 'name' ] ?? array_key_first( $this->widgets[ 'VA_Map_Handler' ] );
                return isset( $this->widgets[ 'VA_Map_Handler' ][ $name ] )
                ? $this->widgets[ 'VA_Map_Handler' ][ $name ]->get_city_select()
                : '[ERR] No such map handler registered for this City Selector';
            } );
        } );
    }

}

new VA_Widgets();
