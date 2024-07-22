<?php

class VA_Map_Ajax_Handler
{
    public function __construct()
    {

        // Load points
        add_action('wp_ajax_get_branches_map_points', [$this, 'get_branches_map_points']);
        add_action('wp_ajax_nopriv_get_branches_map_points', [$this, 'get_branches_map_points']);

    }

    function get_branches_map_points()
    {

        $branches = new WP_Query(
            array(
                'post_type' => 'branch'
            )
        );

    }


}