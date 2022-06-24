<?php

/**
 * A quick router hack for the built in PHP webserver
 *
 * Only used when testing via the PHP built in webserver.
 */

// Don't handle the favicon.ico.
if ($_SERVER["REQUEST_URI"] === '/favicon.ico') {
  exit;
}

// Run the actual script.
include_once __DIR__ . '/index.php';