<?php

    // Load parent theme's style
    function enqueue_parent_styles () {
        wp_enqueue_style("parent_style", get_template_directory_uri()."/style.css");
    }

    add_action("wp_enqueue_scripts", "enqueue_parent_styles");

    // Disable DNS prefetch
    function twentyseventeen_disable_dns_prefetch ($hints, $relation_type) {
        if ( 'dns-prefetch' == $relation_type ) {
            return array_diff(wp_dependencies_unique_hosts(), $hints);
        }
        return $hints;
    }

    add_filter("wp_resource_hints", "twentyseventeen_disable_dns_prefetch", 10, 2);

    // Remove Google fonts
	function twentyseventeen_remove_google_fonts () {
		wp_dequeue_style("twentyseventeen-fonts");
	}
	
    add_action("wp_enqueue_scripts", "twentyseventeen_remove_google_fonts", 11);
	
    // Increse the number of front page panels
     function my_front_page_sections ($my_num_sections) {
        return $my_num_sections + 5;
    }

    add_filter("twentyseventeen_front_page_sections", "my_front_page_sections", 10, 1);

    // Add header meta description
    function my_meta_description () {
        echo '<meta name="description" content="This is my meta description" />'."\n";
    }
    add_action("wp_head", "my_meta_description");

    // Remove WordPress version from output pages
    remove_action("wp_head", "wp_generator");

    // Test is_from_page and is_home
    function my_homepage_test () {
        $homepage_test = '';
        if (is_front_page()) {
           $homepage_test .= '<span>is_front_page: true</span>';
       } else {
            $homepage_test .= '<span>is_front_page: false</span>';  
       }

       $homepage_test .= '<span role="separator" aria-hidden="true"></span>';

       if (is_home()) {
            $homepage_test .= '<span>is_home: true</span>';
        } else {
            $homepage_test .= '<span>is_home: false</span>';  
        }

        return $homepage_test;
    }
    add_action("homepage_test", "my_homepage_test");

    function my_widgets_init() {
        register_sidebar(
            array(
                'name'          => __( 'Post Sidebar', 'twentyseventeen' ),
                'id'            => 'sidebar-4',
                'description'   => __( 'Add widgets here to appear in post pages.', 'twentyseventeen' ),
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            )
        );

    }
    add_action( 'widgets_init', 'my_widgets_init' );


    // Add more customization options
    function my_customize_register( $wp_customize ) {
        /**
         * Custom site credit.
         */
        // $wp_customize->get_setting( 'site_credit' )->transport         = 'postMessage';

        $wp_customize->add_setting(
            'site_credit', array(
                'default'           => 'Powered by WordPress',
                'transport'         => 'postMessage',
                'sanitize_callback' => 'my_sanitize_site_credit',
            )
        );
        
        $wp_customize->add_control(
            'site_credit', array(
                'type'     => 'text',
                'label'    => __( 'Site credit', 'twentyseventeen' ),
                'description' => __('The credit information', 'twentyseventeen'),
                'section'  => 'title_tagline',
                'priority' => 55,
            )
        );

        $wp_customize->selective_refresh->add_partial(
            'site_credit',
            array(
                'selector'        => '.site-info a.credit',
                'render_callback' => 'my_customize_partial_site_credit',
            )
        );

        /**
         * Custom MIIT license.
         */
        $wp_customize->add_setting(
            'miit_license', array(
                'default'           => 'doge',
                'transport'         => 'postMessage',
                'sanitize_callback' => 'my_sanitize_miit_license',
            )
        );
        
        $wp_customize->add_control(
            'miit_license', array(
                'type'     => 'text',
                'label'    => __( 'MIIT License', 'twentyseventeen' ),
                'description' => __('The MIIT license register number required in China', 'twentyseventeen'),
                'section'  => 'title_tagline',
                'priority' => 56,
            )
        );

        $wp_customize->selective_refresh->add_partial(
            'miit_license',
            array(
                'selector'        => '.site-info a.miit',
                'render_callback' => 'my_customize_partial_miit_license',
            )
        );
        
    }
    add_action( 'customize_register', 'my_customize_register' );

    // Sanitize site credit input
    function my_sanitize_site_credit( $input ) {
        return esc_attr($input);
    }

    // Render partial for site credit
    function my_customize_partial_site_credit() {
        echo get_theme_mod("site_credit", "Site Credit"); 
    }

    // Sanitize MIIT License input
    function my_sanitize_miit_license( $input ) {
        return esc_attr($input);
    }

    // Render partial for MIIT license
    function my_customize_partial_miit_license() {
        echo get_theme_mod("miit_license", "MIIT License"); 
    }

    // End more customization options
    
    //custom widget
    class Child_Widget extends WP_Widget 
    {
        function __construct() {
            parent::__construct(
                'Child_widget', // Base ID
                'Child Widget', // Name
                array('description' => __( 'Displays your latest listings. Outputs the post thumbnail, title and date per listing'))
            );
        }

        // Output the widget on front end
        function widget($args, $instance) {
            extract( $args );
            // these are the widget options
            $title = apply_filters('widget_title', $instance['title']);
            $numberOfListings = $instance['numberOfListings'];
            $categoryOfListings = $instance['categoryOfListings'];
            echo $before_widget;
            // Check if title is set
            if ( $title ) {
                echo $before_title . $title . $after_title;
            }
            $this->getChildListings($numberOfListings, $categoryOfListings);
            echo $after_widget;
        }

        // Update the widget instance on the back end
        function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['numberOfListings'] = strip_tags($new_instance['numberOfListings']);
            $instance['categoryOfListings'] = strip_tags($new_instance['categoryOfListings']);
            return $instance;
        }
        
        // Create widget form on the backend
        function form($instance) {
            // Check values
            if( $instance) {
                $title = esc_attr($instance['title']);
                $numberOfListings = esc_attr($instance['numberOfListings']);
                $categoryOfListings = esc_attr($instance['categoryOfListings']);
            } else {
                $title = '';
                $numberOfListings = '';
                $categoryOfListings = '';
            }
            ?>
            <div>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'twentyseventeen-child'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </div>

            <div>
                <label for="<?php echo $this->get_field_id('numberOfListings'); ?>"><?php _e('Number of Listings:', 'twentyseventeen-child'); ?></label>		
                <select id="<?php echo $this->get_field_id('numberOfListings'); ?>"  name="<?php echo $this->get_field_name('numberOfListings'); ?>">
                    <?php for($x=1;$x<=10;$x++): ?>
                    <option <?php echo $x == $numberOfListings ? 'selected="selected"' : '';?> value="<?php echo $x;?>"><?php echo $x;?></option>
                    <?php endfor;?>
                </select>
             </div>
             
            <div>
                <label for="<?php echo $this->get_field_id('categoryOfListings'); ?>"><?php _e('Category of Listings:', 'twentyseventeen-child'); ?></label>
                <?php wp_dropdown_categories( array(
                    'id'      => $this->get_field_id('categoryOfListings'),
                    'name'      => $this->get_field_name('categoryOfListings'),
                    'selected'      => $categoryOfListings,
                    'orderby'       => 'name',
                    'show_count'    => true,
                    'hierarchical'  => true
                ) ); ?> 
           </div>
           
            <?php
        }

        // Output the post list
        function getChildListings($numberOfListings, $categoryOfListings) {
            global $post;
            add_image_size( 'myy_widget_size', 85, 45, false );
            $args = array(
                'post_type'        => 'post',
                'posts_per_page'   => $numberOfListings,
                'tax_query'        => array(
                    array(
                        'taxonomy'         => 'category',
                        'terms'            => array($categoryOfListings),
                        'field'            => 'id',
                    ),
                ),
            );
            $listings = new WP_Query($args);
            if($listings->found_posts > 0) {
                echo '<ul class="child_widget">';
                    while ($listings->have_posts()) {
                        $listings->the_post();
                        $image = (has_post_thumbnail($post->ID)) ? get_the_post_thumbnail($post->ID, 'my_widget_size') : '<div class="noThumb"></div>'; 
                        $listItem = '<li>' . $image; 
                        $listItem .= '<a href="' . get_permalink() . '">';
                        $listItem .= get_the_title() . '</a>';
                        $listItem .= '<span>Added ' . get_the_date() . '</span></li>'; 
                        echo $listItem; 
                    }
                echo '</ul>';
                wp_reset_postdata(); 
            } else {
                echo '<p style="padding:25px;">No listing found</p>';
            } 
        }
        
    } //end class Child_Widget

    register_widget('Child_Widget');