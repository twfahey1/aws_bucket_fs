<?php

namespace Drupal\aws_bucket_fs\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Aws file entities.
 *
 * @ingroup aws_bucket_fs
 */
interface AwsFileInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Aws file name.
   *
   * @return string
   *   Name of the Aws file.
   */
  public function getName();

  /**
   * Sets the Aws file name.
   *
   * @param string $name
   *   The Aws file name.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsFileInterface
   *   The called Aws file entity.
   */
  public function setName($name);

  /**
   * Gets the Aws file creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Aws file.
   */
  public function getCreatedTime();

  /**
   * Sets the Aws file creation timestamp.
   *
   * @param int $timestamp
   *   The Aws file creation timestamp.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsFileInterface
   *   The called Aws file entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Aws file revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Aws file revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsFileInterface
   *   The called Aws file entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Aws file revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Aws file revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsFileInterface
   *   The called Aws file entity.
   */
  public function setRevisionUserId($uid);

}
