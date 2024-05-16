<?php
/**
 * LimeSoda Job Detail Page
 * Detail Page of CPT "job"
 *
 * @package IWGPlating
 * @author LimeSoda
 * @copyright Copyright (c) 2020, LimeSoda
 * @link https://limesoda.com/
 */

namespace Limesoda\Astra_Child\Job\Detailpage;

use const Limesoda\Astra_Child\THEME_URI;
use const Limesoda\Astra_Child\THEME_DIR;

/** Enqueue Job Detail Page Styles and Scripts */
function enqueue_job_assets() {
	if (!is_singular('jobs')) {
		return;
	}

	wp_enqueue_style(
		'job-detailpage-styles',
		THEME_URI . 'assets/css/job-detailpage.css',
		[],
		filemtime(THEME_DIR . 'assets/css/job-detailpage.css'),
	);

	wp_enqueue_style(
		'social-sharing-styles',
		THEME_URI . 'assets/css/social-sharing.css',
		[],
		filemtime(THEME_DIR . 'assets/css/social-sharing.css'),
	);

	$asset_file = include THEME_DIR . '/index.asset.php';
	wp_register_script(
		'social-sharing-scripts',
		THEME_URI . 'assets/js/social-sharing.js',
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);
	wp_enqueue_script(
		'social-sharing-scripts'
	);

}
add_action('wp_enqueue_scripts', '\Limesoda\Astra_Child\Job\Detailpage\enqueue_job_assets');

/** Breadcrumbs */
add_action('astra_primary_content_top', function() {
	if (is_singular('jobs')) {
		echo do_shortcode('[astra_breadcrumb]');
	}
});

/**
 * Add "karriere" to breadcrumb of job detail pages
 */
add_filter('astra_breadcrumb_trail_items', function($breadcrumbs) {
	if (is_singular('jobs')) {
		$breadcrumbs[1] = '<a href="' . get_site_url() . '/karriere/">Karriere</a>';
		$breadcrumbs[2] = get_the_title();
	}
	return $breadcrumbs;
});

/**
 * Disable Post Title
 *
 * @return void
 */
function disable_post_title() {
	$post_types = ['jobs'];

	// bail early if the current post type if not the one we want to customize.
	if (!in_array(get_post_type(), $post_types, true)) {
		return;
	}

	// Disable title.
	add_filter('astra_the_title_enabled', '__return_false');
}
add_action('wp', '\Limesoda\Astra_Child\Job\Detailpage\disable_post_title');

/** Create Job Detail Page */
add_action('astra_entry_content_before', function () {
	if (is_singular('jobs')) {
		$current_id = get_the_ID();
		$job_cat = wp_get_post_terms($current_id, 'job-categories');
		$destination = get_field('job_destination');
		$employment = get_field('job_employment_type');
		$description = get_field('job_description');
		$email = get_field('e-mail');
		$emails = !empty($email) ? $email : 'office@iwgplating.com';
		$downloads = get_field('downloads');
		?>
		<section class="ls-jobdetail">
			<div class="ls-jobdetail_main">
				<div class="ls-jobdetail_header">
					<div class="ls-jobdetail_header__category">
						<?php if ($job_cat && count($job_cat) > 0 ) :
							foreach ($job_cat as $cat) : ?>
								<p class="is-style-label-medium"><?php echo $cat->name; ?></p>
							<?php endforeach;
						endif; ?>
					</div>

					<div class="ls-jobdetail_header__heading">
						<h1 class="ls-jobdetail_header__headline"><?php echo get_the_title(); ?></h1>
						<?php foreach ($job_cat as $cat) :
							if (isset($cat->slug)) {
								echo '<div class="ls-jobdetail__category-icon">';
								if ($cat->slug === 'chemicals') {
									echo '<i class="icon-chemicals"></i>';
								}
								if ($cat->slug === 'plating-service') {
									echo '<i class="icon-lohngalvanik"></i>';
								}
								if ($cat->slug === 'lab-solutions') {
									echo '<i class="icon-lab"></i>';
								}
								echo '</div>';
							}
						endforeach; ?>
					</div>

					<?php if (isset($destination) || ($employment)) : ?>
						<div class="ls-jobdetail_header__info">
							<p class="ls-jobdetail__destination"><?php echo $destination; ?></p> | <p class="ls-jobdetail__employment"><?php echo $employment; ?></p>
						</div>
					<?php endif; ?>
				</div>

				<div class="ls-jobdetail_content">
					<?php echo $description; ?>
				</div>
			</div>

			<div class="ls-jobdetail_sidebar">
				<?php
				if (function_exists('pll_current_language') && function_exists('pll_home_url')) {
					$current_language = pll_current_language();
					if ($current_language) {
						echo '<a class="ls-jobdetail__apply_btn" href="' . pll_home_url($current_language);
						if ($current_language === 'de') {
							echo 'karriere/#jetzt-bewerben">';
						} else {
							echo 'career/#apply-now">';
						}
						echo __('Apply now', 'iwgplating') . '</a>';
					}
				}
				?>
				<div class="ls-social_share">
					<p class="is-style-label-medium"><?php echo __('Share it', 'iwgplating'); ?></p>
					<div class="wp-block-button"><a id="ls-mail-share-button" class="wp-icons icon-contact"></a></div>
					<div class="wp-block-button"><a id="ls-facebook-share-button" class="wp-icons icon-fb"></a></div>
					<div class="wp-block-button"><a id="ls-linkedin-share-button" class="wp-icons icon-linkedin"></a></div>
				</div>

				<?php if ($downloads && count($downloads) > 0 ) : ?>
				<div class="ls-jobdetail__downloads">
					<hr>
					<p class="is-style-label-medium"><?php echo __('Downloads', 'iwgplating'); ?></p>
					<ul>
						<?php foreach ($downloads as $download) : ?>
							<li class="ls-productdetail__downloads_item">
								<a href="<?php echo $download['file_upload']['url']; ?>" target="_blank">
									<?php echo $download['file_upload']['filename']; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
});
