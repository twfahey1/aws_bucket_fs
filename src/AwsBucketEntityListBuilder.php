<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Aws bucket entity entities.
 *
 * @ingroup aws_bucket_fs
 */
class AwsBucketEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Aws bucket entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\aws_bucket_fs\Entity\AwsBucketEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.aws_bucket_entity.edit_form',
      ['aws_bucket_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
