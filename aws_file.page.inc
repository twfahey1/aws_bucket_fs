<?php

/**
 * @file
 * Contains aws_file.page.inc.
 *
 * Page callback for Aws file entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Aws file templates.
 *
 * Default template: aws_file.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_aws_file(array &$variables) {
  // Fetch AwsFile Entity Object.
  $aws_file = $variables['elements']['#aws_file'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  // Generate URL for download.
  $bucket = $aws_file->get('field_bucket')->referencedEntities()[0]->label();
  $path = $aws_file->get('field_path')->first()->getString();
  if (isset($bucket) && isset($path)) {
    $download_url = \Drupal::service('aws_bucket_fs.manager')->getPresignedUrl('GetObject', 'us-east-2', $bucket, $path)->getUri();
    $variables['content']['download_url'] = $download_url;
  }
}
