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
 * Outputs post author info for display on bottom of single posts
 * with subscription form at bottom.
 */
function independent_publisher_posted_author_bottom_card() {
    if ( !independent_publisher_show_author_card() ) {
        return; // This option has been disabled
    }

    do_action( 'independent_publisher_before_post_author_bottom_card' );
    ?>
    <div class="post-author-bottom">
        <div class="post-author-card">
            <a class="site-logo" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
                <?php echo get_avatar( get_the_author_meta( 'ID' ), 100 ); ?>
            </a>

            <div class="post-author-info">
                <h1 class="site-title">
                    <?php independent_publisher_posted_author(); ?>
                </h1>

                <h2 class="site-description"><?php the_author_meta( 'description' ) ?></h2>
            </div>
            <div class="post-published-date">
                <h2 class="site-published"><?php _e( 'Published', 'independent-publisher' ); ?></h2>
                <h2 class="site-published-date"><?php independent_publisher_posted_on_date(); ?></h2>
                <?php /* Show last updated date if the post was modified AND
							Show Updated Date on Single Posts option is enabled AND
								'independent_publisher_hide_updated_date' Custom Field is not present on this post */ ?>
                <?php if ( get_the_modified_date() !== get_the_date() &&
                           independent_publisher_show_updated_date_on_single() &&
                           !get_post_meta( get_the_ID(), 'independent_publisher_hide_updated_date', true )
                ) : ?>
                    <h2 class="site-published"><?php _e( 'Updated', 'independent-publisher' ); ?></h2>
                    <h2 class="site-published-date"><?php independent_publisher_post_updated_date(); ?></h2>
                <?php endif; ?>

                <?php do_action( 'independent_publisher_after_post_published_date' ); ?>

            </div>
        </div>
        <div style="margin-top:50px;">
            <!-- Begin MailChimp Signup Form -->
            <link href="//cdn-images.mailchimp.com/embedcode/horizontal-slim-10_7.css" rel="stylesheet" type="text/css">
            <style type="text/css">
                #mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; width:100%;}
                /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
                   We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
            </style>
            <div id="mc_embed_signup">
                <form action="//raamdev.us1.list-manage.com/subscribe/post?u=5daf0f6609de2506882857a28&amp;id=dc1b1538af" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div style="display:none;"> <input type="hidden" name="group[1129]" value="1" id="mce-group[1129]"> </div>
                    <div style="display:none;"> <input type="hidden" name="group[1873]" value="32" id="mce-group[1873]"> </div>
                    <div style="display:none;"> <input type="hidden" name="mce-group[1989][64]" value="64" id="mce-group[1989]-1989-0"> </div>
                    <div style="display:none;"> <input type="hidden" name="MERGE3" value="<?php echo 'https://' . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; ?>" id="MERGE3"> </div>

                    <div id="mc_embed_signup_scroll">

                        <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="Enter your email address here to subscribe →" required>
                        <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                        <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_5daf0f6609de2506882857a28_dc1b1538af" tabindex="-1" value=""></div>
                        <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                    </div>
                </form>
            </div>

            <!--End mc_embed_signup-->
        </div>
    </div>
    <!-- .post-author-bottom -->
    <?php
    do_action( 'independent_publisher_after_post_author_bottom_card' );
}
