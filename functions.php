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
		<?php echo '#post-' . $post_id; ?>
		{
			opacity: 0.5
		;
		}
	</style>
	<div id="journal-notice">
		<blockquote>
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
					<a href="https://raamdev.com/wordpress/wp-login.php?action=lostpassword">reset your password</a> first).</p>
			<?php } ?>
		</blockquote>
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
