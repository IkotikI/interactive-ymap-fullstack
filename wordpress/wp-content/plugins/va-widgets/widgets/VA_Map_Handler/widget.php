<?php

define( "WIDGET_VA_QUIZ", __DIR__ );

class VA_Map_Handler
{
    /**
     *  Path to chached file.
     */
    const JSON_DATA_PATH = __DIR__ . '/data/data.json';

    /**
     * @var string Post type of map objects
     */
    public string $post_type;

    /**
     * @var string Redring can be 'client' or 'server'.
     * ! Now supports only 'client' !
     */
    public string $rendering_type;

    /**
     * @var array Options of rendering. Specifit for $rendering_type
     */
    public array $options;

    /**
     * @var string Yandex Map API key.
     * @see https://yandex.ru/dev/jsapi-v2-1/doc/ru/v2-1/dg/concepts/load
     * @see https://developer.tech.yandex.ru/
     */
    public string $yandex_api_key;

    /**
     * @var string Language for Yandex Map API.
     */
    public string $lang = 'ru_RU';

    /**
     * @var array Options of how map point would be displayed.
     */
    public array $point_options;

    /**
     * @var string Wordpress image size for rendering images.
     */
    public string $image_size = 'full';

    /**
     * @var array Styles for <div style="<...>" id="map"></div>.
     * Array will be printed in CSS format.
     */
    public array $styles = array();

    public array|false $city_options = false;

    public string|false $city_taxonomy = false;

    public WP_Term_Query|false $cities = false;

    /**
     * Optional in constructor. Required for usage.
     * Can be specified later by @see 'parse_args($args)'
     *
     *
     *
     *
     * }
     * @param array|null $args {
     */
    public function __construct( array | null $args = null )
    {
        if ( !file_exists( $this::JSON_DATA_PATH ) ) {
            touch( 'data.json' );
        }

        if ( $args !== null ) {
            $this->parse_args( $args );
        }

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 99 );
        add_action( "save_post_{$this->post_type}", [ $this, 'update_chached_data' ], 99, 0 );
        add_action( "edited_{$this->city_taxonomy}", [ $this, 'update_chached_data' ], 99, 0 );
        //@see https://www.keycdn.com/support/wordpress-cache-enabler-plugin#hooks
        // add_action( "cache_enabler_clear_site_cache", [ $this, 'update_chached_data' ], 99, 0 );
        // add_action( "cache_enabler_clear_complete_cache", [ $this, 'registered_city_taxonomy_update_chached_data' ], 99, 0 );
        add_action( "init", [ $this, 'cache_enabler_process_clear_cache_request_update_chached_data' ], 8, 0 );
    }
    /**
     * Class settings.
     *
     * 1. Client rendering
     * - Post meta fields
     * - Where it will be rendered
     * - How it will be rendered?
     * 2. Server rendering
     * - Just ajax target
     * - and html selector to insert
     */

    /**
     *
     * Pasring args from array.
     *
     * Example 1.
     *     'post_type' => (string) Post type, where map points stored. Required.
     *     'rendering' => array(    Required. Parameters of rendering.
     *         'type'   => (string) Can be 'client' or 'server'. Define, where data will be rendered. Required.
     *         'fields' => array(   Rendering Fields.
     *             $fieldName  => array(    Field settings
     *                 'name'     => (string) Alias for backend meta name.
     *                 'selector' => (string) Selector, where content will be put.
     *                 'template' => (string) HTML template. Use '{{@}}' as placeholder for value.
     *             ),
     *            ($prefix:)$fieldName => array(<...>)
     *               $prefix:
     *                - "wp" - wordprees value
     *                - "" (default) - meta value
     *         <...>
     *         ),
     *     ),
     * );
     *
     * Exapmle 2.
     * $args = array(
     *     'post_type' => 'branch', // required!
     *     'rendering' => array(
     *         'type' => 'server', required!
     *         'ajax_url' => admin_url( 'admin-ajax.php' ),
     *         'action'   => 'get_branch',
     *         'selector' => '#branch',
     *     ),
     * );
     *
     *
     * @param array $args
     */
    public function parse_args( $args )
    {

        if ( !isset( $args[ 'post_type' ] ) ) {
            trigger_error( '["post_type"] must be specified in $args array for ' . get_class( $this ) );
        }

        if ( !isset( $args[ 'rendering' ][ 'type' ] ) ) {
            trigger_error( '["rendering"]["type"] must be specified in $args array for new ' . get_class( $this ) );
        }

        if ( !isset( $args[ 'yandex-api-key' ] ) ) {
            trigger_error( '["yandex-api-key"] must be specified for new' . get_class( $this ) );
        }

        $this->yandex_api_key = $args[ 'yandex-api-key' ];
        $this->post_type = $args[ 'post_type' ];
        $this->rendering_type = $args[ 'rendering' ][ 'type' ];
        $this->point_options = $args[ 'point-options' ];
        $this->styles = $args[ 'styles' ] ?? [ 'height' => '500px', 'width' => '100%' ];

        $this->options = $args[ 'rendering' ];

        $this->city_taxonomy = $args[ 'city' ][ 'taxonomy' ] ?? false;
        if ( $this->city_taxonomy ) {
            $this->city_options = $args[ 'city' ][ 'fields' ] ?? [  ];
            $this->cities = new WP_Term_Query( array(
                'taxonomy'   => $this->city_taxonomy,
                'hide_empty' => false,
                'order'      => 'ASC',
                'orderby'    => 'name',
            ) );
        }

    }

    public function enqueue_scripts()
    {

        wp_register_script( 'yandex-map', sprintf( "https://api-maps.yandex.ru/2.1/?apikey={$this->yandex_api_key}&lang={$this->lang}&type=text/javascript" ), false, 1, [ 'in_footer' => false ] );
        wp_register_script( 'map-handler', plugin_dir_url( __FILE__ ) . "js/map.js", [ 'jquery', 'yandex-map' ], 2, false );
        // wp_register_script( 'city-select', plugin_dir_url( __FILE__ ) . "js/city_select.js", [ 'jquery' ], 2, false );

        wp_localize_script(
            'map-handler',
            'map_handler_data',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'data_url' => plugin_dir_url( __FILE__ ) . "data/data.json",
            )
        );
        wp_enqueue_script( 'yandex-map' );

    }

    public function the_map()
    {
        echo $this->get_map();
    }

    /**
     * Get the map, where it needed
     *
     * @return string Map HTML
     */
    public function get_map()
    {

        wp_enqueue_script( 'map-handler' );

        return /*'<div>Update chache data: ' . $this->update_chached_data() ?: 'false' . '</div>'.*/
        sprintf( '<div style="%s" id="map"></div>', $this->format_styles() );
    }

    public function get_city_select()
    {
        if ( $this->cities && $this->cities->terms ) {
            wp_enqueue_script( 'map-handler' );
            ob_start();
            include __DIR__ . '/inc/city_select.php';
            $city_select = ob_get_clean();
            return $city_select;
        } else {
            return "[ERR] Cities is empty";
        }
    }

    /**
     * Updates chached file
     */
    public function update_chached_data(): int | false
    {

        $data = $this->get_map_branches_object();

        return file_put_contents( $this::JSON_DATA_PATH, json_encode( $data ) );

    }

    public function cache_enabler_process_clear_cache_request_update_chached_data()
    {
        if ( empty( $_GET[ '_cache' ] ) || empty( $_GET[ '_action' ] ) || $_GET[ '_cache' ] !== 'cache-enabler' || ( $_GET[ '_action' ] !== 'clear' && $_GET[ '_action' ] !== 'clearurl' ) ) {
            return;
        }

        if ( empty( $_GET[ '_wpnonce' ] ) || !wp_verify_nonce( $_GET[ '_wpnonce' ], 'cache_enabler_clear_cache_nonce' ) ) {
            return;
        }

        $temp_taxonomy = false;
        if ( !taxonomy_exists( $this->city_taxonomy ) ) {
            register_taxonomy( $this->city_taxonomy, $this->post_type );
            $temp_taxonomy = true;
        }

        // throw new ErrorException( "Hook has must fired after nonce" );

        $this->update_chached_data();

        if ( $temp_taxonomy ) unregister_taxonomy( $this->city_taxonomy );
    }

    /**
     * If $this->city_taxonomy is not registered, postpone excution of
     * update_chached_data() to the moment of taxonomy registration.
     *
     * @return int
     */
    public function registered_city_taxonomy_update_chached_data(): int | bool
    {
        // throw new ErrorException( "Taxnomy {$this->city_taxonomy} exists: " . ( taxonomy_exists( $this->city_taxonomy ) ? 'True' : 'False' ) );
        // $this->update_chached_data();
        if ( !taxonomy_exists( $this->city_taxonomy ) ) {
            // add_action( "registered_taxonomy_{$this->city_taxonomy}", [ $this, 'update_chached_data' ], 99, 0 );
            add_action( "registered_taxonomy_{$this->city_taxonomy}", [ $this, 'update_chached_data' ], 99, 0 );
            return true;
        } else {
            return $this->update_chached_data();
        }
    }

    /**
     * Making complete client map rendering object. Using lot of class fields.
     *
     * Returns:
     * Array (
     *   [ 'info' ] - Information, what will be insert into HTML.
     *   [ 'points' ] - Object with array of Yandex Map points.
     *   [ 'rendering' ] - Parameters, where and how [ 'info' ] will be rendererd.
     *   [ 'citites' ] - Cities, where map shall locate.
     * )
     * @return array ↑↑↑
     */
    public function get_map_branches_object()
    {

        // Initialize output array
        $data = array();

        // Branches Custom post type
        $branches = new WP_Query(
            array(
                'post_type' => $this->post_type,
            )
        );
        // print_r($branches);

        // Get metafield of each branch
        $meta_associations = array();
        $_options = array();
        foreach ( $this->options[ 'fields' ] as $key => $value ) {
            if ( isset( $value[ 'name' ] ) ) {
                $name = $value[ 'name' ];
                $meta_associations[ $key ] = $name;
                $_options[ $name ] = $value;
                unset( $_options[ $name ][ 'name' ] );
            } else {
                $meta_associations[ $key ] = $key;
                $_options[ $key ] = $value;
            }
        }

        // Depricated?
        $_options[ '_onload' ] = $this->options[ 'onload' ] ?? false;

        // Client rendering options
        $data[ 'rendering' ] = $_options;

        // Array of Yandex Map API points
        // @see https://yandex.ru/dev/jsapi-v2-1/doc/ru/v2-1/dg/concepts/object-manager/frontend
        $data[ 'points' ] = array(
            "type"     => "FeatureCollection",
            "features" => [  ],
        );

        foreach ( $branches->posts as $key => $branch ) {
            $branch_info = array();
            $branch_info[ 'post_title' ] = $branch->post_title;

            // Adding data from CPT Meta.
            // Will be replaced in HTML by JS.
            $meta = apply_filters( 'va_map_handler_get_post_meta', get_post_meta( $branch->ID ), $branch->ID );

            $meta = $this->unpack_meta( $meta, $this->options[ 'fields' ] );
            // print_r( $meta );
            $branch_info = $this::redefine_associations( $meta, $meta_associations );

            $branch_city = wp_get_post_terms( $branch->ID, $this->city_taxonomy )[ 0 ] ?? false;
            if ( $branch_city ) {
                $branch_info[ '_city_id' ] = $branch_city->term_id;
            }

            // Adding Points to the Yandex Map API.
            // @see https://yandex.ru/dev/jsapi-v2-1/doc/ru/v2-1/dg/concepts/object-manager/frontend
            $branch_point = array(
                "id"         => $branch->ID,
                "type"       => "Feature",
                "geometry"   => array(
                    "type"        => "Point",
                    "coordinates" => $this->unpack_meta_value( $meta[ "branch_coordinates" ][ 0 ], 'coordinates' ),
                ),
                "properties" => array(
                    "balloonContent" => sprintf( "ID: %s, Adress: %s", $branch->ID, $meta[ "branch_address" ][ 0 ] ),
                    "clusterCaption" => "Кластер филиалов",
                    "hintContent"    => "Нажми меня",
                ),
            );

            if ( isset( $this->point_options ) ) {
                $branch_point[ "options" ] = $this->point_options;
            }

            // Making result array
            $data[ 'info' ][ $branch->ID ] = $branch_info;
            $data[ 'points' ][ "features" ][  ] = $branch_point;

        }

        if ( $this->cities ) {
            $term_associations = array(
                'term_id' => 'city_id',
                'name'    => 'name',
                'slug'    => 'slug',
            );

            $term_meta_associations = array();
            foreach ( $this->city_options as $key => $value ) {
                if ( isset( $value[ 'name' ] ) ) {
                    $term_meta_associations[ $key ] = $value[ 'name' ];
                } else {
                    $term_meta_associations[ $key ] = $key;
                }
            }

            // Array will inclued
            // term params (WP_Terms), defined by $term_associations, and
            // term meta, defined by, $term_meta_associations
            $data[ 'cities' ] = array();

            foreach ( $this->cities->terms as $term ) {
                $meta = get_term_meta( $term->term_id );
                // Extract sub values $meta[key] = $meta[key][0]
                $meta = array_map( function ( $m ) {return is_array( $m ) ? ( $m[ 0 ] ?? '' ) : $m;}, $meta );
                $meta = $this->unpack_meta( $meta, $this->city_options );
                $data[ 'cities' ][ $term->term_id ] = array_merge(
                    $this::redefine_associations( $term, $term_associations ),
                    $this::redefine_associations( $meta, $term_meta_associations )
                );
            }
        }

        return $data;

    }

    // private static function get_associations_by_field( array | object $data, string $field):array {
    //     return $data;
    // }

    /**
     * Replace keys of $object from 'key' to 'value' of $template array.
     *
     *
     * @param  array|object $object   Object or Array need to be redefined
     * @param  array        $template Template of pairs 'key' => 'value', by which be replaced keys of $object
     * @return array        Returned will array with elements, which keys are defined in both $object and $template.
     */
    private static function redefine_associations( array | object $data, array $template ): array
    {
        $result = array();
        // if (!is_array($data) || !is_array($template)) {
        //     return $result;
        // }
        // print_r($data);
        foreach ( $template as $from => $to ) {
            if ( is_array( $data ) ) {
                if ( isset( $data[ $from ] ) ) {
                    $result[ $to ] = $data[ $from ];
                    // $result[ $to ] = array_map( function ( $v ) {return VA_Map_Handler::unpack_pod_value( $v );}, $object[ $from ] );
                }
            } else if ( is_object( $data ) ) {
                if ( property_exists( $data, $from ) ) {
                    $result[ $to ] = $data->{$from};
                }
            }
        }

        return $result;
    }

    /**
     * Specific type of fields will be unwrapped
     *
     *  @see $this::unpack_meta_value( $value, $type )
     * @param  array|object Metafields
     * @return array        Metafields
     */
    private function unpack_meta( array $object, array $fields )
    {
        $result = array();

        foreach ( $object as $key => $meta ) {
            // print_r( $this->options );
            if ( isset( $fields[ $key ][ 'type' ] ) ) {
                $type = $fields[ $key ][ 'type' ];
                if ( is_array( $meta ) ) {
                    foreach ( $meta as $mkey => $value ) {
                        if ( empty( $value ) ) unset( $meta[ $mkey ] );
                        $meta[ $mkey ] = $this->unpack_meta_value( $value, $type );
                    }
                } else {
                    $meta = $this->unpack_meta_value( $meta, $type );
                }
            }
            $result[ $key ] = $meta;

        }

        return $result;
    }

    /**
     * Execute specific operation by given type.
     *
     * @param  mixed  $value Start value.
     * @param  string $type  Type of value, whick determine action.
     * @return mixed  Meta value after transformation
     */
    private function unpack_meta_value( $value, $type )
    {
        if ( isset( $type ) ) {
            switch ( $type ) {
                case 'photo':
                    return wp_get_attachment_image_url( $value, $this->image_size );
                    break;
                case 'coordinates':
                    return array_map( "floatval", explode( ",", $value ) );
                    break;
                default:
                    return $value;
                    break;
            }
        }

        return $value;

    }

    private function format_styles(): string
    {
        $output = '';
        foreach ( $this->styles as $key => $value ) {
            $output .= "{$key}: {$value}; ";
        }
        return $output;
    }

}

require_once __DIR__ . '/inc/class_VA_Map_Settings.php';

$args = array(
    'parent_slug' => 'edit.php?post_type=branch',
);

new VA_Map_Settings( $args );
