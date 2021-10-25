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

//add_filter( 'wp_nav_menu_items', 'raamdev_logged_in_menu_items', 10, 2 );


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
		$url = apply_filters( 'jetpack_photon_url', 'https://secure.gravatar.com/avatar/a058cfca65a5103e838b1d0ea077ca4b?s=400&r=g' );

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

		$raamdev_home_img = 'https://secure.gravatar.com/avatar/a058cfca65a5103e838b1d0ea077ca4b?s=400&r=g';
		$tags['og:image'] = esc_url( $raamdev_home_img );
	}
	return $tags;
}
add_filter( 'jetpack_open_graph_tags', 'raamdev_home_image' );

/*
 * Use custom twitter metadata instead of Jetpack defaults
 */
function tweakjp_custom_twitter_metadata( $og_tags ) {
	$og_tags['twitter:site'] = '@raamdev';
	$og_tags['twitter:card'] = 'summary';
	return $og_tags;
}
add_filter( 'jetpack_open_graph_tags', 'tweakjp_custom_twitter_metadata', 11 );

/*
 * Use a custom image for og:image on home page
 */
function raamdev_category_prefix() {
    if ( is_single() && !in_category( 'journal' ) ) {
        return 'on'; // Change category prefix to 'on' instead of 'in' on Single Posts, e.g., "Raam Dev on Writing & Publishing"
    } else {
        return 'in';
    }
}
add_filter('independent_publisher_entry_meta_category_prefix', 'raamdev_category_prefix');

/*
 * Override this function to add a class to the anchor so that we can adjust the style in style.css
 */
function independent_publisher_posted_on_date() {
    printf(
        '<a href="%1$s" title="%2$s" rel="bookmark" class="entry-title-meta-post-date-permalink"><time class="entry-date dt-published" datetime="%3$s" itemprop="datePublished" pubdate="pubdate">%4$s</time></a>',
        esc_url( get_permalink() ),
        esc_attr( get_the_title() ),
        esc_attr( get_the_date( DATE_ISO8601 ) ),
        esc_html( get_the_date() )
    );
}

/*
 * Add permalink and social sharing buttons to bottom of post content
 */
add_filter( 'the_content', 'independent_publisher_child_social_buttons', 1 );
 
function independent_publisher_child_social_buttons( $content ) {
 
    // Check if we're inside the main loop in a single Post.
    if ( is_singular() && is_single() && in_the_loop() && is_main_query() ) {
		$social_html = '<div style="margin-top: 50px;"><ul class="social-icons">';

		$social_html .= '<li><a target="_new" href="http://www.facebook.com/sharer/sharer.php?u=' . get_permalink() .'&amp;title='. get_the_title() .'"><div class="svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="-1416 1523 12.579 12.579"> <g id="socialshare_facebook" transform="translate(-2156 934.881)"> <path id="socialshare_facebook-2" d="M11.887,0H.692A.692.692,0,0,0,0,.692v11.2a.692.692,0,0,0,.692.692H6.719V7.71H5.079v-1.9h1.64v-1.4A2.288,2.288,0,0,1,9.162,1.9a12.316,12.316,0,0,1,1.462.079V3.669H9.617c-.786,0-.938.377-.938.928V5.807h1.876l-.241,1.9H8.679v4.869h3.2a.692.692,0,0,0,.7-.692V.692A.692.692,0,0,0,11.887,0" transform="translate(740 588.119)"></path> </g> </svg></div> </a></li>';
		
		$social_html .= '<li> <a target="_new" href="http://twitter.com/intent/tweet?text='. get_the_title() .'%20via%20@raamdev+' . get_permalink() .'"><div class="svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="-1215 1632 12.579 12.579"> <g id="socialshare_twitter" transform="translate(-1980.723 1043.881)"> <path id="socialshare_twitter-2" d="M11.646,0H.928A.917.917,0,0,0,0,.907V11.672a.917.917,0,0,0,.928.907H11.646a.917.917,0,0,0,.933-.907V.907A.917.917,0,0,0,11.646,0ZM10.561,4.114v.278a6.221,6.221,0,0,1-6.289,6.263A6.289,6.289,0,0,1,.891,9.665a4.418,4.418,0,0,0,3.3-.912,2.207,2.207,0,0,1-2.1-1.53,2.1,2.1,0,0,0,.991-.037A2.2,2.2,0,0,1,1.336,5.026V5a2.191,2.191,0,0,0,1,.273,2.2,2.2,0,0,1-.681-2.94,6.242,6.242,0,0,0,4.539,2.3A2.28,2.28,0,0,1,6.132,4.1,2.207,2.207,0,0,1,8.339,1.9a2.18,2.18,0,0,1,1.62.718,4.549,4.549,0,0,0,1.4-.524,2.238,2.238,0,0,1-.98,1.216,4.471,4.471,0,0,0,1.263-.351h0a4.476,4.476,0,0,1-1.08,1.153Z" transform="translate(765.723 588.119)"></path> </g> </svg></div> </a></li>';
		
		$social_html .= '</ul><div class="social-permalink"><p><a title="Permalink for '. get_the_title() .' " href="' . get_permalink() .'">' . get_permalink() .'</a></p></div></div>';
		
		return $content . $social_html;
    }
 
    return $content;
}

function independent_publisher_comment( $comment, $args, $depth ) {
	$GLOBALS['comment']    = $comment;
	$comment_content_class = ''; // Used to style the comment-content differently when comment is awaiting moderation
	if( get_comment_meta( $comment->comment_ID, 'protocol', true ) == 'webmention' ) {
		return; // Don't show comments that are really webmentions
	}
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
	<article id="comment-<?php comment_ID(); ?>" class="comment">
		<footer>
			<div class="comment-author vcard">
				<?php echo get_avatar( $comment, 48 ); ?>
				<?php printf( '<cite class="fn">%s</cite>', get_comment_author_link() ); ?>
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<?php $comment_content_class = "unapproved"; ?>
					<em><?php _e( ' - Your comment is awaiting moderation.', 'independent-publisher' ); ?></em>
				<?php endif; ?>
			</div>
			<!-- .comment-author .vcard -->
			<div class="comment-meta commentmetadata">
				<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
					<time pubdate datetime="<?php comment_time( 'c' ); ?>">
						<?php
						/* translators: 1: date */
						printf( '%1$s', get_comment_date() ); ?>
					</time>
				</a>
				<?php edit_comment_link( __( '(Edit)', 'independent-publisher' ), ' ' );
				?>
			</div>
			<!-- .comment-meta .commentmetadata -->
		</footer>

		<div class="comment-content <?php echo $comment_content_class; ?>"><?php comment_text(); ?></div>

		<div class="reply">
			<?php comment_reply_link(
				array_merge(
					$args, array(
						'depth'     => $depth,
						'max_depth' => $args['max_depth']
					)
				)
			); ?>
		</div>
		<!-- .reply -->
	</article><!-- #comment-## -->
	<?php
}

/**
 * @since 2021-01-03
 * Tweaked to show separator when there are webmentions
 */
function independent_publisher_get_post_word_count() {
        if ( !post_password_required() && ( ( comments_open() && !independent_publisher_hide_comments() ) || independent_publisher_comment_count_mentions() > 0 ) ) {
                $separator = ' <span class="sep"> ' . apply_filters( 'independent_publisher_entry_meta_separator', '|' ) . ' </span>';
        } else {
                $separator = '';
        }

        return sprintf( '<span>' . __( '%1$s Words', 'independent-publisher' ) . '</span>%2$s', independent_publisher_post_word_count(), $separator );
}

/**
 * @since 2021-01-03
 * Hide categories (focusing on tags instead)
 */
function independent_publisher_posted_author_cats() {
        return;
}

/** 
* @since 2021-10-25 
* Fix issue where post meta separator was missing after Published date when comments and word count were disabled, but webmentions were enabled. 
*/
function independent_publisher_get_post_date() {
        if ( ( comments_open() && !independent_publisher_hide_comments() ) || ( independent_publisher_show_post_word_count() && !get_post_format() ) || ( !post_password_required() && pings_open() && independent_publisher_comment_count_mentions() ) ) {
                $separator = ' <span class="sep"> ' . apply_filters( 'independent_publisher_entry_meta_separator', '|' ) . ' </span>';
        } else {
                $separator = '';
        }

        return independent_publisher_posted_on_date() . $separator;
}