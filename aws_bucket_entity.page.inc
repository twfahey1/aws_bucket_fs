<?php

/**
 * @file
 * Contains aws_bucket_entity.page.inc.
 *
 * Page callback for Aws bucket entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Aws bucket entity templates.
 *
 * Default template: aws_bucket_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_aws_bucket_entity(array &$variables) {
  // Fetch AwsBucketEntity Entity Object.
  $aws_bucket_entity = $variables['elements']['#aws_bucket_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
