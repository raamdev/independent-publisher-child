<?php
/**
 * @package Independent Publisher
 * @since   Independent Publisher 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( array( 700, 700 ) ); ?>
	<?php endif; ?>
	<header class="entry-header">
		<h2 class="entry-title-meta">
			<span class="entry-title-meta-author"><?php independent_publisher_posted_author() ?></span> in <?php echo independent_publisher_post_categories( '', TRUE ); ?>
			<?php if ( function_exists( 'indiepub_spoken_essay_link' ) ) : ?>
				<?php if ( indiepub_spoken_essay_url() ) : ?>
					| <?php echo indiepub_spoken_essay_link(); ?>
				<?php endif; ?>
			<?php endif; ?>
		</h2>

		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header>
	<!-- .entry-header -->

	<div class="entry-content">
		<?php if ( in_category( 'journal' ) ) : ?>
			<?php if ( raamdev_is_journal_viewable() ) : // Only show content if Journal is viewable ?>

				<?php raamdev_was_journal_entry_message(); ?>
				<?php the_content(); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'independent_publisher' ), 'after' => '</div>' ) ); ?>

			<?php else : // Show message about why Journal is not viewable ?>

				<?php raamdev_journal_not_released_message(); ?>

			<?php endif; ?>
		<?php else: // Not a journal entry ?>

			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'independent_publisher' ), 'after' => '</div>' ) ); ?>

		<?php endif; ?>

	</div>
	<!-- .entry-content -->

	<?php independent_publisher_posted_author_bottom_card() ?>

	<footer class="entry-meta">

		<?php if ( ! in_category( 'journal' ) || raamdev_is_journal_viewable() ) : ?>
			<?php if ( function_exists( 'indiepub_sharing_buttons' ) ) : ?>
				<?php indiepub_sharing_buttons(); ?>
			<?php endif; ?>

			<?php if ( comments_open() ) : ?>
				<div id="share-comment-button">
					<button><i class="icon-comment"></i>Share a comment</button>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php edit_post_link( __( 'Edit', 'independent_publisher' ), '<span class="edit-link">', '</span>' ); ?>
	</footer>
	<!-- .entry-meta -->

</article><!-- #post-<?php the_ID(); ?> -->
