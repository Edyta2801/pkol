<?php
/*
 * Template Name: Kalendarz startów
 * Template Post Type: olympics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<div id="primary" <?php generate_do_element_classes( 'content' ); ?>>
	<main id="main" <?php generate_do_element_classes( 'main' ); ?>>
		<?php
			/**
			 * generate_before_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_before_main_content' );

			while ( have_posts() ) : the_post();

				get_template_part( 'content', 'single' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || '0' != get_comments_number() ) :
					/**
					 * generate_before_comments_container hook.
					 *
					 * @since 2.1
					 */
					do_action( 'generate_before_comments_container' );
					?>

		<div class="comments-area">
			<?php comments_template(); ?>
		</div>

		<?php
				endif;

			endwhile;

?>

		<?php if( have_rows('events') ): ?>
		<ul id="status" class="harmonogram harmonogram-dnia">
		<?php while( have_rows('events') ): the_row(); ?>
			<li>

				<div class="event-start">
					<span><?php the_sub_field('event_start'); ?></span>
				</div>
				<div class="event-title">
					<a href="<?php echo get_edit_post_link(); ?>">
					<h3>
						<?php if ( $event_title = get_sub_field('event_title') ): ?>
						<?php echo $event_title; ?>
						<?php endif; ?>
					</h3>
					</a>
					<?php if ( $event_description = get_sub_field('event_description') ): ?>
					<span class="event-description">
						<?php echo $event_description; ?>
					</span>
					<?php endif; ?>

				</div>
			</li>
			<?php endwhile; ?>
		</ul>
		<?php endif; ?>





		<?php

			/**
			 * generate_after_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_after_main_content' );
			?>
	</main><!-- #main -->
</div><!-- #primary -->

<?php
	/**
	 * generate_after_primary_content_area hook.
	 *
	 * @since 2.0
	 */
	do_action( 'generate_after_primary_content_area' );

	generate_construct_sidebars();

get_footer();