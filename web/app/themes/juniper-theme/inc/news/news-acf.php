<?php
/**
 * LimeSoda ACF Field New Flag for News
 *
 * @package IWGPlating
 * @author LimeSoda
 * @copyright Copyright (c) 2020, LimeSoda
 * @link https://limesoda.com/
 */

namespace Limesoda\Astra_Child\CustomPostTypes\LS_News;

/**
 * Add and register custom fields for "News"
 *
 * @return void
 */
add_action('acf/init', function() {
	if (function_exists('acf_add_local_field_group')) {
		acf_add_local_field_group([
			'key' => 'news_custom_fields',
			'title' => 'New Flag',
			'fields' => [
				[
					'key' => 'field_news_new',
					'label' => __('NEW', 'iwgplating'),
					'name' => 'news_new',
					'type' => 'true_false',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				],
			],
			'location' => [
				[
					[
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'post',
					],
				],
			],
			'menu_order' => 0,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
		]);
		acf_add_local_field_group([
			'key' => 'newsdetail_custom_fields',
			'title' => 'News Details',
			'fields' => [
				[
					'key' => 'field_news_downloads',
					'label' => __('Downloads', 'iwgplating'),
					'name' => 'news_downloads',
					'type' => 'repeater',
					'layout' => 'table',
					'min' => 0,
					'sub_fields' => [
						[
							'key' => 'field_news_file_upload',
							'label' => __('File Upload', 'iwgplating'),
							'name' => 'news_file_upload',
							'type' => 'file',
							'return_format' => 'array',
							'library' => 'all',
						],
					],
				],
			],
			'location' => [
				[
					[
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'post',
					],
				],
			],
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
		]);
		acf_add_local_field_group([
			'key' => 'newscategory_custom_fields',
			'title' => 'Icon',
			'fields' => [
				[
					'key' => 'field_news_icon',
					'label' => 'Icon Upload',
					'name' => 'newscategory_file_upload',
					'type' => 'file',
					'return_format' => 'array',
					'library' => 'all',
				],
			],
			'location' => [
				[
					[
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'category',
					],
				],
			],
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
		]);
	}
});

