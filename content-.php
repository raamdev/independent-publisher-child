<?php
/**
 * @package Independent Publisher
 * @since   Independent Publisher 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'independent_publisher' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h1>
	</header>
	<!-- .entry-header -->

	<?php if ( is_search() ) : // Only display Excerpts for Search ?>
		<div class="entry-summary">
			<?php if ( in_category( 'journal' ) ) : ?>

				<?php if ( raamdev_is_journal_viewable() ) : // Only show excerpts if Journal viewable ?>

					<?php raamdev_was_journal_entry_message(); ?>
					<?php the_excerpt(); ?>

				<?php else : // Show message describing why journal is not viewable ?>

					<?php raamdev_journal_not_released_message(); ?>

				<?php endif; // is_raamdev_journal_viewable() ?>

			<?php else : // Not a journal entry ?>

				<?php if ( 'aside' === get_post_format() ) : // Do something special for Asides ?>

					<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_content(); ?></a>

				<?php else: ?>

					<?php the_excerpt(); ?>

				<?php endif; // if ('aside' === get_post_format() ?>

			<?php endif; // in_category('journal')?>

		</div><!-- .entry-summary -->
	<?php else : ?>
		<div class="entry-content">
			<?php if ( in_category( 'journal' ) ) : // Journal entry ?>
				<?php if ( raamdev_is_journal_viewable() ) : // Only show content if Journal is viewable ?>

					<?php raamdev_was_journal_entry_message(); ?>
					<?php the_excerpt(); ?>

				<?php else : // Show message about why Journal is not viewable ?>

					<?php raamdev_journal_not_released_message(); ?>

				<?php endif; // is_raamdev_journal_viewable() ?>

			<?php else : // not journal entry ?>

				<?php if ( 'aside' === get_post_format() ) : // Do something special for Asides ?>

					<?php // This creates the same output as the_content() ?>
					<?php $content = get_the_content(); ?>
					<?php $content = apply_filters( 'the_content', $content ); ?>
					<?php $content = str_replace( ']]>', ']]&gt;', $content ); ?>

					<?php // Asides might have footnotes, which don't display properly when linking Asides to themselves, so we strip <sup> here ?>
					<?php $content = preg_replace( '!<sup\s+id="fnref.*?">.*?</sup>!is', '', $content ); ?>

					<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php echo $content; ?></a>

				<?php else : // not an Aside ?>

					<?php the_excerpt(); ?>


				<?php endif; // if ( 'aside' === get_post_format() ) ?>

			<?php endif; // in_category('journal') ?>
		</div><!-- .entry-content -->
	<?php endif; ?>

	<footer class="entry-meta">
		<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
			<?php independent_publisher_posted_author_cats() ?>
		<?php endif; // End if 'post' == get_post_type() ?>

		<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
			<span class="sep"> | </span>
			<?php echo independent_publisher_post_word_count() . ' Words '; ?>
			<span class="sep"> | </span>
			<span class="comments-link"><?php comments_popup_link( __( 'Comment', 'independent_publisher' ), __( '1 Comment', 'independent_publisher' ), __( '% Comments', 'independent_publisher' ) ); ?></span>
			<?php if ( 'aside' !== get_post_format() ) : // Do something special for Asides ?>
				<p style="text-align: right;"><a href="<?php the_permalink(); ?>"><button><?php echo independent_publisher_post_word_count() . ' Words '; ?> →</button></a></p>
			<?php endif; ?>
		<?php endif; ?>

		<?php edit_post_link( __( 'Edit', 'independent_publisher' ), '<span class="sep"> | </span><span class="edit-link">', '</span>' ); ?>
	</footer>
	<!-- .entry-meta -->
</article><!-- #post-<?php the_ID(); ?> -->
