<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Aws file entities.
 *
 * @ingroup aws_bucket_fs
 */
class AwsFileListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Aws file ID');
    $header['name'] = $this->t('Name');
    $header['path'] = $this->t('Path');
    $header['download'] = $this->t('Download link');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\aws_bucket_fs\Entity\AwsFile $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.aws_file.edit_form',
      ['aws_file' => $entity->id()]
    );
    $row['path'] = $entity->getPath();
    $row['download'] = new FormattableMarkup('<a href="@link">Download</a>', ['@link' => $entity->getDownloadUrl()]);
    return $row + parent::buildRow($entity);
  }

}
