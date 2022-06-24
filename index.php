<?php

/**
 * Hashnoderss - Filter by tags in a hashnode blog rss feed.
 *
 * _Usage_
 *
 * Start the server: `php -S localhost:8000 router-hack.php`.
 *
 * Now goto `http://localhost:8000/BLOG_HOSTNAME`,
 *
 * The [Built-in web server](https://www.php.net/features.commandline.webserver)
 * is not designed for production, so go with your favorite webserver instead!
 *
 * This was originally part of the [Linode](https://www.linode.com) and
 * [Hashnode](https://hashnode.com) [hackaton June 2022](https://townhall.hashnode.com/build-with-linode-hackathon-june-2022).
 *
 * @version 1.0.0
 * @author Philip Birk-Jensen <philip@birk-jensen.dk>
 */

include_once __DIR__ . '/definitions.php';
include_once __DIR__ . '/utils.cache.php';
include_once __DIR__ . '/utils.normalize.php';
include_once __DIR__ . '/utils.query.php';
include_once __DIR__ . '/utils.validate.php';

$isDebug = isset($_GET['debug']);
unset($_GET['debug']);

// Get blog host from the given URL.
list($hostname) = array_slice(explode('/', $_SERVER["REQUEST_URI"]), 1);
$hostname = current(explode('?', $hostname));

$rssUrl = "https://$hostname/rss.xml";

// Load the RSS (try cache first, then get from the URL).
$rssRaw = getCache($rssUrl, SETTING_CACHE_RSS_TTL);
if (!$rssRaw) {
  $rssRaw = file_get_contents($rssUrl);
  setCache($rssUrl, $rssRaw);
}

// Parse the XML into a SimpleXML object.
$rssXml = simplexml_load_string($rssRaw);

// Get all the urls to check, and put them into the $posts array.
$posts = [];
$l = $rssXml->channel->item->count();
while ($l--) {
  if ($l >= SETTINGS_MAX_API_ITEMS) {
    unset($rssXml->channel->item[$l]);
    continue;
  }

  $url = parse_url($rssXml->channel->item[$l]->link);

  // The key is used as an alias for the GraphQL.
  $posts['i' . substr(md5($url['path']), 0, 12)] = [
    // Slug is used by the Hashnode api to retrieve a specific post.
    'slug' => substr($url['path'], 1),
    // Used when removing posts from the XML.
    'rssItemIndex' => $l
  ];
}

// Normalize the filter values so they match a normalized Hashnode API item.
$filters = normalizeFilter($_GET);

// Bail if there's no filter.
if (empty($filters)) {
  header('Content-Type: text/xml');
  die($rssRaw);
}

// Build GraphQL property list.
$properties = buildQueryProperties($filters);

// Prepare post queries with an alias to identify them.
$queryPosts = [];
foreach ($posts as $alias => $post) {
  $queryPosts[] = "$alias: post(slug: \"{$post['slug']}\", hostname: \"$hostname\") $properties";
}

// Finalize the query
$query = '{' . implode("\n", $queryPosts) . '}';

// Create a stream context with the query for the Hashnode api.
$context  = stream_context_create([
  'http' => [
    'method'  => 'POST',
    'content' => json_encode(['query' => $query]),
    'header'=>  "Content-Type: application/json",
  ],
]);

// Call Hashnode api and get the GraphQL Response (try cache first).
$apiResult = getCache($query, SETTING_CACHE_RSS_TTL);
if (!$apiResult) {
  $apiResult = file_get_contents('https://api.hashnode.com', false, $context );
  setCache($query, $apiResult);
}

// Parse to JSON.
$apiJson = json_decode($apiResult);

// Check all the posts, and remove them from the XML if they're not valid.
foreach ($apiJson->data as $alias => $item) {
  // Normalize the API response data so it's easily matched the filter.
  $normalizedItem = normalizeApiResponseProperties($filters, $item);

  // Check each filter on the curren titem.
  foreach ($filters as $property => $filterStrings) {
    // Remove the item from the rssXml if it doesn't validate.
    if (($filterString = validateFilterStrings($filterStrings, $normalizedItem[$property])) !== true) {
      $apiJson->data->{$alias}->__REMOVED__ = $filterString;
      unset($rssXml->channel->item[$posts[$alias]['rssItemIndex']]);
      break;
    }
  }
}

if ($isDebug) {
  include_once __DIR__ . '/utils.debug.php';

  debug('Filters', $filters);
  debug('Graph QL Query', $query);
  debug('RSS Posts (parsed)', $posts);
  debug('Hashnode API Result', $apiJson);

  die();
}

// Return the filtered rss.
header('Content-Type: text/xml');
echo $rssXml->saveXML();
