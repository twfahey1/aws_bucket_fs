<?php

namespace Drupal\aws_bucket_fs;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AwsBucketFsManagerService.
 */
class AwsBucketFsManagerService implements AwsBucketFsManagerServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AwsBucketFsManagerService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

}
