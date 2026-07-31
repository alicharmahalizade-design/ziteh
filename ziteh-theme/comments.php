<?php
/**
 * Comments and the reply form.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Never expose comments on a post whose password has not been entered.
if ( post_password_required() ) {
	return;
}
?>
<section class="ziteh-comments" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="ziteh-comments__title">
			<?php
			$ziteh_count = get_comments_number();
			printf(
				esc_html(
					/* translators: %s: comment count */
					_n( '%s دیدگاه', '%s دیدگاه', $ziteh_count, 'ziteh-theme' )
				),
				esc_html( number_format_i18n( $ziteh_count ) )
			);
			?>
		</h2>

		<ol class="ziteh-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 52,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'قبلی', 'ziteh-theme' ),
				'next_text' => esc_html__( 'بعدی', 'ziteh-theme' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="ziteh-comments__closed"><?php esc_html_e( 'امکان ثبت دیدگاه بسته شده است.', 'ziteh-theme' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => esc_html__( 'دیدگاه شما', 'ziteh-theme' ),
			'label_submit'       => esc_html__( 'ثبت دیدگاه', 'ziteh-theme' ),
			'class_form'         => 'ziteh-comment-form',
			'class_submit'       => 'ziteh-comment-form__submit',
			'comment_field'      => sprintf(
				'<p class="comment-form-comment"><label for="comment">%1$s</label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
				esc_html__( 'متن دیدگاه', 'ziteh-theme' )
			),
		)
	);
	?>
</section>
