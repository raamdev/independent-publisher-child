<?php
/*
YARPP Template: Simple List
Description: This template returns posts in an unordered list.
Author: YARPP Team
*/
?>

<h3>Related Posts</h3>
<?php if ( have_posts() ) : ?>
<ul>
        <?php
        while ( have_posts() ) :
                the_post();
                ?>
        <li><a href="<?php the_permalink(); ?>" rel="bookmark norewrite" title="<?php the_title_attribute(); ?>" ><?php the_title(); ?></a> <small>(<?php echo get_the_date('Y'); ?>)</small><!-- (<?php the_score(); ?>)--></li>
        <?php endwhile; ?>
</ul>
<?php else : ?>
<p>No related posts.</p>
<?php endif; ?>