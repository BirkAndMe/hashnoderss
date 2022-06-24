<?php

/**
 * Check and operator functions.
 */

/**
 * @todo Document function
 */
function operator__equal($filterValue, $values) {
  foreach ($values as $value) {
    if ($filterValue === $value) {
      return true;
    }
  }

  return false;
}

/**
 * @todo Document function
 */
function operator__not($filterValue, $values) {
  return !operator__equal($filterValue, $values);
}

/**
 * @todo Document function
 */
function operator__less($filterValue, $values) {
  foreach ($values as $value) {
    $equal = $value <=> $filterValue;
    if ($equal === -1) {
      return true;
    }
  }

  return false;
}

/**
 * @todo Document function
 */
function operator__greater($filterValue, $values) {
  foreach ($values as $value) {
    $equal = $value <=> $filterValue;
    if ($equal === 1) {

      return true;
    }
  }

  return false;
}

/**
 * @todo Document function
 */
function validateFilterString($filterString, $values) {
  $operator = $filterString[0];
  $filterValue = substr($filterString, 1);

  switch ($operator) {
    case OPERATOR_EQUAL:
      return operator__equal($filterValue, $values);
    case OPERATOR_NOT:
      return operator__not($filterValue, $values);
    case OPERATOR_LESS:
      return operator__less($filterValue, $values);
    case OPERATOR_GREATER:
      return operator__greater($filterValue, $values);
  }
}

/**
 * @todo Document function
 */
function validateFilterStrings($filterStrings, $values) {
  foreach ($filterStrings as $filterString) {
    if (!validateFilterString($filterString, $values)) {
      return $filterString;
    }
  }

  return true;
}
