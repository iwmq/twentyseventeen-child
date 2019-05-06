<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- SEO begins -->
<?php
    if (is_front_page()) {
        $keywords = get_theme_mod("seo_keywords", "WordPress");
        $description = get_theme_mod("seo_description", "A beautiful website powered by WordPress");
    } else {
        if ($post->post_excerpt) {
            $description = $post->post_excerpt;
        } else {
            $description = "This is the inner page";;
        }

        if (function_exists('is_product') and is_product()) { // Addd product tags if this page is a woocommerce product page
            $product_tags = get_the_terms($product->ID, 'product_tag');
            foreach ($product_tags as $product_tag) {
                $keywords = $keywords . $product_tag->name . ', ';
            }
        } else { // Not a product page
            $tags = wp_get_post_tags($post->ID);
            foreach ($tags as $tag) {
                $keywords = $keywords . $tag->name . ', ';
            }
        }
        $keywords = $keywords . get_bloginfo('name');
    }
?>
<meta name="keywords" content="<?php echo $keywords; ?>">
<meta name="description" content="<?php echo $description; ?>">
<!-- SEO ends -->
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentyseventeen' ); ?></a>

	<header id="masthead" class="site-header" role="banner">

		<?php get_template_part( 'template-parts/header/header', 'image' ); ?>

		<?php if ( has_nav_menu( 'top' ) ) : ?>
			<div class="navigation-top">
				<div class="wrap">
						<?php get_template_part( 'template-parts/navigation/navigation', 'top' ); ?>
				</div><!-- .wrap -->
			</div><!-- .navigation-top -->
		<?php endif; ?>

	</header><!-- #masthead -->

	<?php

	/*
	 * If a regular post or page, and not the front page, show the featured image.
	 * Using get_queried_object_id() here since the $post global may not be set before a call to the_post().
	 */
	if ( ( is_single() || ( is_page() && ! twentyseventeen_is_frontpage() ) ) && has_post_thumbnail( get_queried_object_id() ) ) :
		echo '<div class="single-featured-image-header">';
		echo get_the_post_thumbnail( get_queried_object_id(), 'twentyseventeen-featured-image' );
		echo '</div><!-- .single-featured-image-header -->';
	endif;
	?>

	<div class="site-content-contain">
		<div id="content" class="site-content">
		<?php get_sidebar("slider"); ?>