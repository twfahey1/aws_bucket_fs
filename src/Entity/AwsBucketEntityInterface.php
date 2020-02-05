<?php

namespace Drupal\aws_bucket_fs\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Aws bucket entity entities.
 *
 * @ingroup aws_bucket_fs
 */
interface AwsBucketEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Aws bucket entity name.
   *
   * @return string
   *   Name of the Aws bucket entity.
   */
  public function getName();

  /**
   * Sets the Aws bucket entity name.
   *
   * @param string $name
   *   The Aws bucket entity name.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface
   *   The called Aws bucket entity entity.
   */
  public function setName($name);

  /**
   * Gets the Aws bucket entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Aws bucket entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Aws bucket entity creation timestamp.
   *
   * @param int $timestamp
   *   The Aws bucket entity creation timestamp.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface
   *   The called Aws bucket entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Aws bucket entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Aws bucket entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface
   *   The called Aws bucket entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Aws bucket entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Aws bucket entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface
   *   The called Aws bucket entity entity.
   */
  public function setRevisionUserId($uid);

}
