<?php

/**
 * Check and operator functions.
 */

function debug($label, $message) {
  if (!is_string($message)) {
    $message = json_encode($message, JSON_PRETTY_PRINT);
  }

  echo '<style>details { margin-bottom: 1em; } summary { list-style: none;} pre { max-height: 50vh; overflow: auto; word-wrap: break-word; white-space: pre-wrap; background-color: #eee; margin: 0em 0; padding: 1em;} .label { font-weight: bold; background-color: #ddd; }</style>';

  echo "<details><summary><pre class='label'>$label</pre></summary><pre>$message</pre></details>";
}