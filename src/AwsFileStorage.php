<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class AwsFileStorage extends SqlContentEntityStorage implements AwsFileStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AwsFileInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {aws_file_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {aws_file_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

}
