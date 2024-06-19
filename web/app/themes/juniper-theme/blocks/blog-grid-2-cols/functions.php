<?php

add_action('wp_enqueue_scripts', function() {
	if ( ! has_block( 'acf/blog-grid-2-cols' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$script_file_path = $theme_path . '/blocks/blog-grid-2-cols/script.js';
	$style_file_path = $theme_path . '/blocks/blog-grid-2-cols/style.css';

	$script_file_content = file_get_contents( $script_file_path );

	if ( empty( $script_file_content ) ) {
		return;
	}

	unset( $script_file_content );

	wp_enqueue_style( 'blog-grid-2-cols-css', $style_file_path, [], $time, 'all');

	$style_file_content = file_get_contents( $style_file_path );

	if ( empty( $style_file_content ) ) {
		return;
	}

	unset( $style_file_content );

	wp_enqueue_script( 'blog-grid-2-cols-js', $script_file_path, [], $time, true);
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/blog-grid-2-cols',

 	function( $context ) {

		$args = [
			'post_type'      => 'post',
			'posts_per_page' => 4,
			'order'          => 'DESC',
		];
	
		$query = new WP_Query($args);
	
		$posts_data = [];
	
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
	
				$post_data = [
					'title'         => get_the_title(),
					'featured_image' => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '',
					'date'          => get_the_date('d/m/Y'),
					'permalink'     => get_permalink(),
					'excerpt'		=> get_the_excerpt(),
				];
	
				$posts_data[] = $post_data;
			}
	
			wp_reset_postdata();
		}
	
		$context["posts"] = $posts_data;

		$context["fields"]["dark_mode"] == true ?
        $context["dark_mode"] = "dark" : $context["dark_mode"] = "";

 	return $context;
 });
