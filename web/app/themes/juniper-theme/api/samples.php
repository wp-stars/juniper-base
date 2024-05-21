<?php
/**
 * REST API Routes for Samples
 *
 * @package IWGPlating
 */

namespace WPS\API\Samples;

use WP_REST_Server;
use WP_REST_Request;

/**
 * Return Samples from Post ID
 *
 * @param WP_REST_Request $request API Request.
 * @return array List of Samples
 */
function get_samples(WP_REST_Request $request) {
	$wishlist = $request->get_param('wishlist');
	$products = '';
	foreach ($wishlist as $item) {
		$products .= get_the_title($item) . ' - ' . get_the_permalink($item) . ' ; ';
	}
	$response = [
		'success' => true,
		'products' => $products,
	];
	return $response;
}

add_action('rest_api_init', function () {
	register_rest_route('ls/v1', '/samples/', [
		'methods' => WP_REST_Server::READABLE,
		'callback' => 'WPS\API\Samples\get_samples',
		'permission_callback' => '__return_true',
	]);
});
