<?php

namespace Juniper\Taxonomies;

class MetalsAndAccessories
{
    public string $taxonomy_slug;
    public string $taxonomy_name;

    public function __construct()
    {
        $this->taxonomy_slug = 'metals-and-accessories';
        $this->taxonomy_name = __('Metals And Accessories', 'juniper');

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

        register_taxonomy($this->taxonomy_slug, 'product', $args);
    }

}
