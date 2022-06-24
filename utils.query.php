<?php

/**
 * Query helper functions.
 */

/**
 * Build the properties ready for the Hashnode API call.
 */
function buildQueryProperties($filter) {

  // Nest the filter values into an array using the property seperator.
  $properties = [];
  foreach ($filter as $property => $value) {
    $parts = explode(PROPERTY_SEPARATOR, $property);

    $child = &$properties;
    foreach ($parts as $part) {
      $child[$part] = $child[$part] ?? [];
      $child = &$child[$part];
    }
    unset($child);
  }

  // Prepare the properties for the Graph QL.
  $out = [];
  foreach ($properties as $property => $nested) {
    if (!empty($nested)) {
      $out[] = $property . ' ' . buildQueryProperties($nested);
    } else {
      $out[] = $property;
    }
  }

  return '{' . implode(', ', $out) . '}';
}