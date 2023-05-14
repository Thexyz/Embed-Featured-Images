    <?php
    /*
    Plugin Name: Embed Featured Image
    Plugin URI: https://github.com/Thexyz/Embed-Featured-Images
    Description: Displays code to allow the featured image to be embedded on an external site with light and dark mode options.
    Version: 1.0
    Author: Thexyz
    Author URI: https://www.thexyz.com/
    License: GPL2
    */
    
    // Add a shortcode to display the code for embedding the featured image
    add_shortcode( 'embed_featured_image', 'embed_featured_image_shortcode' );
    
    function embed_featured_image_shortcode() {
    
        // Get the ID of the current post's featured image
        $featured_image_id = get_post_thumbnail_id();
    
        // If a featured image exists, display the code for embedding the image
        if ( $featured_image_id ) {
            $image_url = wp_get_attachment_url( $featured_image_id );
            $embed_code = '<img src="' . $image_url . '" alt="' . get_the_title() . '" />';
            $code_box = '<div class="embed-code-box">' .
                            '<textarea readonly>' . htmlentities( $embed_code ) . '</textarea>' .
                            '<button onclick="copyEmbedCode()">Copy Embed Code</button>' .
                        '</div>';
            return $code_box;
        } else {
            return '<p>No featured image found.</p>';
        }
    }
    
    // Automatically add the shortcode to the post content
    add_filter( 'the_content', 'embed_featured_image_auto_shortcode' );
    
    function embed_featured_image_auto_shortcode( $content ) {
        $display_setting = get_option('embed_featured_image_display_setting', 'posts_and_pages');
        $should_display = false;
    
        if (($display_setting === 'posts_and_pages') ||
            ($display_setting === 'posts' && get_post_type() === 'post') ||
            ($display_setting === 'pages' && get_post_type() === 'page')) {
            $should_display = true;
        }
    
        if (has_post_thumbnail() && $should_display) {
            $content .= '[embed_featured_image]';
        }
        return $content;
    }
    
    // Add the required CSS and JavaScript to the page
    add_action( 'wp_enqueue_scripts', 'embed_featured_image_scripts' );
    
    function embed_featured_image_scripts() {
        wp_enqueue_style( 'embed-featured-image-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
        wp_enqueue_script( 'embed-featured-image-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), '1.0', true );
    
        // Localize the script with necessary data
        wp_localize_script( 'embed-featured-image-script', 'embedFeaturedImageSettings', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'save_embed_featured_image_theme' ),
        ) );
    }
    
    // Add settings page
    add_action( 'admin_menu', 'embed_featured_image_add_settings_page' );
    
    function embed_featured_image_add_settings_page() {
        add_options_page( 'Embed Featured Image Settings', 'Embed Featured Image', 'manage_options', 'embed-featured-image-settings', 'embed_featured_image_render_settings_page' );
    }
    
    function embed_featured_image_render_settings_page() {
        if ( isset( $_POST['submit'] ) ) {
            check_admin_referer( 'embed_featured_image_save_settings' );
            update_option( 'embed_featured_image_theme', sanitize_text_field( $_POST['theme'] ) );
            update_option( 'embed_featured_image_display_setting', sanitize_text_field( $_POST['display_setting'] ) );
        }
    
        $current_theme = get_option( 'embed_featured_image_theme', 'light' );
        $current_display_setting = get_option( 'embed_featured_image_display_setting', 'posts_and_pages' );
    
    
    
        ?>
        <div class="wrap">
            <h1>Embed Featured Image Settings</h1>
            <form method="post">
                <?php wp_nonce_field( 'embed_featured_image_save_settings' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Theme</th>
                        <td>
                            <select name="theme">
                                <option value="light" <?php selected( $current_theme, 'light' ); ?>>Light</option>
                                <option value="dark" <?php selected( $current_theme, 'dark' ); ?>>Dark</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Display on</th>
                        <td>
                            <select name="display_setting">
                                <option value="posts_and_pages" <?php selected( $current_display_setting, 'posts_and_pages' ); ?>>Posts and Pages</option>
                                <option value="posts" <?php selected( $current_display_setting, 'posts' ); ?>>Posts only</option>
                                <option value="pages" <?php selected( $current_display_setting, 'pages' ); ?>>Pages only</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>
            </form>
        </div>
        <?php
    }
    
    // Save the theme when toggled
    add_action( 'wp_ajax_save_embed_featured_image_theme', 'embed_featured_image_save_theme' );
    
    function embed_featured_image_save_theme() {
        check_ajax_referer( 'save_embed_featured_image_theme', 'nonce' );
    
        if ( isset( $_POST['theme'] ) ) {
            $theme = sanitize_text_field( $_POST['theme'] );
            update_option( 'embed_featured_image_theme', $theme );
        }
    
        wp_die();
    }
    
