<?php

/**
 * For $args description
 * @see widgets/VA_Map_Handler/widget.php
 */

$array_args[ 'footer' ] = array(
    'post_type'     => 'branch', // required!
    'yandex-api-key' => '7bd88ed2-8134-4aee-9936-e94ec80efeee',
    'point-options' => array(
        // @see https://yandex.ru/dev/jsapi-v2-1/doc/ru/v2-1/ref/reference/GeoObject
        // @see https://ru.stackoverflow.com/questions/443643/Свой-маркер-картинка-Яндекс-Карты
        'iconLayout'      => 'default#image',
        'iconImageHref'   => plugin_dir_url( __FILE__ ) . 'widgets/VA_Map_Handler/img/map-label-red.png',
        // 'iconImageSize'   => [ 50, 62 ],
        'iconImageOffset' => [ 0, 0 ],
    ),
    'styles'        => array(
        'height' => '500px',
        'width'  => '100%',
    ),
    'city'          => array(
        'taxonomy' => 'branch_city',
        'fields'   => array(
            // array: meta_name => args
            'city_coordinates'    => array(
                'name' => 'coordinates', // required for city
                'type' => 'coordinates',
            ),
            'city_default_branch' => array(
                'name' => 'render_id',
            ),
            'city_zoom'           => array(
                'name' => 'zoom',
            ),
        ),
    ),
    'rendering'     => array(
        'type'   => 'client', // required!
        'onload' => true,
        // default - metafields;
        // prefix "wp" - patial wordpress fields of the current post, avaliable: post_title, post_content, post_excerpt, post_thumbnail
        'cities' => array(
            'name'     => 'address',
            'selector' => '#branch_address',
        ),
        // array: meta_name => args
        'fields' => array(
            // 'wp:post_title' => array(
            //     'name'     => 'title',
            //     'selector'  => '#branch_title',
            // ),
            'branch_address'  => array(
                'name'     => 'address',
                'selector' => '#branch_address',
            ),
            'branch_email'    => array(
                'name'     => 'email',
                'selector' => '#branch_email',
                'template' => '<a href="mailto:{{@}}">{{@}}</a>',
            ),
            'branch_phone'    => array(
                'name'     => 'phone',
                'selector' => '#branch_phone',
                'template' => '<li><a href="tel:{{@}}">{{@}}</a></li>',
            ),
            'branch_schedule' => array(
                'name'     => 'schedule',
                'selector' => '#branch_schedule',
            ),
            'branch_seller'   => array(
                'name'     => 'seller',
                'selector' => '#branch_seller',
            ),
            'branch_benefits' => array(
                'name'     => 'benefits',
                'selector' => '#branch_benefits',
            ),
            'branch_photo'    => array(
                'type'     => 'photo',
                'name'     => 'photo',
                'selector' => '#branch_photo_styles',
                'template' => '<style>#branch_photo { background-image: url({{@}}); }</style>',
            ),
        ),
    ),

);
