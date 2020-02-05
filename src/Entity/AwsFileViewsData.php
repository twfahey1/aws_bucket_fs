<?php

namespace Drupal\aws_bucket_fs\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Aws file entities.
 */
class AwsFileViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
