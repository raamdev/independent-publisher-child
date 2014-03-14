<?php
/**
 * Parent Theme Functions Overridden by this Child Theme
 *
 */

/**
 * Returns the excerpt with the excerpt linked to the permalink, for display on non-Single pages
 */
function independent_publisher_maybe_linkify_the_excerpt( $content ) {
	if ( ! is_single() ) {
		if( in_category('Journal') && ! raamdev_is_journal_viewable() ) {
			$content = raamdev_journal_not_released_message();
		}
		else {
			$content = '<a href="' . get_permalink() . '" rel="bookmark" title="' . esc_attr( sprintf( __( 'Permalink to %s', 'independent_publisher' ), the_title_attribute( 'echo=0' ) ) ) . '">' . $content . '</a>';
		}
	}

	return $content;
}

/**
 * Prints HTML with Continue Reading link
 *
 * @since Independent Publisher 1.0
 */
function independent_publisher_continue_reading_link() {
	$text = apply_filters( 'independent_publisher_continue_reading_link_text', ' ' . __( 'Continue Reading &rarr;', 'independent_publisher' ) );

	if( in_category('Journal') && ! raamdev_is_journal_viewable() ) {
		return;
	}
	else {
		printf(
			'<div class="enhanced-excerpt-read-more"><a class="read-more" href="%1$s">%2$s</a></div>',
			esc_url( get_permalink() ),
			esc_html( $text )
		);
	}
}

/**
 * Determines if the comments and comment form should be hidden altogether.
 * This differs from disabling the comments by also hiding the
 * "Comments are closed." message and allows for easily overriding this
 * function in a Child Theme.
 */
function independent_publisher_hide_comments() {
	if( in_category('Journal') && ! raamdev_is_journal_viewable() ) {
		return true;
	}
	else {
		return false;
	}
}