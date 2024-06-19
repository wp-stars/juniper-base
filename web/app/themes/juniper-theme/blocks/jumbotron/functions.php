<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/jumbotron' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/jumbotron/style.css';
	$script_file_path = $theme_path . '/blocks/jumbotron/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('jumbotron-css', $theme_path . '/blocks/jumbotron/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('jumbotron-js', $theme_path . '/blocks/jumbotron/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/jumbotron',
    function ( $context ) {

	    $context['fields']['dark_mode'] ? $context['dark_mode'] = 'dark' : $context['dark_mode'] = 'light';

        if( $context['fields']['layout'] == 'Text-Left' ){
            $context['reverse'] = 'order-first ml-[auto]';
        } else if( $context['fields']['layout'] == 'Text-Right' ){
            $context['reverse'] = 'order-last mr-[auto] ';
        }

        if( $context['fields']['layout'] == 'Fullwidth-Overlay' ){
            $context['overlay'] = 'overlay';
        }
        else if( $context['fields']['layout'] == 'Fullwidth-Textbox' ){
            $context['textbox'] = 'textbox';
        }
        
      
    
        return $context;
    }
);
