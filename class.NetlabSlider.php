<?php

class NetlabSlider
{
    const TEXTDOMAIN        = "netlab-slider-plugin";

    const POSTTYPE_SLIDER   = "slider";
    const POSTTYPE_SLIDE    = "slide";

    const ENGINES = [
        'bootstrap' => "Bootstrap Carousel",
        'slick'     => "Slick Slider",
    ];

    private static $initiated = false;
    
    public function get(string $name)
    {
        // Front Assets
        // --

        self::slider_wp_assets();


        // Parent Slider
        // --

        // Retrieve the Slider
        $slider = new WP_Query([
            'post_type'         => self::POSTTYPE_SLIDER,
            'name'              => $name,
            'posts_per_page'    => 1,
            'order'             => 'ASC'
        ]);

        if (!isset($slider->post) || empty($slider->post))
        {
            return $this;
        }

        $slider_id          = isset($slider->post->ID) ? $slider->post->ID : null;
        $slider_engine      = get_post_meta($slider->post->ID, '_slider-engine-value-key', true);
        $slider_indicator   = get_post_meta($slider->post->ID, '_slider-indicator-value-key', true) != "no" ? true : false;
        $slider_controls    = get_post_meta($slider->post->ID, '_slider-controls-value-key', true) != "no" ? true : false;
        $slider_attr_id     = "slider".uniqid();
        $slider_attr_class  = "carousel-".$name;
        

        // Slides
        // --

        $slides = self::getSlides($slider->post);
        $slides_total = count($slides->posts);
        // $post_count = count($slides->posts);


        $slide_index = 0;

        
        $template = plugin_dir_path(__FILE__)."views/slider-{$slider_engine}.php";
        include $template;

        return $this;
    }


    /**
     * Hooks
     */

    /**
     * Setup the post types
     *
     * @return void
     */
    public static function plugin__register_post_type()
    {
        // Sliders definiton
        $labels = [
            'name'              => _x( 'Sliders', self::TEXTDOMAIN ),
            'singular_name'     => _x( 'Sliders', self::TEXTDOMAIN ),
            'all_items'         => __( 'All Sliders', self::TEXTDOMAIN ),
            'view_item'         => __( 'View Slider', self::TEXTDOMAIN ),
            'add_new_item'      => __( 'Add New Slider', self::TEXTDOMAIN ),
            'add_new'           => __( 'Add New Slider', self::TEXTDOMAIN ),
            'edit_item'         => __( 'Edit Slider', self::TEXTDOMAIN ),
            'update_item'       => __( 'Update Slider', self::TEXTDOMAIN ),
            'search_items'      => __( 'Search Slider', self::TEXTDOMAIN ),
            'search_items'      => __( 'Sliders', self::TEXTDOMAIN ),
        ];
        $args = [
            'label'             => __( 'Slider', self::TEXTDOMAIN ),
            'labels'            => $labels,
            'description'       => __( 'Create New Slider', self::TEXTDOMAIN ),
            'hierarchical'      => true,
            'public'            => true,
            'menu_position'     => 27,
            'has_archive'       => true,
            'map_meta_cap'      => true,
            'capability_type'   => 'post',
            'menu_icon'         => 'dashicons-slides',
            'rewrite'           => false,//['slug' => "-"],
            'supports'          => ['title', 'excerpt'],
        ];
        register_post_type( self::POSTTYPE_SLIDER, $args ); 

        // Slides definition
        $labels = [
            'name'              => __( 'Slides', self::TEXTDOMAIN ),
            'singular_name'     => __( 'Slides', self::TEXTDOMAIN ),
            'all_items'         => __( 'All Slides', self::TEXTDOMAIN ),
            'view_item'         => __( 'View Slide', self::TEXTDOMAIN ),
            'add_new_item'      => __( 'Add New Slide', self::TEXTDOMAIN ),
            'add_new'           => __( 'Add New Slide', self::TEXTDOMAIN ),
            'edit_item'         => __( 'Edit Slide', self::TEXTDOMAIN ),
            'update_item'       => __( 'Update Slide', self::TEXTDOMAIN ),
            'search_items'      => __( 'Search Slide', self::TEXTDOMAIN ),
            'search_items'      => __( 'Slides', self::TEXTDOMAIN )
        ];
        $args = [
            'label'             => __( 'Slide', self::TEXTDOMAIN ),
            'labels'            => $labels,
            'description'       => __( 'Add New Slide contents', self::TEXTDOMAIN ),
            'hierarchical'      => false,
            'public'            => true,
            'menu_position'     => 3,
            'show_in_menu'      => 'edit.php?post_type='.self::POSTTYPE_SLIDE,
            'has_archive'       => true,
            'map_meta_cap'      => true,
            'capability_type'   => 'post',
            'menu_icon'         =>'dashicons-format-image',
            'rewrite'           => false,//['slug' => false, 'with_front' => false],
            'supports'          => ['title', 'thumbnail', 'excerpt', 'page-attributes'],
        ];
        register_post_type( self::POSTTYPE_SLIDE, $args ); 
    }

    public static function plugin_activation()
    {
        self::plugin__register_post_type();
    }
    public static function plugin_deactivation()
    {
    }
    public static function plugin_init()
    {
        self::plugin__register_post_type();

        remove_post_type_support( self::POSTTYPE_SLIDER, 'editor' );
        remove_post_type_support( self::POSTTYPE_SLIDER, 'slug' );
        add_post_type_support( 'slider', 'disabled_post_lock' );

		if ( !self::$initiated ) {
			self::plugin_init_hooks();
		}
    }
    private static function plugin_init_hooks()
    {
        self::$initiated = true;
        
        // Assets
        // --

        add_action('admin_enqueue_scripts', ['NetlabSlider', 'slider_admin_assets']);


        // Sliders admin MetaBoxes
        // --

        // slider_permalink
        add_action('admin_head', ['NetlabSlider', 'slider_permalink']);
        add_action('add_meta_boxes', ['NetlabSlider', 'slider_slug']);
        add_action('save_post', ['NetlabSlider', 'slider_slug_save']);

        // slider_engine
        add_action('add_meta_boxes',['NetlabSlider', 'slider_engine']);
        add_action('save_post', ['NetlabSlider', 'slider_engine_save']);

        // slider_indicator
        add_action('add_meta_boxes',['NetlabSlider', 'slider_indicator']);
        add_action('save_post', ['NetlabSlider', 'slider_indicator_save']);

        // slider_controls
        add_action('add_meta_boxes',['NetlabSlider', 'slider_controls']);
        add_action('save_post', ['NetlabSlider', 'slider_controls_save']);

        // slider_slides
        add_action('add_meta_boxes',['NetlabSlider', 'slider_slides']);
        

        // Slides admin MetaBoxes
        // --

        add_action('admin_menu', ['NetlabSlider', 'my_admin_menu']); 

        // slider_permalink
        add_action('admin_head', ['NetlabSlider', 'slide_permalink']);

        // slide_parent
        add_action('add_meta_boxes', ['NetlabSlider', 'slide_parent']);

        // slide_link
        add_action('add_meta_boxes', ['NetlabSlider', 'slide_link']);
        add_action('save_post', ['NetlabSlider', 'slide_link_save']);
    }


    // Assets
    // --

    public static function slider_admin_assets() 
    {
        wp_enqueue_style('_netlab_slider', NETLAB_SLIDER__PLUGIN_URL.'assets/styles/style.css', false, '1.0.0');
    }
    public static function slider_wp_assets()
    {
        // Define Handles
        $handle_bootstrap   = defined('NETLAB_SLIDER__ASSETS_HANDLE__BOOTSTRAP')    ? NETLAB_SLIDER__ASSETS_HANDLE__BOOTSTRAP   : 'bootstrap';
        $handle_jquery      = defined('NETLAB_SLIDER__ASSETS_HANDLE__JQUERY')       ? NETLAB_SLIDER__ASSETS_HANDLE__JQUERY      : 'jquery';
        $handle_popperjs    = defined('NETLAB_SLIDER__ASSETS_HANDLE__POPPERJS')     ? NETLAB_SLIDER__ASSETS_HANDLE__POPPERJS    : 'popperjs';
        $handle_slick       = defined('NETLAB_SLIDER__ASSETS_HANDLE__SLICK')        ? NETLAB_SLIDER__ASSETS_HANDLE__SLICK       : 'slick';

        // Add Bootstrap JS
        if (!wp_script_is($handle_bootstrap, 'enqueued'))
        {
            wp_enqueue_script($handle_bootstrap, NETLAB_SLIDER__PLUGIN_URL.'assets/scripts/bootstrap.min.js', [
                $handle_jquery,
                $handle_popperjs,
            ],  "4.5.2", true);
        }

        // Add jQuery
        if (!wp_script_is($handle_jquery, 'enqueued'))
        {
            wp_enqueue_script($handle_jquery, NETLAB_SLIDER__PLUGIN_URL.'assets/scripts/jquery.min.js', [],  "3.5.1", true);
        }

        // Add Popper JS
        if (!wp_script_is($handle_popperjs, 'enqueued'))
        {
            wp_enqueue_script($handle_popperjs, NETLAB_SLIDER__PLUGIN_URL.'assets/scripts/popper.min.js', [],  "1.16.0", true);
        }

        // Add Slick
        if (!wp_script_is($handle_slick, 'enqueued'))
        {
            wp_enqueue_style($handle_slick, NETLAB_SLIDER__PLUGIN_URL.'assets/styles/slick.css', false, '1.0.0' );

            wp_enqueue_script($handle_slick, NETLAB_SLIDER__PLUGIN_URL.'assets/scripts/slick.min.js', [
                $handle_jquery,
            ],  "1.8.1", true);
            wp_enqueue_script('netlab-slider', NETLAB_SLIDER__PLUGIN_URL.'assets/scripts/app.js', [
                $handle_slick,
            ],  null, true);
        }
    }


    // Slider
    // --

    public static function slider_permalink() 
    {
        global $post_type;

        if ($post_type == self::POSTTYPE_SLIDER) 
        {
            echo "<style>#edit-slug-box {display:none;}</style>";
        }
    }
    public static function slider_slug()
    {
        add_meta_box('slider-slug', __('Slider Name', self::TEXTDOMAIN), ['NetlabSlider', 'slider_slug_content'], self::POSTTYPE_SLIDER, 'side', 'high');
    }
    public static function slider_slug_content($post)
    {
        if (in_array($post->post_name, ["auto-draft"]))
        {
            return false;
        }

        echo $post->post_name;
    }
    public static function slider_slug_save($post_id)
    {
        // verify post is not a revision
        if (!wp_is_post_revision( $post_id ))
        {
            // unhook this function to prevent infinite looping
            remove_action('save_post', ['NetlabSlider', 'slider_slug_save'] );

            $post = get_post($post_id);

            // update the post slug
            wp_update_post( array(
                'ID' => $post_id,
                'post_name' => sanitize_title($post->post_title)
            ));

            // re-hook this function
            add_action('save_post', ['NetlabSlider', 'slider_slug_save'] );
        }
    }

    public static function slider_engine()
    {
        add_meta_box('slider-engine', __('Engine', self::TEXTDOMAIN), ['NetlabSlider', 'slider_engine_content'], self::POSTTYPE_SLIDER);
    }
    public static function slider_engine_content($post)
    {
        wp_nonce_field('slider-engine-save','slider-engine-meta-box-nonce');
        $value = get_post_meta($post->ID,'_slider-engine-value-key',true);
        ?>
        <select name="slider-engine-field" id="slider-engine-field">
            <option value="">-</option>
            <?php foreach(self::ENGINES as $key => $label): ?>
            <option value="<?= $key ?>" <?= $value == $key ? "selected" : null ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    public static function slider_engine_save($post_id)
    {
        if( ! isset($_POST['slider-engine-meta-box-nonce'])) {
           return;
        }
        if( ! wp_verify_nonce( $_POST['slider-engine-meta-box-nonce'], 'slider-engine-save') ) {
           return;
        }
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
           return;
        }
        if( ! current_user_can('edit_post', $post_id)) {
           return;
        }
        if( ! isset($_POST['slider-engine-field'])) {
           return;
        }
        $slider_link = sanitize_text_field($_POST['slider-engine-field']);
        update_post_meta( $post_id,'_slider-engine-value-key', $slider_link );
    }

    public static function slider_indicator()
    {
        add_meta_box('slider-indicator', __('Indicator', self::TEXTDOMAIN), ['NetlabSlider', 'slider_indicator_content'], self::POSTTYPE_SLIDER);
    }
    public static function slider_indicator_content($post)
    {
        wp_nonce_field('slider-indicator-save','slider-indicator-meta-box-nonce');
        $value = get_post_meta($post->ID,'_slider-indicator-value-key',true);
        ?>
        <select name="slider-indicator-field" id="slider-indicator-field">
            <option value="yes" <?= $value == "yes" ? "selected" : null ?>>Yes</option>
            <option value="no" <?= $value == "no" ? "selected" : null ?>>No</option>
        </select>
        <?php
    }
    public static function slider_indicator_save($post_id)
    {
        if( ! isset($_POST['slider-indicator-meta-box-nonce'])) {
           return;
        }
        if( ! wp_verify_nonce( $_POST['slider-indicator-meta-box-nonce'], 'slider-indicator-save') ) {
           return;
        }
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
           return;
        }
        if( ! current_user_can('edit_post', $post_id)) {
           return;
        }
        if( ! isset($_POST['slider-indicator-field'])) {
           return;
        }
        $slider_link = sanitize_text_field($_POST['slider-indicator-field']);
        update_post_meta( $post_id,'_slider-indicator-value-key', $slider_link );
    }

    public static function slider_controls()
    {
        add_meta_box('slider-controls', __('Controls', self::TEXTDOMAIN), ['NetlabSlider', 'slider_controls_content'], self::POSTTYPE_SLIDER);
    }
    public static function slider_controls_content($post)
    {
        wp_nonce_field('slider-controls-save','slider-controls-meta-box-nonce');
        $value = get_post_meta($post->ID,'_slider-controls-value-key',true);
        ?>
        <select name="slider-controls-field" id="slider-controls-field">
            <option value="yes" <?= $value == "yes" ? "selected" : null ?>>Yes</option>
            <option value="no" <?= $value == "no" ? "selected" : null ?>>No</option>
        </select>
        <?php
    }
    public static function slider_controls_save($post_id)
    {
        if( ! isset($_POST['slider-controls-meta-box-nonce'])) {
           return;
        }
        if( ! wp_verify_nonce( $_POST['slider-controls-meta-box-nonce'], 'slider-controls-save') ) {
           return;
        }
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
           return;
        }
        if( ! current_user_can('edit_post', $post_id)) {
           return;
        }
        if( ! isset($_POST['slider-controls-field'])) {
           return;
        }
        $slider_link = sanitize_text_field($_POST['slider-controls-field']);
        update_post_meta( $post_id,'_slider-controls-value-key', $slider_link );
    }

    public static function slider_slides()
    {
        add_meta_box('slider-slides', __('Slides', self::TEXTDOMAIN), ['NetlabSlider', 'slider_slides_content'], self::POSTTYPE_SLIDER);
        add_filter('postbox_classes_slider_slider-slides', ['NetlabSlider', 'slider_slides_classes']);
    }
    public static function slider_slides_content($post)
    {
        $slides = self::getSlides($post);
        ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th scope="col" id="title" class="manage-column column-title column-primary">
                        <strong>Title</strong>
                    </th>
                    <th scope="col" id="date" class="manage-column column-date">
                        <strong>Order</strong>
                    </th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($slides->posts as $post): ?>
                <tr id="post-<?= $post->ID ?>">
                    <td>
                        <strong><a class="row-title" href="post.php?post=<?= $post->ID ?>&amp;action=edit" aria-label="<?= $post->post_title ?>” (Edit)"><?= $post->post_title ?></a></strong>
                    </td>
                    <td>
                        <?= $post->menu_order ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
        <?php
    }
    public static function slider_slides_classes($classes) 
    {
        array_push($classes, 'inside-no-padding');
        return $classes;
    }


    // Slide
    // --
    public static function my_admin_menu() { 
        add_submenu_page('edit.php?post_type='.self::POSTTYPE_SLIDER, 'All Slides', 'All Slides', 'manage_options', 'edit.php?post_type='.self::POSTTYPE_SLIDE); 
        add_submenu_page('edit.php?post_type='.self::POSTTYPE_SLIDER, 'Add New Slide', 'Add New Slide', 'manage_options', 'post-new.php?post_type='.self::POSTTYPE_SLIDE); 
    } 

    public static function slide_permalink() 
    {
        global $post_type;

        if ($post_type == self::POSTTYPE_SLIDE) 
        {
            echo "<style>#edit-slug-box {display:none;}</style>";
        }
    }

    /**
     * Creation de la liste de selection du "slider" dans le formulaire "slide"
     */
    public static function slide_parent() {
        add_meta_box( 'slide-parent', 'Sliders', ['NetlabSlider', 'slide_parent_content'], self::POSTTYPE_SLIDE, 'side', 'high' );
    }
    public static function slide_parent_content($post) 
    {
        $pages = wp_dropdown_pages([
            'post_type'         => 'slider', 
            'selected'          => $post->post_parent, 
            'name'              => 'parent_id', 
            'show_option_none'  => __( '(no parent)' ), 
            'sort_column'       => 'menu_order, post_title', 
            'echo'              => 0 
        ]);

        if (!empty($pages)) echo $pages;
    }

    /**
     * Création de la metabox "lien de la slide"
     */
    public static function slide_link()
    {
        add_meta_box('slide-link','Slide Link', ['NetlabSlider', 'slide_link_content'], self::POSTTYPE_SLIDE);
    }
    public static function slide_link_content($post)
    {
        wp_nonce_field('slide-link-save','slide-link-meta-box-nonce');
        $value = get_post_meta($post->ID,'_slide-link-value-key',true);
        ?>
        <input type="text" name="slide-link-field" id="slide-link-field" value="<?= esc_attr( $value ); ?>" size="100" />
        <?php
    }
    public static function slide_link_save($post_id)
    {
        if( ! isset($_POST['slide-link-meta-box-nonce'])) {
           return;
        }
        if( ! wp_verify_nonce( $_POST['slide-link-meta-box-nonce'], 'slide-link-save') ) {
           return;
        }
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
           return;
        }
        if( ! current_user_can('edit_post', $post_id)) {
           return;
        }
        if( ! isset($_POST['slide-link-field'])) {
           return;
        }
        $slider_link = sanitize_text_field($_POST['slide-link-field']);
        update_post_meta( $post_id,'_slide-link-value-key', $slider_link );
    }






    private static function getSlides($post)
    {
        return new WP_Query([
            'post_type'     => self::POSTTYPE_SLIDE,
            'post_parent'   => $post->ID,
            'orderby'       => 'menu_order',
            'order'         => 'ASC',
        ]);
    }

    // public static function plugin_after_theme_support()
    // {
    //     add_theme_support( 'post-thumbnails' );

    //     $sizes = NETLAB_PLUGIN__CONFIG['image_sizes'];
    //     // $sizes = isset(NETLAB_PLUGIN__CONFIG['image_sizes']) ? NETLAB_PLUGIN__CONFIG['image_sizes'] : [];
    //     $sizes = is_array($sizes) ? $sizes : [];
        
    //     foreach ($sizes as $size)
    //     {
    //         add_image_size($size[0], $size[1], $size[2], $size[3]);
    //     }
    // }
}
