<?php
/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/StarterSite.php';

Timber\Timber::init();

// Sets the directories (inside your theme) to find .twig files.
Timber::$dirname = [ 'templates', 'views' ];

new StarterSite();

// WP data wiring, one file per domain.
require_once __DIR__ . '/inc/cache.php';
require_once __DIR__ . '/inc/categories.php';
require_once __DIR__ . '/inc/options.php';
require_once __DIR__ . '/inc/events.php';
require_once __DIR__ . '/inc/blocks.php';
require_once __DIR__ . '/inc/blog.php';
require_once __DIR__ . '/inc/interior.php';
