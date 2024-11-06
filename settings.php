<?php

// The blog domain.
const HASHNODE_HOST = 'blog.birk-jensen.dk';

// The feed channel description.
const FEED_DESCRIPTION = 'BirkAndMe Drupal feed';

// Cache time to live.
const CACHE_TTL = 600;

// The REQUEST_SCHEME should be native for Apache^2.4, but change this to match
// your server setup.
$scheme = $_SERVER['REQUEST_SCHEME'] ?? 'https';