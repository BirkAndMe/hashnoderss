<?php

/**
 * A very simple file cache, using the temporary directory.
 */

/**
 * Get cache.
 */
function getCache($key, $ttl = 0) {
  $path = getChachePath($key);

  if (file_exists($path)) {
    if ($ttl === 0 || filemtime($path) > time() - $ttl) {
      return file_get_contents($path);
    }
  }
}

/**
 * Set cache.
 */
function setCache($key, $value) {
  if (!is_writable(sys_get_temp_dir())) {
    return $value;
  }

  file_put_contents(getChachePath($key), $value);

  return $value;
}

/**
 * Get the cache path of a key.
 */
function getChachePath($key) {
  return sys_get_temp_dir() . DIRECTORY_SEPARATOR
    . 'hashnoderss-'
    . substr(md5($key), 0, 12);
}