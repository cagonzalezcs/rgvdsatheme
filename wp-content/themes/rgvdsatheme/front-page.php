<?php
/**
 * The front page template. Static front page or posts index both land here;
 * content is the designed Home layout in views/front-page.twig.
 */

$context         = Timber::context();
$context['post'] = Timber::get_post();

Timber::render( 'front-page.twig', $context );
