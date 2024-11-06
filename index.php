<?php

use FeedWriter\RSS2;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

require __DIR__ . '/vendor/autoload.php';


// -----------------------------------------------------------------------------
// Setup.
// -----------------------------------------------------------------------------

// Include the settings.
$settings = __DIR__ . '/settings.php';
if (!is_readable($settings)) {
  die('Copy and edit the "example.settings.php" to "settings.php"');
}
require $settings;


// -----------------------------------------------------------------------------
// Fetch data from hashnode.
// -----------------------------------------------------------------------------
$tags = $_GET['tags'] ?? [];

// Prepare cache.
$cache = new FilesystemAdapter();
$cache_key = md5(json_encode([HASHNODE_HOST, $tags]));

// Use a simple cache for speed and as a kindness on the API.
$body = $cache->get($cache_key, function (ItemInterface $item) use ($tags): string {
  $item->expiresAfter(CACHE_TTL);

  // Call the Hashnode API, and fetch all the nodes with a specific tag
  $client = new Client();
  $response = $client->post('https://gql.hashnode.com', [
    RequestOptions::JSON => ['query' => '{
      publication(host: "' . HASHNODE_HOST . '") {
        title
        url
          posts(first: 10' . (!empty($tags) ? ', filter: {tagSlugs: ["' . implode('", "', $tags) . '"]}' : '') . ') {
          edges {
            node {
              id
              publishedAt
              title
              subtitle
              url
              brief
              content {
                html
              }
              author {
                name
              }
            }
          }
        }
      }
    }']
  ]);

  // Assume everything went accordingly and get the inner data.
  return (string) $response->getBody();
});

// Get the data from the response body.
$data = json_decode($body)->data;


// -----------------------------------------------------------------------------
// Restructure the data into an RSS feed.
// -----------------------------------------------------------------------------

// Setup the feed.
$feed = new RSS2();
$feed->setTitle($data->publication->title);
$feed->setLink($data->publication->url);

$feed->setAtomLink("{$scheme}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", 'self', 'application/rss+xml');
$feed->setDescription(FEED_DESCRIPTION);

// Add all the posts.
foreach ($data->publication->posts->edges as $edge) {
  $node = $edge->node;

  $item = $feed->createNewItem();
  $item
    ->setTitle($node->title)
    ->setLink($node->url)
    ->setDate(new \DateTime($node->publishedAt))
    ->setDescription(htmlentities($node->content->html))
    ->setId($node->id);

  $feed->addItem($item);
}

$feed->printFeed();
