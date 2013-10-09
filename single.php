<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Independent Publisher
 * @since   Independent Publisher 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php independent_publisher_posted_author_card(); ?>

				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( array(700, 700) );?>
				<?php endif; ?>

				<?php get_template_part( 'content', 'single' ); ?>

				<?php
				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || '0' != get_comments_number() )
					comments_template( '', true );
				?>

				<?php if ( is_single() && function_exists( 'wp_related_posts' ) ) : ?>
					<div id="further-reading">
						<?php do_action( 'erp-show-related-posts', array( 'title' => 'Further Reading', 'num_to_display' => 5, 'no_rp_text' => 'No Related Posts Found' ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( get_the_tag_list() ) : ?>
					<div id="taglist">
						<?php echo get_the_tag_list( '<ul class="taglist"><li class="taglist-title">Related Content by Tag</li><li>', '</li><li>', '</li></ul>' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( in_category( 'journal' ) && ! current_user_can( "access_s2member_level1" ) && raamdev_is_journal_viewable() ) : ?>
					<?php if ( function_exists( 'indiepub_subscribe_form_custom' ) ) : ?>
						<?php indiepub_subscribe_form_custom( '<strong>Subscribe</strong> to receive new Journal entries', '<p><a href="/subscriptions/"><button>View Subscription Options</button></a></p>' ); ?>
					<?php endif; ?>
				<?php elseif ( ! in_category( 'journal' ) ) : ?>
					<?php if ( function_exists( 'indiepub_subscribe_form_mailchimp' ) ) : ?>
						<div id="subscribe-form">
							<?php indiepub_subscribe_form_mailchimp(); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

			<?php endwhile; // end of the loop. ?>

		</div>
		<!-- #content .site-content -->
	</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>