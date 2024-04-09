<?php


namespace Limesoda\Astra_Child\Shortcodes\ContactButton;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('ContactButtonShortcode')) {

    class ContactButtonShortcode {

        public function __construct() {
            add_shortcode('ls_contact_button', array($this, 'render_contact_button'));
        }

        public function render_contact_button() {
            ob_start();
            if (get_page_by_title('Kontakt')){
                $url_de = get_the_permalink(get_page_by_title('Kontakt')) . '/#fragen-musterbestellung';
            }
            if (get_page_by_title('Kontakt')){
            $url_en = get_the_permalink(get_page_by_title('Contact')) . '/#contact-form';
            }
            $current_lang = defined('ICL_LANGUAGE_CODE');
            ?>
           <div class="ls-contact-button">
		<?php if ($current_lang === 'en') { ?>
		<a href="<?php echo $url_en; ?>"><i class="icon-message"></i></a>
		<?php } else { ?>
		<a href="<?php echo $url_de; ?>"><i class="icon-message"></i></a>
		<?php } ?>
	</div>
            <?php
            return ob_get_clean();
        }
    }

    new ContactButtonShortcode();
}

