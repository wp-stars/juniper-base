<?php

namespace IWG\Taxonomies\Product;

class Application
{
    public string $taxonomy_slug;
    public string $taxonomy_name;
    public string $postType = 'product';

    public function __construct()
    {
        $this->taxonomy_slug = 'application';
        $this->taxonomy_name = __('Applikation', 'juniper');

        add_action('init', array( $this, 'register_custom_taxonomy' ));
    }

    public function register_custom_taxonomy()
    {
        $args = array(
            'label'        => $this->taxonomy_name,
            'public'       => true,
            'rewrite'      => false,
            'hierarchical' => true
        );

        register_taxonomy($this->taxonomy_slug, $this->postType, $args);
    }
}