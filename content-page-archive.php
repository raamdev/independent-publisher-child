<?php
/**
 * The template used for displaying Archive page content in page-archives.php
 * Template Name: Archives
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<?php the_content(); ?>

						<?php get_search_form(); ?>

						<?php if ( function_exists('wp_tag_cloud') ) : ?>
							<hr>
							<div class="commonly-used-tags">
							<h2>Explore by Tag</h2>
							<?php wp_tag_cloud('smallest=10&largest=22'); ?>
							</div>
						<?php endif; ?>

						<?php if ( class_exists('SnazzyArchives') ) : ?>
							<hr>
							<h2>The Full Archives</h2>
							<p>Click on the month headers to see all posts for that month.</p>
							<?php echo do_shortcode("[snazzy-archive]"); ?>
						<?php endif; ?>

						<h2>Category Archives</h2>
						<ul>
							<?php wp_list_categories('title_li='); ?>
						</ul>

						<h2>Yearly Archives</h2>
						<select name="archive-dropdown" onchange="document.location.href=this.options[this.selectedIndex].value;">
							<option value=""><?php echo esc_attr( __( 'Select Year' ) ); ?></option>
							<?php wp_get_archives( array( 'type' => 'yearly', 'format' => 'option' ) ); ?>
						</select>

						<h2>Monthly Archives</h2>
						<select name="archive-dropdown" onchange="document.location.href=this.options[this.selectedIndex].value;">
							<option value=""><?php echo esc_attr( __( 'Select Month' ) ); ?></option>
							<?php wp_get_archives( array( 'type' => 'monthly', 'format' => 'option' ) ); ?>
						</select>

						<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'publish' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->

					<?php edit_post_link( __( 'Edit', 'publish' ), '<footer class="entry-meta"><span class="edit-link">', '</span></footer>' ); ?>
				</article><!-- #post-<?php the_ID(); ?> -->


			<?php endwhile; // end of the loop. ?>

		</div>
		<!-- #content .site-content -->
	</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>