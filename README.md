# Fullstack WordPress-oriented Yandex Map with dynamic points

Project contain 
- Backend, where get information from (WordPress') Custom Post Type and it's Metafields, processing and cached save to `data.json`. Cache dropped by settings page, or based on WordPress Hooks.
-  Frontend is Yandex Map JS API and jQuery handling for initialization, YMap balloon click event's to change branch presentation, city select.
  
## Configuration

See `config.php` in plugin's folder.

Post type for Branches and Metafields for it can be created in any separate way. It must be configured in `config.php`. Configuration parameters see in `widgets/VA_Map_Handler/widget.php` as documentation of `function parse_args( $args )`

## Usage

- Copy plugin directory into your `wordpress/wp-content/plugins`.
- Add Custom Post Type in WordPress (for example you can use Pods or ACF plugin), which will represent points in the map.
- Add required Metafields to the Custom Post Type
- Configure `config.php` in the plugin directory, where each 
```php
$array_args[ key ]
```
represent each registering handler.
Or you can regiset it in `functions.php`
```php
$GLOBALS[ $this::VA_WIDGETS ][ 'VA_Map_Handler' ][ key ] = new VA_Map_Handler( $args );
```
- User Shortcodes `[VA_Map_Handler name="key"]` and `[VA_Map_City_Selector name="key"]` to display Map and City Selector respectively. If `name` is not specified, will be taken displayed the first element of `$GLOBALS[ $this::VA_WIDGETS ][ 'VA_Map_Handler' ]`

## Lore
Actual commercial order, but the site didn't existing long :/

