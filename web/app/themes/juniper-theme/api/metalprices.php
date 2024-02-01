<?php
/**
 * REST API Routes for Metalprices
 *
 * @package IWGPlating
 */

namespace WPS\MetalPrices;

use WP_REST_Server;
use WP_REST_Request;

/**
 * Return Metalprices between dates
 * GET Params: after, before, key
 * Date Formats: YYYY-MM-DD
 *
 * @param WP_REST_Request $request API Request.
 * @return array List of Metalprices
 */
function get_metalprices(WP_REST_Request $request) {
	$after = $request->get_param('after');
	$before = $request->get_param('before');
	$key = $request->get_param('key');
	$preview_bool = $request->get_param('preview');

	$transient = get_transient(substr(md5('get_metalprices_' . $key . $after . $before), 0, 8));
	if ($transient !== false) {
		return $transient;
	} else {
		$args = [
			'post_type' => 'metalprices',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'ASC',
			'date_query' => [
				[
					'after' => $after,
					'before' => $before,
					'inclusive' => true,
				],
			],
		];

		$query = new \WP_Query($args);
		$posts = $query->posts;

		$prices = [];
		if (!empty($posts)) {
			foreach ($posts as $post) {
				$prices[] = [
					'ID' => $post->ID,
					'post_date' => (new \DateTime($post->post_date))->format('Y-m-d'),
					'price' => floatval(get_field('metalprice_' . $key, $post->ID)),
				];
			}
			wp_reset_postdata();
		} else {
			$args_extended = [
				'post_type' => 'metalprices',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'date',
				'order' => 'ASC',
				'date_query' => [
					[
						'after' => gmdate('Y-m-d', strtotime(gmdate($after)) - (24 * 60 * 60 * 14)),
						'before' => $before,
						'inclusive' => true,
					],
				],
			];

			$query_extended = new \WP_Query($args_extended);
			$posts_extended = $query_extended->posts;
			if (!empty($posts_extended)) {
				foreach ($posts_extended as $post) {
					$prices[] = [
						'ID' => $post->ID,
						'post_date' => (new \DateTime($post->post_date))->format('Y-m-d'),
						'price' => floatval(get_field('metalprice_' . $key, $post->ID)),
					];
				}
				wp_reset_postdata();
			}
		}
		$response['prices'] = $prices;
	}

	$preview = [];
	if ($preview_bool) {
		$preview_args = [
			'post_type' => 'metalprices',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'ASC',
			'date_query' => [
				[
					'after' => gmdate('Y-m-d', strtotime(gmdate('Y-m-d')) - (24 * 60 * 60 * 365)),
					'before' => gmdate('Y-m-d'),
					'inclusive' => true,
				],
			],
		];

		$preview_query = new \WP_Query($preview_args);
		$preview_posts = $preview_query->posts;
		if (!empty($preview_posts)) {
			foreach ($preview_posts as $post) {
				$preview[] = [
					'ID' => $post->ID,
					'post_date' => (new \DateTime($post->post_date))->format('Y-m-d'),
					'price' => floatval(get_field('metalprice_' . $key, $post->ID)),
				];
			}
		}
		$response['preview'] = $preview;
		wp_reset_postdata();
	}

	set_transient(substr(md5('get_metalprices_' . $key . $after . $before), 0, 8), $response, 3600);
	return $response;
}

add_action('rest_api_init', function () {
	register_rest_route('ls/v1', '/metalprices/', [
		'methods' => WP_REST_Server::READABLE,
		'callback' => 'WPS\API\MetalPrices\get_metalprices',
		'permission_callback' => '__return_true',
	]);
});
