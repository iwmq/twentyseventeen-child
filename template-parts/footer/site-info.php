<?php
/**
 * Displays footer site info
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?>
<div class="site-info">
	<?php
	if ( function_exists( 'the_privacy_policy_link' ) ) {
		the_privacy_policy_link( '', '<span role="separator" aria-hidden="true"></span>' );
	}
	?>
	<a href="/" class="credit">
		&copy;<?php echo get_theme_mod("site_credit", "Site Credit"); ?>
	</a>
	<span role="separator" aria-hidden="true"></span>
	<a href="/" class="additional-credit">
		<?php echo get_theme_mod("additional_credit", "Additional Credit"); ?>
	</a>
</div><!-- .site-info -->
