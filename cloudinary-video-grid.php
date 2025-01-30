<?php
/*
Plugin Name: Cloudinary Video Grid
Description: Masonry grid of Cloudinary videos with filter buttons
Version: 1.0
Author: Your Name
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
function cvg_add_admin_menu() {
    add_options_page(
        'Cloudinary Video Grid Settings',
        'Cloudinary Video Grid',
        'manage_options',
        'cloudinary-video-grid',
        'cvg_options_page'
    );
}
add_action('admin_menu', 'cvg_add_admin_menu');

// Register settings
function cvg_settings_init() {
    register_setting('cloudinaryVideoGrid', 'cvg_settings');

    add_settings_section(
        'cvg_cloudinary_settings',
        'Cloudinary API Settings',
        'cvg_settings_section_callback',
        'cloudinaryVideoGrid'
    );

    add_settings_field(
        'cloud_name',
        'Cloud Name',
        'cvg_text_field_render',
        'cloudinaryVideoGrid',
        'cvg_cloudinary_settings',
        array('field' => 'cloud_name')
    );

    add_settings_field(
        'api_key',
        'API Key',
        'cvg_text_field_render',
        'cloudinaryVideoGrid',
        'cvg_cloudinary_settings',
        array('field' => 'api_key')
    );

    add_settings_field(
        'api_secret',
        'API Secret',
        'cvg_text_field_render',
        'cloudinaryVideoGrid',
        'cvg_cloudinary_settings',
        array('field' => 'api_secret')
    );
}
add_action('admin_init', 'cvg_settings_init');

// Settings section callback
function cvg_settings_section_callback() {
    echo 'Enter your Cloudinary API credentials below:';
}

// Text field render
function cvg_text_field_render($args) {
    $options = get_option('cvg_settings');
    $field = $args['field'];
    $value = isset($options[$field]) ? $options[$field] : '';
    ?>
    <input type='text' name='cvg_settings[<?php echo $field; ?>]' value='<?php echo esc_attr($value); ?>'>
    <?php
}

// Options page
function cvg_options_page() {
    ?>
    <div class="wrap">
        <h2>Cloudinary Video Grid Settings</h2>
        <form action='options.php' method='post'>
            <?php
            settings_fields('cloudinaryVideoGrid');
            do_settings_sections('cloudinaryVideoGrid');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Enqueue necessary scripts and styles
function cvg_enqueue_scripts() {
    wp_enqueue_script('masonry');
    wp_enqueue_script('imagesloaded');
    wp_enqueue_style('cvg-styles', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('cvg-script', plugins_url('js/script.js', __FILE__), array('jquery', 'masonry', 'imagesloaded'), '1.0', true);

    // Pass PHP variables to JavaScript
    $options = get_option('cvg_settings');
    wp_localize_script('cvg-script', 'cvgSettings', array(
        'cloudName' => $options['cloud_name']
    ));
}
add_action('wp_enqueue_scripts', 'cvg_enqueue_scripts');

// Create shortcode for the grid
function cvg_grid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'folder' => '',
    ), $atts);

    // Get Cloudinary credentials
    $options = get_option('cvg_settings');
    $cloud_name = $options['cloud_name'];
    $api_key = $options['api_key'];
    $api_secret = $options['api_secret'];

    // Fetch videos from Cloudinary
    $videos = cvg_get_cloudinary_videos($cloud_name, $api_key, $api_secret, $atts['folder']);

    ob_start();
    ?>
    <div class="cvg-container">
        <!-- Filter Buttons -->
        <div class="cvg-filters">
            <!-- User Filters -->
            <div class="filter-group">
                <h4>Users</h4>
                <button class="filter-btn" data-filter="user1">User 1</button>
                <button class="filter-btn" data-filter="user2">User 2</button>
                <button class="filter-btn" data-filter="user3">User 3</button>
            </div>
            
            <!-- Technology Filters -->
            <div class="filter-group">
                <h4>Technologies</h4>
                <button class="filter-btn" data-filter="ar">AR</button>
                <button class="filter-btn" data-filter="vr">VR</button>
                <button class="filter-btn" data-filter="ai">AI</button>
                <button class="filter-btn" data-filter="js">JS</button>
                <button class="filter-btn" data-filter="cesium">Cesium</button>
            </div>
        </div>

        <!-- Video Grid -->
        <div class="cvg-grid">
            <?php foreach ($videos as $video): ?>
            <div class="grid-item" data-user="<?php echo esc_attr($video['user']); ?>" data-tech="<?php echo esc_attr($video['tech']); ?>">
                <video controls>
                    <source src="<?php echo esc_url($video['url']); ?>" type="video/mp4">
                </video>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('cloudinary_video_grid', 'cvg_grid_shortcode');

// Function to get videos from Cloudinary
function cvg_get_cloudinary_videos($cloud_name, $api_key, $api_secret, $folder) {
    // Build API URL
    $timestamp = time();
    $signature = sha1("folder={$folder}&timestamp={$timestamp}{$api_secret}");
    $url = "https://api.cloudinary.com/v1_1/{$cloud_name}/resources/video";
    $url .= "?prefix={$folder}&type=upload&timestamp={$timestamp}&signature={$signature}&api_key={$api_key}";

    // Make API request
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        return array();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!isset($data['resources'])) {
        return array();
    }

    // Process videos
    $videos = array();
    foreach ($data['resources'] as $resource) {
        // Extract user and tech from tags or context metadata
        $user = 'user1'; // Default value, modify based on your metadata structure
        $tech = 'ar';    // Default value, modify based on your metadata structure
        
        $videos[] = array(
            'url' => $resource['secure_url'],
            'user' => $user,
            'tech' => $tech
        );
    }

    return $videos;
}

// Add CSS file
function cvg_create_css() {
    $css_content = "
    .cvg-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .cvg-filters {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filter-btn {
        width: 50px;
        height: 50px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: #fff;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: #007bff;
        color: #fff;
    }

    .cvg-grid {
        width: 100%;
    }

    .grid-item {
        width: calc(33.333% - 20px);
        margin: 10px;
        background: #f8f8f8;
        border-radius: 5px;
        overflow: hidden;
    }

    .grid-item video {
        width: 100%;
        display: block;
    }
    ";

    $css_file = plugin_dir_path(__FILE__) . 'css/style.css';
    file_put_contents($css_file, $css_content);
}
register_activation_hook(__FILE__, 'cvg_create_css');

// Add JavaScript file
function cvg_create_js() {
    $js_content = "
    jQuery(document).ready(function($) {
        // Initialize Masonry
        var grid = $('.cvg-grid').masonry({
            itemSelector: '.grid-item',
            columnWidth: '.grid-item',
            percentPosition: true,
            gutter: 20
        });

        // Reload masonry layout after videos are loaded
        $('.grid-item video').on('loadeddata', function() {
            grid.masonry('layout');
        });

        // Filter functionality
        $('.filter-btn').on('click', function() {
            var filterValue = $(this).attr('data-filter');
            $(this).toggleClass('active');
            
            // Get all active filters
            var activeUserFilters = [];
            var activeTechFilters = [];
            
            $('.filter-btn.active').each(function() {
                var filter = $(this).attr('data-filter');
                if (['user1', 'user2', 'user3'].includes(filter)) {
                    activeUserFilters.push(filter);
                } else {
                    activeTechFilters.push(filter);
                }
            });

            // Filter grid items
            $('.grid-item').each(function() {
                var item = $(this);
                var user = item.attr('data-user');
                var tech = item.attr('data-tech');
                
                var userMatch = activeUserFilters.length === 0 || activeUserFilters.includes(user);
                var techMatch = activeTechFilters.length === 0 || activeTechFilters.includes(tech);
                
                if (userMatch && techMatch) {
                    item.show();
                } else {
                    item.hide();
                }
            });

            // Re-layout Masonry
            grid.masonry('layout');
        });
    });
    ";

    $js_file = plugin_dir_path(__FILE__) . 'js/script.js';
    file_put_contents($js_file, $js_content);
}
register_activation_hook(__FILE__, 'cvg_create_js');

// Create necessary directories on plugin activation
function cvg_activate() {
    // Create plugin directories
    $dirs = array(
        plugin_dir_path(__FILE__) . 'css',
        plugin_dir_path(__FILE__) . 'js'
    );

    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // Create CSS and JS files
    cvg_create_css();
    cvg_create_js();
}
register_activation_hook(__FILE__, 'cvg_activate');