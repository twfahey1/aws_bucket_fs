<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Aws file entity.
 *
 * @see \Drupal\aws_bucket_fs\Entity\AwsFile.
 */
class AwsFileAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\aws_bucket_fs\Entity\AwsFileInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished aws file entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published aws file entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit aws file entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete aws file entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add aws file entities');
  }


}
