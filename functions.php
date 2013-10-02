<?php

/**
 * We store the RSS Journal Key in a separate file.
 * This key is used to access protected Journal entries
 * via RSS and allows us to give MailChimp an RSS URL
 * for RSS-to-Email campaigns for Journal subscribers.
 */
require_once( WP_CONTENT_DIR . '/private/rss-journal-key.php' );

/**
 * Returns recent posts for given category and excludes given post formats
 */
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

/*
 * Add Twitter handle to end of tweet text when sharing via Twitter
 */
function indiepub_sharing_buttons_tweet_text( $tweet_text ) {
	return $tweet_text . '%20via%20@raamdev';
}

add_filter( 'indiepub_sharing_buttons_tweet_text', 'indiepub_sharing_buttons_tweet_text' );

/**
 * Returns true if post is more than 1 year old or user has access, otherwise returns false
 */
function raamdev_is_journal_viewable() {
	$release_after = 365 * 86400; // days * seconds per day
	$post_age      = date( 'U' ) - get_post_time( 'U' );
	if ( $post_age > $release_after || current_user_can( "access_s2member_level1" ) )
		return TRUE;
	else
		return FALSE;
}

/**
 * Returns message about journal release
 */
function raamdev_was_journal_entry_message() {
	// only show this message if the user is not logged in or doesn't have access
	if ( ! is_user_logged_in() || ! current_user_can( "access_s2member_level1" ) ) :
		?>
		<div style="font-size: 80%; border: 1px solid #eee; padding: 20px; margin-bottom: 20px; line-height: 1.4em; background: #eee;">This is an entry from my
			<a href="http://raamdev.com/about/journal/">personal Journal</a> and it was published over one year ago. It was initially only available to paying subscribers. However, as per my
			<a href="http://raamdev.com/income-ethics-series/#public_domain">Income Ethics</a>, "all non-free creative work will be made public domain within one year". So, after spending one year behind a paywall, this content is now free. Ah, sweet freedom!
		</div>
	<?php
	endif;
}

/**
 * Returns message about journal not released yet
 */
function raamdev_journal_not_released_message() {
	?>

	<?php $post_id = get_the_ID(); ?>
	<style type="text/css">
		<?php echo '#post-' . $post_id . ' header'; ?>
		{
			opacity: 0.5
		;
		}
	</style>
	<div id="journal-notice">
		<p>This journal entry has not been released into the public domain and is currently only available through a subscription to the
			<a href="http://raamdev.com/about/journal/">Journal</a> or a
			<a href="/about/journal/#one_time_donation">one-time donation</a>.</p>
		<?php if ( is_user_logged_in() ) {
			?>
			<p>Since you're already logged in, you can
				<a href="/account/modification/">upgrade now</a> to receive access to this entry.</p>
		<?php
		}
		else {
			?>
			<p>If you have an active subscription to the Journal, please
				<a href="https://raamdev.com/wordpress/wp-login.php">login</a> to access this entry (you may need to
				<a href="https://raamdev.com/wordpress/wp-login.php?action=lostpassword">reset your password</a> first).
			</p>
			<p><a href="/subscriptions/">
					<button>View Subscription Options</button>
				</a></p>
		<?php } ?>
	</div>
<?php
}

/**
 * Returns message about journal not released yet
 */
function raamdev_journal_not_released_comments_message() {
	?>
	<div id="journal-notice-comments">
		<p><strong>Comments are hidden.</strong></p>
	</div>
<?php
}

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
 */
function raamdev_rss_filter_post_formats( &$wp_query ) {
	if ( $wp_query->is_feed() ) {
		if ( isset( $wp_query->query_vars['rss-post-format-aside'] ) ) { // Only return Asides
			$post_format_tax_query = array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => 'post-format-aside',
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
				'terms'    => array( 'post-format-aside', 'post-format-image' ),
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

/**
 * Filter journal entries from RSS feeds, except when using secret URL
 */
add_action( 'pre_get_posts', 'raamdev_rss_filter_journal' );
function raamdev_rss_filter_journal( &$wp_query ) {

	if ( $wp_query->is_feed() && ! isset( $wp_query->query_vars[RSS_JOURNAL_KEY] ) ) {
		$wp_query->set( 'category__not_in', '921' );
	}
	else if ( $wp_query->is_feed() && isset( $wp_query->query_vars[RSS_JOURNAL_KEY] ) ) {
		$wp_query->set( 'category__in', '921' );
	}
}

/**
 * Query vars used for filtering RSS feeds
 */
function raamdev_add_query_vars( $aVars ) {
	$aVars[] = "rss-post-format-aside";
	$aVars[] = "rss-post-format-image";
	$aVars[] = "rss-post-format-standard";
	$aVars[] = RSS_JOURNAL_KEY;
	return $aVars;
}

add_filter( 'query_vars', 'raamdev_add_query_vars' );

/**
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

/**
 * Redirect the registration form to a specific page after submission
 */
function __my_registration_redirect() {
	return home_url( '/please-confirm-subscription/' );
}

add_filter( 'registration_redirect', '__my_registration_redirect' );

/**
 * Add custom styles for login form (brings entire form up to accommodate for custom header logo)
 */
function raamdev_my_login_css() {
	echo '<style type="text/css">#login { padding: 15px 0 0; margin: auto; } .login h1 a { padding-bottom: 0px; }</style>';
}

add_action( 'login_head', 'raamdev_my_login_css' );

/**
 * Allow Custom MIME Types to be uploaded via WordPress Media Library
 */
function raamdev_custom_mime_media_types( $mimes ) {
	$mimes = array_merge( $mimes, array(
		'epub|mobi' => 'application/octet-stream'
	) );
	return $mimes;
}

add_filter( 'upload_mimes', 'raamdev_custom_mime_media_types' );

/**
 * Shortcode for including Static HTML Files in posts
 */
function raamdev_sc_static_html( $atts ) {

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

/**
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

/**
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