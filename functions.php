<?php

/**
 * Returns recent posts for given category and excludes given post formats
 */
if ( ! function_exists( 'raamdev_get_recent_posts' ) ) :
	function raamdev_get_recent_posts( $number_posts = '10', $category = '', $exclude_formats = array() ) {

		// Make sure category exists
		if ( ! get_cat_ID( $category ) ) {
			return FALSE;
		}

		// Build array of format exclution queries
		if ( ! empty( $exclude_formats ) ) :
			$i         = 0;
			$tax_query = array();

			foreach ( $exclude_formats as $format ) {

				$tax_query[$i] = array(
					'taxonomy' => 'post_format',
					'field'    => 'slug',
					'terms'    => 'post-format-' . $format,
					'operator' => 'NOT IN'
				);

				$i ++;
			}
		endif;

		?>
		<ul>
			<?php
			$args = array( 'numberposts' => $number_posts, 'category' => get_cat_ID( $category ), 'post_status' => 'publish', 'tax_query' => $tax_query );
			$recent_posts = wp_get_recent_posts( $args );
			foreach ( $recent_posts as $recent ) {
				echo '<li><a href="' . get_permalink( $recent["ID"] ) . '" title="Look ' . esc_attr( $recent["post_title"] ) . '" >' . $recent["post_title"] . '</a> </li> ';
			}
			?>
		</ul>
	<?php
	}
endif;

/*
 * Add Twitter handle to end of tweet text when sharing via Twitter
 */
function indiepub_sharing_buttons_tweet_text($tweet_text) {
	return $tweet_text . '%20via%20@raamdev';
}
add_filter('indiepub_sharing_buttons_tweet_text', 'indiepub_sharing_buttons_tweet_text');