<?php

    // Load parent theme's style
    function enqueue_parent_styles () {
        wp_enqueue_style("parent_style", get_template_directory_uri()."/style.css");
        wp_enqueue_style("slick_style", get_stylesheet_directory_uri()."/assets/slick/slick.css");
        wp_enqueue_style("slick_theme", get_stylesheet_directory_uri()."/assets/slick/slick-theme.css");
        wp_enqueue_script("slick_js", get_stylesheet_directory_uri()."/assets/slick/slick.js", array(), "1.0.0", true);
        wp_enqueue_script("slick_test", get_stylesheet_directory_uri()."/assets/slider.js", array("slick_js"), "1.0.0", true);
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
        return $my_num_sections - 1;
    }

    add_filter("twentyseventeen_front_page_sections", "my_front_page_sections", 10, 1);

    // Remove WordPress version from output pages
    remove_action("wp_head", "wp_generator");

    function my_widgets_init() {
        // Register a sidebar area in the post page
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

        // Register a sidebar area in the top navigation, for adding search form
        register_sidebar(
            array(
                'name'          => __( 'Navigation Sidebar', 'twentyseventeen' ),
                'id'            => 'sidebar-nav',
                'description'   => __( 'Add widgets here to appear in top navigation.', 'twentyseventeen' ),
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

		// Site credit
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
         * Aadditional Credit
         */
        $wp_customize->add_setting(
            'additional_credit', array(
                'default'           => 'All rights reserved',
                'transport'         => 'postMessage',
                'sanitize_callback' => 'my_sanitize_additional_credit',
            )
        );
        
        $wp_customize->add_control(
            'additional_credit', array(
                'type'     => 'text',
                'label'    => __( 'Additional Credit', 'twentyseventeen' ),
                'description' => __('Additional credit, used for the MIIT license register number required in China', 'twentyseventeen'),
                'section'  => 'title_tagline',
                'priority' => 56,
            )
        );

        $wp_customize->selective_refresh->add_partial(
            'additional_credit',
            array(
                'selector'        => '.site-info a.additional-credit',
                'render_callback' => 'my_customize_partial_additional_credit',
            )
        );
		
		// SEO keywords
        $wp_customize->add_setting(
            'seo_keywords', array(
                'default'           => 'WordPress',
                'transport'         => 'postMessage',
                'sanitize_callback' => 'my_sanitize_seo_keywords',
            )
        );
        
        $wp_customize->add_control(
            'seo_keywords', array(
                'type'     => 'text',
                'label'    => __( 'SEO keywords', 'twentyseventeen' ),
                'description' => __('SEO keywords mainly for the frontpage, separated by comma', 'twentyseventeen'),
                'section'  => 'title_tagline',
                'priority' => 57,
            )
        );
		
		// SEO description
        $wp_customize->add_setting(
            'seo_description', array(
                'default'           => 'A beautiful WordPress website',
                'transport'         => 'postMessage',
                'sanitize_callback' => 'my_sanitize_seo_description',
            )
        );
        
        $wp_customize->add_control(
            'seo_description', array(
                'type'     => 'text',
                'label'    => __( 'SEO description', 'twentyseventeen' ),
                'description' => __('SEO description mainly for the frontpage', 'twentyseventeen'),
                'section'  => 'title_tagline',
                'priority' => 58,
            )
        );
		
	// Create a setting and control for slides
	for ( $i = 1; $i < 5; $i++ ) {
		$wp_customize->add_setting(
			'slide_' . $i,
			array(
				'default'           => false,
				'sanitize_callback' => 'absint',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'slide_' . $i,
			array(
				/* translators: %d is the front page section number */
				'label'           => sprintf( __( 'Front Page Slide %d Content', 'twentyseventeen' ), $i ),
				'description'     => ( 1 !== $i ? '' : __( 'Select pages to feature in each area from the dropdowns. Add an image to a section by setting a featured image in the page editor. Empty sections will not be displayed.', 'twentyseventeen' ) ),
				'section'         => 'theme_options',
				'type'            => 'dropdown-pages',
				'allow_addition'  => true,
				'active_callback' => 'twentyseventeen_is_static_front_page',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'slide_' . $i,
			array(
				'selector'            => '#slide' . $i,
				'render_callback'     => 'twentyseventeen_front_page_section',
				'container_inclusive' => true,
			)
		);
    }
    // END Create a setting and control for slides
}
    add_action('customize_register', 'my_customize_register');

    // Sanitize site credit input
    function my_sanitize_site_credit( $input ) {
        return esc_attr($input);
    }

    // Render partial for site credit
    function my_customize_partial_site_credit() {
        echo get_theme_mod("site_credit", "Site Credit"); 
    }

    // Sanitize additional credit input
    function my_sanitize_additional_credit( $input ) {
        return esc_attr($input);
    }

    // Render partial for additional credit
    function my_customize_partial_additional_credit() {
        echo get_theme_mod("additional_credit", "Additional Credit"); 
    }

	// Sanitize SEO keywords input
    function my_sanitize_seo_keywords( $input ) {
        return esc_attr($input);
    }
	
	// Sanitize SEO description input
    function my_sanitize_seo_description( $input ) {
        return esc_attr($input);
    }
    // End more customization options
    
    // Begin SEO tags
    function  my_custom_header_tags() {
        if (is_front_page()) {
            $keywords = get_theme_mod("seo_keywords", "WordPress") . ", ";
            $description = get_theme_mod("seo_description", "A beautiful website powered by WordPress");
        } else {
            global $post;
            if ($post->post_excerpt) {
                $description = $post->post_excerpt;
            } else {
                $description = "This is the inner page";;
            }

            if (is_page()) {
                $tags = wp_get_post_terms($post->ID);
                print_r($tags);
                foreach ($tags as $tag) {
                    $keywords = $keywords . $tag->name . ', ';
                }
            }

            if (is_category()) { // Check if it is a category page
                $keywords = wp_strip_all_tags(get_term_meta(get_queried_object_id(), '_cat_keywords', true)) . ", ";
                $description = wp_strip_all_tags(get_term_field('description', get_queried_object_id()), true);
            }
    
            if (function_exists('is_product') and is_product()) { // Addd product tags if this page is a woocommerce product page
                $product_tags = get_the_terms($product->ID, 'product_tag');
                foreach ($product_tags as $product_tag) {
                    $keywords = $keywords . $product_tag->name . ', ';
                }
            }
            
            if (function_exists('is_product_category') and is_product_category()) { // Check if it is a product category page
                $keywords = wp_strip_all_tags(get_term_meta(get_queried_object_id(), '_product_cat_keywords', true)) . ", ";
                $description = wp_strip_all_tags(get_term_field('description', get_queried_object_id()), true);
            }
        }
        $keywords = $keywords . get_bloginfo('name');
        ?>
        <meta name="keywords" content="<?php echo $keywords; ?>">
        <meta name="description" content="<?php echo $description; ?>">
        <?php
    }

    add_action('wp_head', 'my_custom_header_tags', 200, 0);
    // End SEO tags

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
                    'id'            => $this->get_field_id('categoryOfListings'),
                    'name'          => $this->get_field_name('categoryOfListings'),
                    'selected'      => $categoryOfListings,
                    'orderby'       => 'name',
                    'show_count'    => true,
                    'hierarchical'  => true,
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
        
    } // End class Child_Widget

    register_widget('Child_Widget');

// Add keywords for category
function add_cat_keywords(){
    ?> 
    <div class="form-field">
        <label for="cat_keywords"><?php _e('Category Keywords', 'twentyseventeen'); ?></label>
        <input type="text" name="cat_keywords" id="cat_keywords" value="<?php echo $cat_keywords; ?>" size="40">
        <p><?php _e('Category Keywords for better SEO', 'twentyseventeen');; ?></p>
    </div>
    <?php
}
add_action ('category_add_form_fields', 'add_cat_keywords');

function edit_cat_keywords($tag){
    $cat_keywords = get_term_meta($tag->term_id, '_cat_keywords', true);
    ?> 
    <tr class="form-field">
        <th scope="row" valign="top"><label for="cat_keywords"><?php _e('Category Keywords', 'twentyseventeen'); ?></label></th>
        <td>
            <input type="text" name="cat_keywords" id="cat_keywords" value="<?php echo $cat_keywords; ?>" size="40">
            <p class="description"><?php _e('Category Keywords for better SEO', 'twentyseventeen'); ?></p>
            <p class="term_id"><?php echo "tag term_id: " . $tag->term_id; ?></p>
        </td>
    </tr>
    <?php

}
add_action ('category_edit_form_fields', 'edit_cat_keywords');

function add_cat_keywords_success($term_id, $tt_id) {
    if ( isset( $_POST['cat_keywords'] ) ) {
        $cat_keywords = esc_attr($_POST['cat_keywords']);
        add_term_meta($term_id, '_cat_keywords', $cat_keywords, true);
    }
}
add_action ('create_category', 'add_cat_keywords_success', 10, 2); 

function edit_cat_keywords_success($term_id, $tt_id) {
    if ( isset( $_POST['cat_keywords'] ) ) {
        $cat_keywords = esc_attr($_POST['cat_keywords']);
        update_term_meta($term_id, '_cat_keywords', $cat_keywords);
    }
}
add_action ('edited_category', 'edit_cat_keywords_success', 10, 2);
// END Add keywords for category

// Add keywords for product category
function edit_product_cat_keywords($tag){
    $cat_keywords = get_term_meta($tag->term_id, '_product_cat_keywords', true);
    ?> 
    <tr class="form-field">
        <th scope="row" valign="top"><label for="cat_keywords"><?php _e('Product Category Keywords', 'twentyseventeen'); ?></label></th>
        <td>
            <input type="text" name="cat_keywords" id="cat_keywords" value="<?php echo $cat_keywords; ?>" size="40">
            <p class="description"><?php _e('Product Category Keywords for better SEO', 'twentyseventeen'); ?></p>
        </td>
    </tr>
    <?php

}
add_action ('product_cat_edit_form_fields', 'edit_product_cat_keywords');

function edit_product_cat_keywords_success($term_id, $tt_id) {
    if ( isset( $_POST['cat_keywords'] ) ) {
        $cat_keywords = esc_attr($_POST['cat_keywords']);
        update_term_meta($term_id, '_product_cat_keywords', $cat_keywords);
    }
}
add_action ('edited_product_cat', 'edit_product_cat_keywords_success', 10, 2);
// END Add keywords for product category

// BENGIN Add post_tag to page
function add_taxonomies() {
	register_taxonomy(
		'post_tag',
		'page',
		array(
			'hierarchical'          => false,
			'query_var'             => 'tag',
			'rewrite'               => $rewrite['post_tag'],
			'public'                => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'_builtin'              => true,
			'capabilities'          => array(
				'manage_terms' => 'manage_post_tags',
				'edit_terms'   => 'edit_post_tags',
				'delete_terms' => 'delete_post_tags',
				'assign_terms' => 'assign_post_tags',
			),
			'show_in_rest'          => true,
			'rest_base'             => 'tags',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		)
    );
    
    add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'add_taxonomies', 1);
// END Add post_tag to page

	// BEGIN Protect the login page
	function xmag_login_protection(){
		$security_key = "access";
		$security_value = "accesscode";
		if($_GET{$security_key} != $security_value) header('Location: https://google.com/');
	}

	#add_action('login_enqueue_scripts','xmag_login_protection');
	// END Protect the login page