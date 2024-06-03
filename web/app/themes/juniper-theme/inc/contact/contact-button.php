<?php
/**
 * LimeSoda Contact Button
 *
 * @package IWGPlating
 * @author LimeSoda
 * @copyright Copyright (c) 2020, LimeSoda
 * @link https://limesoda.com/
 */

namespace Contact\WPS_Contact_Button;

/** Enqueue Job Detail Page Styles and Scripts */
function enqueue_contact_assets() {

    if (!defined('THEME_DIR')) {
        define('THEME_DIR', get_template_directory() . '/');
    }

    if (!defined('THEME_URI')) {
        define('THEME_URI', get_template_directory_uri() . '/');
    }

	wp_enqueue_style(
		'contact-button-styles',
		THEME_URI . 'assets/css/contact-button.css',
		[],
		filemtime(THEME_DIR . 'assets/css/contact-button.css'),
	);
}
add_action('wp_enqueue_scripts', '\Contact\WPS_Contact_Button\enqueue_contact_assets');

add_action('astra_body_bottom', function() {
	$url_de = get_the_permalink(get_page_by_title('Kontakt')) . '/#fragen-musterbestellung';
	$url_en = get_the_permalink(get_page_by_title('Contact')) . '/#contact-form';
	$current_lang = pll_current_language();
	?>
	<div class="ls-contact-button">
		<?php if ($current_lang === 'en') { ?>
		<a href="<?php echo $url_en; ?>"><i class="icon-message"></i></a>
		<?php } else { ?>
		<a href="<?php echo $url_de; ?>"><i class="icon-message"></i></a>
		<?php } ?>
	</div>
<?php });
