<?php

if ( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_6638ddcba6fb9',
        'title' => 'product_cat_fields',
        'fields' => array(
            array(
                'key' => 'field_6638ddd2ffde4',
                'label' => 'Farbe',
                'name' => 'wps_product_cat_color',
                'aria-label' => '',
                'type' => 'color_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'wpml_cf_preferences' => 3,
                'default_value' => '#ffeb00',
                'enable_opacity' => 0,
                'return_format' => 'string',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'taxonomy',
                    'operator' => '==',
                    'value' => 'product_cat',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
        'acfml_field_group_mode' => 'localization',
    ));

endif;