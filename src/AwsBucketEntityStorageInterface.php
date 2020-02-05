<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface;

/**
 * Defines the storage handler class for Aws bucket entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Aws bucket entity entities.
 *
 * @ingroup aws_bucket_fs
 */
interface AwsBucketEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Aws bucket entity revision IDs for a specific Aws bucket entity.
   *
   * @param \Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface $entity
   *   The Aws bucket entity entity.
   *
   * @return int[]
   *   Aws bucket entity revision IDs (in ascending order).
   */
  public function revisionIds(AwsBucketEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Aws bucket entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Aws bucket entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

}
