<?php /* 

Template Name: Full Width Gutenberg

*/ ?>
<?php get_header(); 
if ( have_posts() ): while ( have_posts() ) : the_post();
 ?>
 <style>
	 .template-full-width-gutenberg {
		<?php if(get_option('max_width')): ?>
			--width-wide:<?php echo get_option( 'max_width' ); ?>;
		<?php endif; ?>
	}
</style>

					
<main id="main" class="container--fullwidth container--main template-full-width-gutenberg">
	<?php the_content(); ?>
</main>
<?php endwhile; endif?>
<?php get_footer(); ?>