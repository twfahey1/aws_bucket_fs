<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class AwsBucketEntityStorage extends SqlContentEntityStorage implements AwsBucketEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AwsBucketEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {aws_bucket_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {aws_bucket_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

}
