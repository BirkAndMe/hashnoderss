<?php

/**
 * Normalizer functions.
 */

/**
 * @todo Document function
 */
function normalizeFilter($filter) {
  foreach ($filter as &$values) {
    if (!is_array($values)) {
      $values = [$values];
    }
    foreach ($values as &$value) {
      $value = normalizeFilterValue($value);
    }
    unset($value);
  }
  unset($values);

  return $filter;
}

/**
 * @todo Document function
 */
function normalizeFilterValue($value) {
  if (!in_array($value[0], [OPERATOR_NOT, OPERATOR_EQUAL, OPERATOR_LESS, OPERATOR_GREATER])) {
    $value = OPERATOR_EQUAL . $value;
  }

  return $value;
}

/**
 * @todo Document function
 */
function getNestedValue($parts, $parent) {
  $path = array_shift($parts);
  $values = $parent->{$path};

  if (is_object($values)) {
    return getNestedValue($parts, $values);
  }

  if (is_array($values)) {
    foreach ($values as $valueKey => $value) {
      if (is_object($value)) {
        $values[$valueKey] = getNestedValue($parts, $value);
      }
    }
  }

  return $values;
}

/**
 * @todo Document function
 */
function getItemValue($propertyPath, $item) {
  $parts = explode(PROPERTY_SEPARATOR, $propertyPath);
  $value = getNestedValue($parts, $item);

  if (!is_array($value)) {
    $value = [$value];
  }

  return $value;
}

/**
 * @todo Document function
 */
function normalizeApiResponseProperties($filter, $item) {
  $properties = [];

  foreach ($filter as $propertyPath => $values) {
    $properties[$propertyPath] = getItemValue($propertyPath, $item);
  }

  return $properties;
}
