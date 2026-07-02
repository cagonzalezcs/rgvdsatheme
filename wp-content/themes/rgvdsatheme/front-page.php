<?php
/**
 * The front page template. Static front page or posts index both land here;
 * content is the designed Home layout in views/front-page.twig.
 */

$context         = Timber::context();
$context['post'] = Timber::get_post();

// Domain files (inc/) inject home_events, blog_featured, blog_rows, options.
$context = apply_filters( 'rgvdsa/context/front_page', $context );

Timber::render( 'front-page.twig', $context );
