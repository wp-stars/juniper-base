<?php

namespace Juniper\Taxonomies;

class Application
{
    public string $taxonomy_slug;
    public string $taxonomy_name;

    public function __construct()
    {
        $this->taxonomy_slug = 'application';
        $this->taxonomy_name = _('Applikation', 'juniper');

        add_action('init', array( $this, 'register_custom_taxonomy' ));
    }

    public function register_custom_taxonomy()
    {
        $args = array(
            'label'        => $this->taxonomy_name,
            'public'       => true,
            'rewrite'      => false,
            'hierarchical' => false
        );

        register_taxonomy($this->taxonomy_slug, 'product', $args);
    }
}