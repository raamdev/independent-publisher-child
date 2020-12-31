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
        <div style="margin-top:70px;">
            <!-- Begin MailChimp Signup Form -->
            <style type="text/css">
			/* MailChimp Form Embed Code - Horizontal Super Slim - 12/16/2015 v10.7
				Adapted from: http://blog.heyimcat.com/universal-signup-form/ */

				#mc_embed_signup form {text-align:center; padding:10px 0 10px 0;}
				.mc-field-group { display: inline-block; } /* positions input field horizontally */
				#mc_embed_signup input.name, #mc_embed_signup input.email, #mc_embed_signup select.frequency {font-family:"Open Sans","Helvetica Neue",Arial,Helvetica,Verdana,sans-serif; font-size: 15px; border: 1px solid #ABB0B2;  -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; color: #343434; background-color: #fff; box-sizing:border-box; height:32px; padding: 0px 0.4em; display: inline-block; margin: 0; vertical-align:top;}
				#mc_embed_signup input.name { width: 200px; margin-right: 10px; }
				#mc_embed_signup input.email { width: 200px; margin-right: 10px; }
				#mc_embed_signup select.frequency { width: 140px; margin-right: 10px; }
				#mc_embed_signup label {display:block; font-size:16px; padding-bottom:10px; font-weight:bold;}
				#mc_embed_signup .clear {display: inline-block;} /* positions button horizontally in line with input */
				#mc_embed_signup .button {font-size: 13px; border: none; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; letter-spacing: .03em; color: #fff; background-color: #aaa; box-sizing:border-box; height:32px; line-height:32px; padding:0 18px; display: inline-block; margin: 0; transition: all 0.23s ease-in-out 0s;}
				#mc_embed_signup .button:hover {background-color:#777; cursor:pointer;}
				#mc_embed_signup div#mce-responses {float:left; top:-1.4em; padding:0em .5em 0em .5em; overflow:hidden; width:90%;margin: 0 5%; clear: both;}
				#mc_embed_signup div.response {margin:1em 0; padding:1em .5em .5em 0; font-weight:bold; float:left; top:-1.5em; z-index:1; width:80%;}
				#mc_embed_signup #mce-error-response {display:none;}
				#mc_embed_signup #mce-success-response {color:#529214; display:none;}
				#mc_embed_signup label.error {display:block; float:none; width:auto; margin-left:1.05em; text-align:left; padding:.5em 0;}
				@media (max-width: 768px) {
					#mc_embed_signup input.name, #mc_embed_signup input.email, #mc_embed_signup select.frequency {width:100%; margin-bottom:5px; margin-right:0;}
					#mc_embed_signup .clear {display: block; width: 100% }
					#mc_embed_signup .button {width: 100%; margin:0; }
				}
                #mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; width:100%;}
            </style>
            <div id="mc_embed_signup">
                <form action="//raamdev.us1.list-manage.com/subscribe/post?u=5daf0f6609de2506882857a28&amp;id=dc1b1538af" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div style="display:none;"> <input type="hidden" name="MERGE3" value="<?php echo 'https://' . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; ?>" id="MERGE3"> </div>

                    <div id="mc_embed_signup_scroll">

					<input type="text" value="" name="FNAME" class="name" id="mce-FNAME" placeholder="Name" required="">
						<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="E-mail address" required>
						<select name="group[1129]" class="frequency" id="mce-group[1129]">
							<option value="1">1 email per day</option>
							<option value="2" selected>1 email per week</option>
							<option value="4">1 email per month</option>
						</select>
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

/*
 * Add permalink and social sharing buttons to bottom of post content
 */
add_filter( 'the_content', 'independent_publisher_child_social_buttons', 1 );
 
function independent_publisher_child_social_buttons( $content ) {
 
    // Check if we're inside the main loop in a single Post.
    if ( is_singular() && in_the_loop() && is_main_query() ) {
		$social_html = '<div style="margin-top: 50px;"><ul class="social-icons">';

		$social_html .= '<li><a href="http://www.facebook.com/sharer/sharer.php?u=' . get_permalink() .'&amp;title='. get_the_title() .'"><div class="svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="-1416 1523 12.579 12.579"> <g id="socialshare_facebook" transform="translate(-2156 934.881)"> <path id="socialshare_facebook-2" d="M11.887,0H.692A.692.692,0,0,0,0,.692v11.2a.692.692,0,0,0,.692.692H6.719V7.71H5.079v-1.9h1.64v-1.4A2.288,2.288,0,0,1,9.162,1.9a12.316,12.316,0,0,1,1.462.079V3.669H9.617c-.786,0-.938.377-.938.928V5.807h1.876l-.241,1.9H8.679v4.869h3.2a.692.692,0,0,0,.7-.692V.692A.692.692,0,0,0,11.887,0" transform="translate(740 588.119)"></path> </g> </svg></div> </a></li>';
		
		$social_html .= '<li> <a href="http://twitter.com/intent/tweet?text='. get_the_title() .'+' . get_permalink() .'"><div class="svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="-1215 1632 12.579 12.579"> <g id="socialshare_twitter" transform="translate(-1980.723 1043.881)"> <path id="socialshare_twitter-2" d="M11.646,0H.928A.917.917,0,0,0,0,.907V11.672a.917.917,0,0,0,.928.907H11.646a.917.917,0,0,0,.933-.907V.907A.917.917,0,0,0,11.646,0ZM10.561,4.114v.278a6.221,6.221,0,0,1-6.289,6.263A6.289,6.289,0,0,1,.891,9.665a4.418,4.418,0,0,0,3.3-.912,2.207,2.207,0,0,1-2.1-1.53,2.1,2.1,0,0,0,.991-.037A2.2,2.2,0,0,1,1.336,5.026V5a2.191,2.191,0,0,0,1,.273,2.2,2.2,0,0,1-.681-2.94,6.242,6.242,0,0,0,4.539,2.3A2.28,2.28,0,0,1,6.132,4.1,2.207,2.207,0,0,1,8.339,1.9a2.18,2.18,0,0,1,1.62.718,4.549,4.549,0,0,0,1.4-.524,2.238,2.238,0,0,1-.98,1.216,4.471,4.471,0,0,0,1.263-.351h0a4.476,4.476,0,0,1-1.08,1.153Z" transform="translate(765.723 588.119)"></path> </g> </svg></div> </a></li>';
		
		$social_html .= '</ul><div class="social-permalink"><p><a title="Permalink for '. get_the_title() .' " href="' . get_permalink() .'">' . get_permalink() .'</a></p></div></div>';
		
		return $content . $social_html;
    }
 
    return $content;
}