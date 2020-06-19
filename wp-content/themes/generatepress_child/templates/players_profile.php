<?php
/*
 * Template Name: Kalendarz startów
 * Template Post Type: person
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

<?php if ( have_rows( 'olimpic_information' ) ) : ?>
	<?php while ( have_rows( 'olimpic_information' ) ) :
		the_row(); ?>
		
		<?php
		$posts = get_sub_field( 'olimpic' );
		if ( $posts ) : ?>
			<?php foreach( $posts as $post) : ?>
				<?php setup_postdata( $post ); ?>
				
			<?php endforeach; ?>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>

			<?php if ( have_rows( 'competitions_rewards' ) ) : ?>
				<?php while ( have_rows( 'competitions_rewards' ) ) :
					the_row(); ?>
					
					<?php
					$posts = get_sub_field( 'competition' );
					if ( $posts ) : ?>
						<?php foreach( $posts as $post) : ?>
							<?php setup_postdata( $post ); ?>
							
						<?php endforeach; ?>
						<?php wp_reset_postdata(); ?>
					<?php endif; ?>

					<?php if ( get_sub_field( 'reward' ) == 1 ) : ?>
					
					<?php endif; ?>

				<?php endwhile; ?>
			<?php endif; ?>

	<?php endwhile; ?>
<?php endif; ?>


<?php
$picture = get_field( 'picture' );
if ( $picture ) : ?>
	<img src="<?php echo esc_url( $picture['url'] ); ?>" alt="<?php echo esc_attr( $picture['alt'] ); ?>" />
<?php endif; ?>

<?php if ( $firstname = get_field( 'firstname' ) ) : ?>
	<?php echo esc_html( $firstname ); ?>
<?php endif; ?>

<?php if ( $lastname = get_field( 'lastname' ) ) : ?>
	<?php echo esc_html( $lastname ); ?>
<?php endif; ?>

<?php if ( $birthdate_date = get_field( 'birthdate_date' ) ) : ?>
	<?php echo esc_html( $birthdate_date ); ?>
<?php endif; ?>

<?php if ( $birthdate = get_field( 'birthdate' ) ) : ?>
	<?php echo esc_html( $birthdate ); ?>
<?php endif; ?>

<?php if ( $deathdate_date = get_field( 'deathdate_date' ) ) : ?>
	<?php echo esc_html( $deathdate_date ); ?>
<?php endif; ?>

<?php if ( $deathdate = get_field( 'deathdate' ) ) : ?>
	<?php echo esc_html( $deathdate ); ?>
<?php endif; ?>

<?php if ( $placeofbirth = get_field( 'placeofbirth' ) ) : ?>
	<?php echo esc_html( $placeofbirth ); ?>
<?php endif; ?>

<?php if ( $club = get_field( 'club' ) ) : ?>
	<?php echo esc_html( $club ); ?>
<?php endif; ?>

<?php if ( $coach = get_field( 'coach' ) ) : ?>
	<?php echo esc_html( $coach ); ?>
<?php endif; ?>

<?php if ( $metrics = get_field( 'metrics' ) ) : ?>
	<?php echo esc_html( $metrics ); ?>
<?php endif; ?>

<?php if ( $seolink = get_field( 'seolink' ) ) : ?>
	<?php echo esc_html( $seolink ); ?>
<?php endif; ?>

<?php if ( $tmp_old_id = get_field( 'tmp_old_id' ) ) : ?>
	<?php echo esc_html( $tmp_old_id ); ?>
<?php endif; ?>

<?php if ( $tmp_old_id = get_field( 'tmp_old_id' ) ) : ?>
	<?php echo esc_html( $tmp_old_id ); ?>
<?php endif; ?>

<?php if ( $tmp_old_fid = get_field( 'tmp_old_fid' ) ) : ?>
	<?php echo esc_html( $tmp_old_fid ); ?>
<?php endif; ?>

<?php if ( $tmp_old_parent_id = get_field( 'tmp_old_parent_id' ) ) : ?>
	<?php echo esc_html( $tmp_old_parent_id ); ?>
<?php endif; ?>

<?php if ( $createdate = get_field( 'createdate' ) ) : ?>
	<?php echo esc_html( $createdate ); ?>
<?php endif; ?>

<?php if ( $tmp_old_title = get_field( 'tmp_old_title' ) ) : ?>
	<?php echo esc_html( $tmp_old_title ); ?>
<?php endif; ?>

<?php if ( $tmp_old_lead = get_field( 'tmp_old_lead' ) ) : ?>
	<?php echo esc_html( $tmp_old_lead ); ?>
<?php endif; ?>

<?php if ( $tmp_old_body = get_field( 'tmp_old_body' ) ) : ?>
	<?php echo esc_html( $tmp_old_body ); ?>
<?php endif; ?>

<?php
$tmp_old_body_img = get_field( 'tmp_old_body_img' );
if ( $tmp_old_body_img ) : ?>
	<img src="<?php echo esc_url( $tmp_old_body_img['url'] ); ?>" alt="<?php echo esc_attr( $tmp_old_body_img['alt'] ); ?>" />
<?php endif; ?>

<?php
$tmp_old_lead_img = get_field( 'tmp_old_lead_img' );
if ( $tmp_old_lead_img ) : ?>
	<img src="<?php echo esc_url( $tmp_old_lead_img['url'] ); ?>" alt="<?php echo esc_attr( $tmp_old_lead_img['alt'] ); ?>" />
<?php endif; ?>

<?php if ( $important = get_field( 'important' ) ) : ?>
	<?php echo esc_html( $important ); ?>
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