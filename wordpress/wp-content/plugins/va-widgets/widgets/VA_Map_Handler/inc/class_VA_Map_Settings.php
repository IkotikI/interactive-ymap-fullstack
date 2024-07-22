<?php
class VA_Map_Settings
{

    public string $parent_slug;

    public string $page_name = 'va_map_settings';

    public function __construct( $args )
    {
        $this->parent_slug = $args[ 'parent_slug' ] ?? 'tools.php';

        add_action( 'admin_menu', [ $this, 'register_submenu' ] );
        add_action( 'admin_menu', [ $this, 'va_map_settings_fields' ] );

		add_action( 'wp_ajax_drop-map-chache', [ $this, 'ajax_drop_chache'] );
        add_action( 'wp_ajax_nopriv_drop-map-chache', [ $this, 'ajax_drop_chache'] );
    }

    public function register_submenu()
    {

        add_submenu_page(
            $this->parent_slug, // parent page slug
            __( 'Map settings' ),
            __( 'Settings' ),
            'manage_options',
            'va_map_settings',
            [ $this, 'va_map_settings_page_callback' ],
            100// menu position
        );
    }

    public function va_map_settings_page_callback()
    {
        ?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title() ?></h1>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'va_map_settings' ); // settings group name
					do_settings_sections( 'va_map' ); // just a page slug
					submit_button(); // "Save Changes" button
				?>
			</form>
		</div>
	<?php
}

    public function va_map_settings_fields()
    {

        // I created variables to make the things clearer
        $page_slug = 'va_map';
        $option_group = 'va_map_settings';

        // 1. create section
        add_settings_section(
            'va_map_section_common', // section ID
            '', // title (optional)
            '', // callback function to display the section (optional)
            $page_slug
        );

        // 2. register fields
        // register_setting( $option_group, 'yandex_map_api_key', 'sanitize_text_field' );
        // register_setting( $option_group, 'map_field_setting', 'sanitize_text_field' );

        // 3. add fields
        // add_settings_field(
        //     'yandex_api_key',
        //     'Yandex Map API Key',
        //     [ $this, 'va_map_input_text' ], // function to print the field
        //     $page_slug,
        //     'va_map_section_common', // section ID
        //     array(
        //         'label_for' => 'yandex_map_api_key',
        //         'class'     => 'yandex-map-api-key', // for <tr> element
        //         'name' => 'yandex-map-api-key', // pass any custom parameters
        //         'option_name' => 'yandex_map_api_key',
        //     )
        // );

        // add_settings_field(
        //     'map_field_setting',
        //     'Settings for Map fields',
        //     [ $this, 'va_map_repeater_field_settings' ],
        //     $page_slug,
        //     'va_map_section_common',
        //     array(
        //         'label_for' => 'map_field_setting',
        //         'class'     => 'map-field-setting', // for <tr> element
        //         'name' => 'map-field-setting', // pass any custom parameters
        //         'option_name' => 'map_field_setting',
        //     )
        // );

        add_settings_field(
            '',
            'Drop chache',
            [ $this, 'va_drop_map_chache_button' ],
            $page_slug,
            'va_map_section_common',
            array(
                'label_for' => 'drop-map-chache',
                'class'     => 'drop-map-chache', // for <tr> element
                'name' => 'drop-map-chache', // pass any custom parameters
            )
        );

    }

    public function va_map_input_text( $args )
    {
        printf(
            '<input type="number" id="%s" name="%s" value="%d" />',
            $args[ 'name' ],
            $args[ 'name' ],
            get_option( $args[ 'option_name' ], 2 ) // 2 is the default number of slides
        );
    }

    public function va_map_repeater_field_settings( $args )
    {
        $option = get_option( $args[ 'option_name' ], array() );

    }

    public function va_drop_map_chache_button( $args )
    {
		?> 
		
		<span id="<?= $args['name'] ?>" class="button button-primary <?= $args['name'] ?>">Drop Chache</span>
		<span class="response"></span>
		<script>
		(function($) {
			$('#<?= $args['name'] ?>').click(function(e) {
				let btn = $(this)
				e.preventDefault();
				$.ajax({
					method: 'POST',
					url: '<?= admin_url('admin-ajax.php') ?>',
					data: {
						action: 'drop-map-chache',
						map_handler: 'footer'
					},
					success: (response) => {
						console.log(response)
						console.log(btn, btn.siblings('.response') )
						btn.siblings('.response').html(response)
					}

				})
			});
		})( jQuery );
		</script>
		<?

    }

	public function ajax_drop_chache () {
		
		$map_handler_key = $_POST['map_handler'] ?? false;
		if ( !$map_handler_key ) {
			wp_send_json_error('Handler is not specified. ' . print_r($_POST, true));
		}
		$va_widgets =& $GLOBALS[VA_Widgets::VA_WIDGETS];
		
		// wp_send_json_error('There is not handler with such key.'  . ($va_widgets) ? ' See global ' . VA_Widgets::VA_WIDGETS  . print_r($va_widgets, true)   : ' VA_Map_Handler is not isset') );
		if ( !isset($va_widgets['VA_Map_Handler']) || !isset($va_widgets['VA_Map_Handler'][ $map_handler_key ]  ) ) {
			wp_send_json_error('There is not handler with such key.'  . (isset($GLOBALS[VA_Widgets::VA_WIDGETS]) ? ' See global ' . VA_Widgets::VA_WIDGETS  . print_r($va_widgets, true)   : ' VA_Map_Handler is not isset') );
		}

		$handler = $va_widgets['VA_Map_Handler'][$map_handler_key];
		
		// wp_send_json_error('Handler ' . print_r($handler, true));
		try {
		    $update = $handler->update_chached_data();
		} catch (Exception $e) {
			wp_send_json_error('[ERR]: ' . $e->getMessage());
		}
		// wp_send_json_error('Error whereabout next.');

		wp_send_json($update ? "{$update} byte written" : 'Cant save new chache to the file.');
		
    }

}