<?php
/**
 * LimeSoda Cover Slider
 *
 * @package IWGPlating
 * @author LimeSoda
 * @copyright Copyright (c) 2020, LimeSoda
 * @link https://limesoda.com/
 */

namespace WPS\Cover\LS_Cover_Slider;

/**
 * Add Custom Fields
 */
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key' => 'cover_slider_custom_fields',
        'title' => 'Cover Slider Settings',
        'fields' => [
            [
                'key' => 'field_activate_cover_slider',
                'label' => __('Activate Cover Slider', 'iwgplating'),
                'name' => 'activate_cover_slider',
                'type' => 'checkbox',
                'choices' => [
                    'true' => __('Activate', 'iwgplating'),
                ],
                'layout' => 'vertical',
                'return_format' => 'value',
                'translations' => 'copy_once',
            ],
            [
                'key' => 'field_text_color',
                'label' => 'Text Color',
                'name' => 'text_color',
                'type' => 'color_picker',
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_activate_cover_slider',
                            'operator' => '==',
                            'value' => 'true',
                        ],
                    ],
                ],
                'default_value' => '#fff',
                'enable_opacity' => 0,
                'return_format' => 'string',
                'translations' => 'copy_once',
            ],
            [
                'key' => 'field_content_type',
                'label' => __('Content Type', 'iwgplating'),
                'name' => 'content_type',
                'type' => 'select',
                'instructions' => __('Choose between custom content or posts', 'iwgplating'),
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_activate_cover_slider',
                            'operator' => '==',
                            'value' => 'true',
                        ],
                    ],
                ],
                'choices' => [
                    'custom' => __('Custom Content', 'iwgplating'),
                    'post' => __('Posts', 'iwgplating'),
                ],
                'default_value' => false,
                'return_format' => 'value',
                'translations' => 'copy_once',
                'ajax' => 0,
            ],
            [
                'key' => 'field_slide_content',
                'label' => __('Slides Content', 'iwgplating'),
                'name' => 'slides_content',
                'type' => 'repeater',
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_content_type',
                            'operator' => '==',
                            'value' => 'custom',
                        ],
                    ],
                ],
                'min' => 1,
                'layout' => 'table',
                'sub_fields' => [
                    [
                        'key' => 'field_media_content',
                        'label' => __('Media Content', 'iwgplating'),
                        'name' => 'media_content',
                        'type' => 'group',
                        'layout' => 'block',
                        'sub_fields' => [
                            [
                                'key' => 'field_main_image',
                                'label' => __('Main Image', 'iwgplating'),
                                'name' => 'main_image',
                                'type' => 'image',
                                'required' => 1,
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                                'mime_types' => '',
                                'translations' => 'copy_once',
                            ],
                            [
                                'key' => 'field_icon_one',
                                'label' => __('Icon One', 'iwgplating'),
                                'name' => 'icon_one',
                                'type' => 'image',
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                                'mime_types' => '',
                                'translations' => 'copy_once',
                            ],
                            [
                                'key' => 'field_icon_two',
                                'label' => __('Icon Two (optional)', 'iwgplating'),
                                'name' => 'icon_two',
                                'type' => 'image',
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                                'mime_types' => '',
                                'translations' => 'copy_once',
                            ],
                            [
                                'key' => 'field_icon_three',
                                'label' => __('Icon Three (optional)', 'iwgplating'),
                                'name' => 'icon_three',
                                'type' => 'image',
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                                'mime_types' => '',
                                'translations' => 'copy_once',
                            ],
                        ],
                    ],
                    [
                        'key' => 'field_headings_and_color',
                        'label' => __('Headings and Color', 'iwgplating'),
                        'name' => 'headings_and_color',
                        'type' => 'group',
                        'layout' => 'block',
                        'sub_fields' => [
                            [
                                'key' => 'field_top_heading',
                                'label' => __('Top-Heading', 'iwgplating'),
                                'name' => 'top_heading',
                                'type' => 'text',
                                'translations' => 'translate',
                            ],
                            [
                                'key' => 'field_main_heading',
                                'label' => __('Main Heading', 'iwgplating'),
                                'name' => 'main_heading',
                                'type' => 'text',
                                'required' => 1,
                                'translations' => 'translate',
                            ],
                            [
                                'key' => 'field_background_color',
                                'label' => 'Background Color',
                                'name' => 'background_color',
                                'type' => 'color_picker',
                                'default_value' => '#ffef00',
                                'enable_opacity' => 0,
                                'return_format' => 'string',
                                'translations' => 'copy_once',
                            ],
                        ],
                    ],
                    [
                        'key' => 'field_text_content',
                        'label' => __('Text Content', 'iwgplating'),
                        'name' => 'text_content',
                        'type' => 'wysiwyg',
                        'wrapper' => [
                            'width' => '50',
                        ],
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 0,
                        'translations' => 'translate',
                    ],
                ],
            ],
            [
                'key' => 'field_post_select_repeater',
                'label' => __('Select Posts for Slider', 'iwgplating'),
                'name' => 'post_select_repeater',
                'type' => 'repeater',
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_content_type',
                            'operator' => '==',
                            'value' => 'post',
                        ],
                    ],
                ],
                'min' => 1,
                'layout' => 'table',
                'sub_fields' => [
                    [
                        'key' => 'field_post_picker',
                        'label' => 'Select Post',
                        'name' => 'post_picker',
                        'type' => 'post_object',
                        'post_type' => 'post',
                        'allow_null' => 0,
                        'multiple' => 0,
                        'return_format' => 'id',
                        'translations' => 'copy_once',
                        'ui' => 1,
                    ],
                    [
                        'key' => 'field_background_color',
                        'label' => 'Background Color',
                        'name' => 'background_color',
                        'type' => 'color_picker',
                        'default_value' => '#ffef00',
                        'enable_opacity' => 0,
                        'return_format' => 'string',
                        'translations' => 'copy_once',
                    ],
                    [
                        'key' => 'field_icons',
                        'label' => __('Icons', 'iwgplating'),
                        'name' => 'icons',
                        'type' => 'group',
                        'layout' => 'block',
                        'sub_fields' => [
                            [
                                'key' => 'field_icon_one',
                                'label' => __('Icon One', 'iwgplating'),
                                'name' => 'icon_one',
                                'type' => 'image',
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                                'mime_types' => '',
                                'translations' => 'copy_once',
                            ],
                            [
                                'key' => 'field_icon_two',
                                'label' => __('Icon Two (optional)', 'iwgplating'),
                                'name' => 'icon_two',
                                'type' => 'image',
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                                'mime_types' => '',
                                'translations' => 'copy_once',
                            ],
                            [
                                'key' => 'field_icon_three',
                                'label' => __('Icon Three (optional)', 'iwgplating'),
                                'name' => 'icon_three',
                                'type' => 'image',
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                                'mime_types' => '',
                                'translations' => 'copy_once',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page',
                ],
            ],
        ],
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
        'show_in_rest' => 0,
    ]);
}

add_action('wp', function () {
    $cover_slider = get_field('activate_cover_slider');
    if ($cover_slider && $cover_slider[0] === 'true') {
        add_action('wp_enqueue_scripts', '\WPS\Cover\LS_Cover_Slider\enqueue_cover_assets');
        add_action('astra_content_before', '\WPS\Cover\LS_Cover_Slider\add_cover_slider_markup');
    }
});

/**
 * Enqueue Cover Slider Styles and Scripts
 *
 * @return void
 */
function enqueue_cover_assets() {

    if (!defined('THEME_DIR')) {
        define('THEME_DIR', get_template_directory() . '/');
    }

    if (!defined('THEME_URI')) {
        define('THEME_URI', get_template_directory_uri() . '/');
    }

    wp_enqueue_style(
        'cover-slider-styles',
        THEME_URI . 'assets/css/cover-slider.css',
        [],
        filemtime(THEME_DIR . 'assets/css/cover-slider.css'),
    );

    $asset_file = include THEME_DIR . '/index.asset.php';
    wp_register_script(
        'cover-slider-scripts',
        THEME_URI . 'assets/js/cover-slider.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );
    wp_enqueue_script(
        'cover-slider-scripts'
    );
}

/**
 * Create and add cover slider markup
 *
 * @return void
 */
function add_cover_slider_markup() {
    $text_color = get_field('text_color');
    $html_markup = '<div class="ls-cover-slider" style="--slider-text-color:' . $text_color . '">';
    $html_markup .= '<div class="cover-slider-swiper-pagination swiper-pagination"></div>';
    $html_markup .= '<div class="ls-cover-slider__slides swiper"><div class="swiper-wrapper">';

    $content_type = get_field('content_type');
    $content = [];
    if ($content_type === 'custom') {
        $slides = get_field('slides_content');
        if ($slides) {
            foreach ($slides as $slide) {
                $image = $slide['media_content']['main_image']['sizes']['large'];
                $image_alt = $slide['media_content']['main_image']['alt'];
                $content['main_heading'] = $slide['headings_and_color']['main_heading'];
                $content['top_heading'] = $slide['headings_and_color']['top_heading'];
                $content['text_content'] = $slide['text_content'];
                $icons = [];
                if ($slide['media_content']['icon_one']) {
                    $icons[] = $slide['media_content']['icon_one'];
                }
                if ($slide['media_content']['icon_two']) {
                    $icons[] = $slide['media_content']['icon_two'];
                }
                if ($slide['media_content']['icon_three']) {
                    $icons[] = $slide['media_content']['icon_three'];
                }
                $color['background'] = $slide['headings_and_color']['background_color'];

                $html_markup .= get_slide_markup($image, $image_alt, $content, $color, $icons, []);
            }
        }
    } else {
        $posts = get_field('post_select_repeater');
        if ($posts) {
            foreach ($posts as $post) {
                $post_id = $post['post_picker'];

                $image = get_the_post_thumbnail_url($post_id);
                $thumbnail_id = get_post_thumbnail_id($post_id);
                $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                $content['main_heading'] = get_the_title($post_id);
                $post_args['date'] = date_i18n('M Y', strtotime(get_the_date('Y-m-d', $post_id)));
                $post_args['post_categories'] = get_the_category($post_id);
                $content['text_content'] = get_the_excerpt($post_id);
                $post_args['link'] = get_the_permalink($post_id);
                $post_args['new'] = get_field('news_new', $post_id);

                $color['background'] = $post['background_color'];

                $icons = [];
                if ($post['icons']['icon_one']) {
                    $icons[] = $post['icons']['icon_one'];
                }
                if ($post['icons']['icon_two']) {
                    $icons[] = $post['icons']['icon_two'];
                }
                if ($post['icons']['icon_three']) {
                    $icons[] = $post['icons']['icon_three'];
                }

                $html_markup .= get_slide_markup($image, $image_alt, $content, $color, $icons, $post_args);
            }
        }
    }
    $html_markup .= '</div></div>';
    $html_markup .= '<div class="cover-slider-swiper-button-prev swiper-button-prev"></div><div class="cover-slider-swiper-button-next swiper-button-next"></div>';
    $html_markup .= '</div>';
    echo $html_markup;
}

/**
 * Create single slide markup
 *
 * @param array|string $image shortcode attributes.
 * @param array|string $image_alt shortcode attributes.
 * @param array|string $content shortcode attributes.
 * @param array|string $color shortcode attributes.
 * @param array|string $icons shortcode attributes.
 * @param array|string $post_args shortcode attributes.
 *
 * @return string
 */
function get_slide_markup($image, $image_alt, $content, $color, $icons, $post_args): string {
    $slide_markup = '<div class="ls-cover-slider__slide swiper-slide" style="--slide-background-color:' . $color['background'] . '">';
    $slide_markup .= '<div class="ls-cover-slider__image">';
    $slide_markup .= '<img src="' . $image . '" alt="' . $image_alt . '">';
    $slide_markup .= '</div>';
    $slide_markup .= '<div class="ls-cover-slider__column">';
    if ($icons) {
        $slide_markup .= '<div class="ls-cover-slider__icons">';

        for ($i = 0; $i < 3; $i++) {
            $slide_markup .= '<div class="ls-cover-slider__icon';
            if ($icons[ $i ]) {
                $slide_markup .= ' ls-has-icon">';
                $slide_markup .= '<img src="' . $icons[ $i ]['sizes']['thumbnail'] . '">';
            } else {
                $slide_markup .= '">';
            }
            $slide_markup .= '</div>';
        }
        $slide_markup .= '</div>';
    }
    $slide_markup .= '<div class="ls-cover-slider__content">';
    if ($post_args['new']) {
        $slide_markup .= '<div class="ls-cover-slider__new">' . __('NEW', 'iwgplating') . '</div>';
    }
    if ($content['top_heading']) {
        $slide_markup .= '<span class="ls-cover-slider__top_heading">' . $content['top_heading'] . '</span>';
    }
    if ($post_args['date']) {
        $slide_markup .= '<p class="ls-cover-slider__post_info"><span class="ls-cover-slider__post_date">' . $post_args['date'] . '</span>';
        if ($post_args['post_categories']) {
            $slide_markup .= '<span class="ls-cover-slider__post_categories">';
            foreach ($post_args['post_categories'] as $post_category) {
                $slide_markup .= '<span class="ls-cover-slider__post_category"> | ' . $post_category->name . '</span>';
            }
            $slide_markup .= '</span>';
        }
        $slide_markup .= '</p>';
    }
    $slide_markup .= '<h2 class="ls-cover-slider__main_heading">' . $content['main_heading'] . '</h2>';
    $slide_markup .= '<div class="ls-cover-slider__text_content">' . $content['text_content'] . '</div>';

    if ($post_args['link']) {
        $slide_markup .= '<div class="ls-cover-slider__button"><a href="' . $post_args['link'] . '">' . __('Read now', 'iwgplating') . '</a></div>';
    }
    $slide_markup .= '</div>';
    $slide_markup .= '</div>';
    $slide_markup .= '</div>';
    return $slide_markup;
}

/**
 * Remove read more of excerpts
 */
add_filter('excerpt_more', '__return_empty_string');
