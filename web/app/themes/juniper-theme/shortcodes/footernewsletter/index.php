<?php
/**
 * LimeSoda Shortcode for footer newsletter
 * Displays cards for all footer newsletter
 *
 * @author        LimeSoda
 * @copyright    Copyright (c) 2020, LimeSoda
 * @link        https://limesoda.com/
 * @package Limesoda\\Astra_Child\\Shortcodes\\job
 */

namespace Limesoda\Astra_Child\Shortcodes\FooterNewsletter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('FooterNewsletter')) {

    /**
     * LS_SHORTCODE_FooterNewsletter
     *
     * @since 1.0.0
     * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
     */
    class FooterNewsletter {

        /**
         * Set class instance
         *
         * @var $instance
         */
        private static $instance;

        /**
         * Set shortcode slug name
         *
         * @var string
         */

        private static string $slug = 'footernewsletter';

        /**
         * Localized Vars
         *
         * @var array
         */
        private static array $localized_vars = [];

        /**
         * Flag to check if modal has been displayed
         *
         * @var bool
         */
        private static $modal_displayed = false;

        /**
         * Initiator
         *
         * @return object initialized object of class.
         */
        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor
         */
        public function __construct() {
    
            add_action(
                'init',
                function () {
                    add_shortcode(self::$slug, [$this, 'shortcode_markup']);
                }
            );
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        }

        /**
         * Enqueue Assets for Shortcode
         *
         * @return void
         */
        public function enqueue_scripts() {
            global $post;

            if (!defined('THEME_DIR')) {
                define('THEME_DIR', get_template_directory() . '/');
            }

            if (!defined('THEME_URI')) {
                define('THEME_URI', get_template_directory_uri() . '/');
            }

            if (has_shortcode($post->post_content, self::$slug) && !self::$modal_displayed) {
                $asset_file = include THEME_DIR . 'shortcodes/' . self::$slug . '/index.asset.php';
                wp_enqueue_style(
                    'ls_shortcode_style_' . self::$slug,
                    THEME_URI . 'shortcodes/' . self::$slug . '/index.css',
                    [],
                    filemtime(THEME_DIR . 'shortcodes/' . self::$slug . '/index.css')
                );
            }
        }

        public function enqueue_hubspot_script_in_footer() {
            wp_enqueue_script('hubspot-forms-script', '//js.hsforms.net/forms/v2.js', array(), '1.0', true);
        }

        /**
         * Creates markup for job shortcode
         *
         * @param array|string $attributes shortcode attributes.
         * @return string html markup
         */
        public function shortcode_markup($attributes = []): string {

            echo '<p class="font-bold leading-6 pb-2">' . __('Newsletter','wps-juniper') . '</p>';

            $privacyPageUrl = (get_locale() === "de_DE") ? '/datenschutz' : '/en/privacy-policy/';
            echo '<p class="mb-7" >' . sprintf(__('Register now and always be well informed! Here you will find information about our <a class="underline hover:no-underline" target="_blank" href="%s">privacy policy</a>.','wps-juniper'), $privacyPageUrl) . '</p>';

            if (self::$modal_displayed) {
                return ''; // Return empty string if modal has already been displayed
            }

            $formId = (get_locale() === "de_DE") ? "7c1eb7af-43a6-4cb0-8836-bcd82e6bc5f4" : "2cebeb42-68d0-4fd8-8c8f-da8f8ad8204f";
            $form_id = uniqid();

            if (!isset($GLOBALS['hubspot_script_enqueued'])) {
                add_action('wp_footer', [$this, 'enqueue_hubspot_script_in_footer']);
                $GLOBALS['hubspot_script_enqueued'] = true;
            }

            ob_start();

            ?>
            <script>
                hbspt.forms.create({
                    portalId: '25864699',
                    formId: '<?= $formId; ?>',
                    target: '.newsletter-wrapper-<?= $form_id?>'
                });
            </script>
            <?php

            $newsletterForm = ob_get_contents();
            ob_get_clean();

            $button = '<button class="newsletterBtn">' . __('Subscribe', 'wps-juniper') . '</button>';

            // Set the flag to indicate that the modal has been displayed
            self::$modal_displayed = true;

            return "<div class='newsletter'>
                <div class='newsletterModal modal'>
                    <div class='modal-content'>
                        <span class='close'>&times;</span>
                        <div class='newsletter-wrapper-$form_id'>
                            $newsletterForm
                        </div>
                    </div>
                </div>
                $button
            </div>";
        }
    }
}
return FooterNewsletter::get_instance();
