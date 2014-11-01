<?php

/*
 * Returns recent posts for given category and excludes given post formats
 */
function raamdev_get_recent_posts( $number_posts = '10', $category = '', $exclude_formats = array() ) {

	$tax_query = array();

	// Make sure category exists
	if ( ! get_cat_ID( $category ) ) {
		return FALSE;
	}

	// Build array of format exclusion queries
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

/*
 * Add Twitter handle to end of tweet text when sharing via Twitter
 */
function indiepub_sharing_buttons_tweet_text( $tweet_text ) {
	return $tweet_text . '%20via%20@raamdev';
}

add_filter( 'indiepub_sharing_buttons_tweet_text', 'indiepub_sharing_buttons_tweet_text' );

/*
 * Hide the Twitter handle when adding mentions to posts
 * with Twitter Mentions as Comment Plugin
 */
function tmac_hide_twitter_handle() {
	return TRUE;
}

add_filter( 'tmac_hide_twitter_handle', 'tmac_hide_twitter_handle' );

/**
 * Filter post formats from RSS feed
 * @var $wp_query WP_Query Reference for IDEs.
 */
function raamdev_rss_filter_post_formats( &$wp_query ) {
	if ( $wp_query->is_feed() ) {
		if ( isset( $wp_query->query_vars['rss-post-format-aside'] ) ) { // Only return Asides and Quotes
			$post_format_tax_query = array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => array('post-format-aside', 'post-format-quote'),
				'operator' => 'IN'
			);
			$tax_query             = $wp_query->get( 'tax_query' );
			if ( is_array( $tax_query ) ) {
				$tax_query = $tax_query + $post_format_tax_query;
			}
			else {
				$tax_query = array( $post_format_tax_query );
			}
			$wp_query->set( 'tax_query', $tax_query );
		}
		else if ( isset( $wp_query->query_vars['rss-post-format-image'] ) ) { // Only return Images

			$post_format_tax_query = array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => 'post-format-image',
				'operator' => 'IN'
			);
			$tax_query             = $wp_query->get( 'tax_query' );
			if ( is_array( $tax_query ) ) {
				$tax_query = $tax_query + $post_format_tax_query;
			}
			else {
				$tax_query = array( $post_format_tax_query );
			}
			$wp_query->set( 'tax_query', $tax_query );
		}
		else if ( isset( $wp_query->query_vars['rss-post-format-standard'] ) ) { //

			$post_format_tax_query = array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => array( 'post-format-aside', 'post-format-image', 'post-format-quote' ),
				'operator' => 'NOT IN'
			);
			$tax_query             = $wp_query->get( 'tax_query' );
			if ( is_array( $tax_query ) ) {
				$tax_query = $tax_query + $post_format_tax_query;
			}
			else {
				$tax_query = array( $post_format_tax_query );
			}
			$wp_query->set( 'tax_query', $tax_query );
		}
	}
}

add_action( 'pre_get_posts', 'raamdev_rss_filter_post_formats' );


/*
 * Query vars used for filtering RSS feeds
 */
function raamdev_add_query_vars( $aVars ) {
	$aVars[] = "rss-post-format-aside";
	$aVars[] = "rss-post-format-image";
	$aVars[] = "rss-post-format-standard";
	return $aVars;
}

add_filter( 'query_vars', 'raamdev_add_query_vars' );

/*
 * Changes the RSS feed title to rename specific post formats
 */
function raamdev_rss_change_title() {
	$title = get_wp_title_rss();
	if ( strpos( $title, "Aside" ) ) {
		$new_title = str_replace( "Aside", "Thoughts", $title );
		echo $new_title;
	}
	else {
		echo get_wp_title_rss();
	}
}

add_filter( 'wp_title_rss', 'raamdev_rss_change_title', 1 );

/*
 * Allow Custom MIME Types to be uploaded via WordPress Media Library
 */
function raamdev_custom_mime_media_types( $mimes ) {
	$mimes = array_merge( $mimes, array(
		'epub|mobi' => 'application/octet-stream'
	) );
	return $mimes;
}

add_filter( 'upload_mimes', 'raamdev_custom_mime_media_types' );

/*
 * Shortcode for including Static HTML Files in posts
 */
function raamdev_sc_static_html( $atts ) {

	$file = NULL;
	$subdir = NULL;

	// Extract Shortcode Parameters/Attributes
	extract( shortcode_atts( array(
		'subdir' => NULL,
		'file'   => NULL
	), $atts ) );

	// Set file path
	$path_base = ABSPATH . "wp-content/static-files/";
	$path_file = ( $subdir == NULL ) ? $path_base . $file : $path_base . $subdir . "/" . $file;

	// Load file or, if absent. throw error
	if ( file_exists( $path_file ) ) {
		$file_content = file_get_contents( $path_file );
		return $file_content;
	}
	else {
		trigger_error( "'$path_file' file not found", E_USER_WARNING );
		return "FILE NOT FOUND: " . $path_file . "
SUBDIR = " . $subdir . "
FILE = " . $file . "

";
	}
}

add_shortcode( 'static_html', 'raamdev_sc_static_html' );

/*
 * Return the full permalink instead of the shortlink.
 * Prefer the full permalink over the shortlink so the domain (raamdev.com)
 * appears in the URL when social sites pull page metadata (as opposed to
 * wp.me URLs or raamdev.com/?p=1234).
 */
function raamdev_custom_shortlink() {
	$permalink = get_permalink();
	return $permalink;
}

add_filter( 'get_shortlink', 'raamdev_custom_shortlink' );

/*
 * Add "My Account" and "Logout" menu items to nav menu when logged in
 *
 * @param $nav
 * @param $args
 *
 * @return string
 */
function raamdev_logged_in_menu_items( $nav, $args ) {

	if ( is_user_logged_in() && ! is_single() && $args->theme_location == 'primary' ) {
		$nav             = $nav . '<li class="menu-item menu-space-separator">&nbsp;</li>';
		$my_account_link = '<li class="menu-item my-account-menu-item"><a href="/account/">My Account</a></li>';
		$nav             = $nav . $my_account_link;
		$logout_link     = '<li class="menu-item logout-menu-item"><a href="' . wp_logout_url() . '">Logout</a></li>';
		$nav             = $nav . $logout_link;
	}
	return $nav;
}

add_filter( 'wp_nav_menu_items', 'raamdev_logged_in_menu_items', 10, 2 );


/*
 * Load the Link Library Tooltips javascript when the Link Library shortcode is used on the page.
 *
 * This JavaScript is used to show the link description when hovering over the link.
 *
 */
function raamdev_link_library_tooltips() {
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'link-library') ) {
		wp_enqueue_script( 'link-library-tooltips', get_stylesheet_directory_uri() . '/link-library-tooltips.js', array(), '1.0.0', true );
	}
}
add_action( 'wp_enqueue_scripts', 'raamdev_link_library_tooltips');

/*
 * Use a custom image for og:image meta tag when post has no image
 */
function raamdev_custom_image( $media, $post_id, $args ) {
	if ( $media ) {
		return $media;
	} else {
		$permalink = get_permalink( $post_id );
		$url = apply_filters( 'jetpack_photon_url', 'https://s3.amazonaws.com/cdn.raamdev.com/wordpress/wp-content/uploads/2014/03/raam_2014-01-orig.jpg' );

		return array( array(
			              'type'  => 'image',
			              'from'  => 'custom_fallback',
			              'src'   => esc_url( $url ),
			              'href'  => $permalink,
		              ) );
	}
}
add_filter( 'jetpack_images_get_images', 'raamdev_custom_image', 10, 3 );

/*
 * Use a custom image for og:image on home page
 */
function raamdev_home_image( $tags ) {
	if ( is_home() || is_front_page() ) {
		// Remove the default blank image added by Jetpack
		unset( $tags['og:image'] );

		$raamdev_home_img = 'https://s3.amazonaws.com/cdn.raamdev.com/wordpress/wp-content/uploads/2014/03/raam_2014-01-orig.jpg';
		$tags['og:image'] = esc_url( $raamdev_home_img );
	}
	return $tags;
}
add_filter( 'jetpack_open_graph_tags', 'raamdev_home_image' );

/*
 * Use @raamdev for twitter:site meta tag (instead of default @jetpack)
 */
function tweakjp_custom_twitter_site( $og_tags ) {
	$og_tags['twitter:site'] = '@raamdev';
	return $og_tags;
}
add_filter( 'jetpack_open_graph_tags', 'tweakjp_custom_twitter_site', 11 );
