<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::context();


$context['home_url'] = home_url();
$context['contact'] = home_url( '/kontakt/' );
$context['projects'] = home_url( '/projekte/' );

Timber::render( '404.twig', $context );
