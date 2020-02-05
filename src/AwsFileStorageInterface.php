<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\aws_bucket_fs\Entity\AwsFileInterface;

/**
 * Defines the storage handler class for Aws file entities.
 *
 * This extends the base storage class, adding required special handling for
 * Aws file entities.
 *
 * @ingroup aws_bucket_fs
 */
interface AwsFileStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Aws file revision IDs for a specific Aws file.
   *
   * @param \Drupal\aws_bucket_fs\Entity\AwsFileInterface $entity
   *   The Aws file entity.
   *
   * @return int[]
   *   Aws file revision IDs (in ascending order).
   */
  public function revisionIds(AwsFileInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Aws file author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Aws file revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

}
