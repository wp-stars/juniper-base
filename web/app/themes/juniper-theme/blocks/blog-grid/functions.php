<?php

add_action('wp_enqueue_scripts', function() {
	if (has_block('acf/blog-grid')) {
	$time = time();
	$theme_path = get_template_directory_uri();

		wp_enqueue_style('blog-grid-css', $theme_path . '/blocks/blog-grid/style.css', array(), $time, 'all');
		wp_enqueue_script('blog-grid-js', $theme_path . '/blocks/blog-grid/script.js', array(), $time, true);
	}
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/blog-grid',

	

 	function( $context ) {

		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'order'          => 'DESC',
		);
	
		$query = new WP_Query($args);
	
		$posts_data = array();
	
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
	
				$post_data = array(
					'title'         => get_the_title(),
					'featured_image' => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '',
					'date'          => get_the_date('d/m/Y'),
					'permalink'     => get_permalink(),
				);
	
				$posts_data[] = $post_data;
			}
	
			wp_reset_postdata();
		}
	
		$context["posts"] = $posts_data;

		
 	return $context;
 });
