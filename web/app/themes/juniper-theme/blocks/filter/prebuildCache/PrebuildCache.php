<?php
/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */

namespace blocks\filter\prebuildCache;

use Exception;
use stdClass;

class PrebuildCache {
	private static PrebuildCache $instance;

	private array $cache_loaded = [];

	public static string $FILTER_PREGEN_JSON_META_KEY = 'filter_pregen_json';
	public static string $FILTER_PREGEN_TIME_META_KEY = 'filter_pregen_time';

	public static int $standard_ttl = HOUR_IN_SECONDS;

	/**
	 * @return PrebuildCache
	 */
	public static function get_instance(): PrebuildCache {
		self::$instance = self::$instance ?? new PrebuildCache();

		return self::$instance;
	}

	private function __construct() {
		try {
			$this->load_cache_into_instance();
		} catch ( Exception $e ) {
			error_log( 'ERROR: UNABLE TO LOAD IN PRODUCT SEARCH CACHE!!!! ' . $e->getMessage());
		}
	}

	public function get_current_cache(): array {
		return $this->cache_loaded;
	}

	/**
	 * @throws Exception
	 */
	private function load_cache_into_instance(): void {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT pregen_json.post_id AS post_id, pregen_json.meta_value AS pregen_json, pregen_time.meta_value AS pregen_time FROM (SELECT * FROM $wpdb->postmeta WHERE meta_key = %s) AS pregen_json ,(SELECT * FROM $wpdb->postmeta WHERE meta_key = %s) AS pregen_time WHERE pregen_json.post_id = pregen_time.post_id",
		                         self::$FILTER_PREGEN_JSON_META_KEY,
		                         self::$FILTER_PREGEN_TIME_META_KEY,
		);

		$result = $wpdb->get_results( $query, OBJECT_K );

		$this->cache_loaded = $result;
	}

	/**
	 * @throws Exception
	 */
	public function get_prebuild( int $id, $force_refill ): ?stdClass {
		if ( ! get_post( $id ) ) {
			return null;
		}

		if($force_refill) {
			$this->refill_prebuild($id);
		}

		$pregen_cache_still_uptodate = $this->json_from_cache_still_uptodate( $id, self::$standard_ttl );
		$pregen_exists               = $this->entry_exists( $id );

		if ( $pregen_exists && $pregen_cache_still_uptodate) {
			return json_decode( $this->pull_pregen_entry_json( $id ) );
		}

		$this->refill_prebuild( $id );

		return json_decode( $this->pull_pregen_entry_json( $id ) );
	}

	/**
	 * @throws Exception
	 */
	public function json_from_cache_still_uptodate( int $id, int $ttl ): bool {
		$date_time_gen = new \DateTime( $this->pull_pregen_entry_datetime( $id ) );
		$now = new \DateTime('now');

		$timediff = date_diff( $date_time_gen, $now);

		return $timediff->s <= $ttl;
	}

	/**
	 * @throws Exception
	 */
	public function refill_prebuild( int $id ): void {
		$generated_prebuild     = $this->generate_prebuild_json( $id );
		$now_date_time          = new \DateTime( 'now' );
		$now_date_time_formated = $now_date_time->format( 'Y-m-d H:i:s' );

		update_post_meta( $id, self::$FILTER_PREGEN_JSON_META_KEY, $generated_prebuild );
		update_post_meta( $id, self::$FILTER_PREGEN_TIME_META_KEY, $now_date_time_formated );

		// TODO: implement only generate new entry
		$this->load_cache_into_instance();
	}

	private function entry_exists( int $id ): bool {
		return isset( $this->cache_loaded[ $id ] ) && ! empty( $this->cache_loaded[ $id ] );
	}

	private function pull_pregen_entry_json( int $id ): string {
		return $this->cache_loaded[ $id ]->pregen_json;
	}

	private function pull_pregen_entry_datetime( int $id ) {
		return $this->cache_loaded[ $id ]->pregen_time;
	}

	public function generate_prebuild_json( int $post_íd ): string {
		$post = get_post( $post_íd );

		$fields = get_fields( $post->ID );

		$post_obj     = new stdClass();
		$post_obj->ID = $post->ID;
		//		$post_obj->fields               = get_fields(json_encode($post));
		$post_obj->excerpt           = htmlspecialchars( wp_trim_excerpt( '', $post ) );
		$post_obj->post_title        = htmlspecialchars( $post->post_title );
		$post_obj->post_name         = htmlspecialchars( $post->post_name );
		$post_obj->date              = htmlspecialchars( $post->post_date );
		$post_obj->featured_image    = htmlspecialchars( get_the_post_thumbnail_url( $post ) );
		$post_obj->link              = htmlspecialchars( get_permalink( $post ) );
		$post_obj->price             = (int) ( wc_get_product( $post->ID ) )->get_regular_price();
		$post_obj->subheadline       = htmlspecialchars( $fields['wps_sp_subheadline'] ?? '' );
		$post_obj->description_title = htmlspecialchars( $fields['wps_sp_description_title'] ?? '' );
		$post_obj->description_text  = htmlspecialchars( $fields['wps_sp_description_text'] ?? '' );
		//		$post_obj->features_text        = htmlspecialchars( $fields['wps_sp_features_text'] ?? '' );
		//		$post_obj->areas_of_application = htmlspecialchars( $fields['wps_sp_areas_of_application_text'] ?? '' );

		$taxonomies = get_post_taxonomies( $post );

		$taxonomy_data = [];
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy );

			if ( $terms && ! is_wp_error( $terms ) ) {

				foreach ( $terms as $term ) {
					$taxonomy_data[ $taxonomy ][] = [
						'term_id' => $term->term_id,
						'name'    => $term->name,
						'slug'    => $term->slug
					];
				}

			}

		}

		$post_obj->taxonomies = $taxonomy_data;

		$terms        = get_the_terms( $post, 'product_type' );
		$product_type = $terms && ! is_wp_error( $terms )
			? $terms[0]->name
			: '';

		$post_obj->product_type = $product_type;

		$rendered_card = do_shortcode( "[wps_get_product_card product_id='{$post->ID}' encoding='ISO-8859-1']" );

		$encoded_html = base64_encode( $rendered_card );

		$post_obj->html = $encoded_html;

		return json_encode( $post_obj );
	}

}

/**
 * SELECT pregen_json.post_id AS post_id, pregen_json.meta_value AS pregen,  FROM (SELECT * FROM `wp_postmeta` WHERE meta_key = 'filter_pregen_json') AS pregen_json ,(SELECT * FROM `wp_postmeta` WHERE meta_key = 'filter_pregen_time') AS pregen_time WHERE pregen_json.post_id = pregen_time.post_id;
 */