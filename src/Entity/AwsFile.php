<?php

namespace Drupal\aws_bucket_fs\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

/**
 * Defines the Aws file entity.
 *
 * @ingroup aws_bucket_fs
 *
 * @ContentEntityType(
 *   id = "aws_file",
 *   label = @Translation("Aws file"),
 *   handlers = {
 *     "storage" = "Drupal\aws_bucket_fs\AwsFileStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\aws_bucket_fs\AwsFileListBuilder",
 *     "views_data" = "Drupal\aws_bucket_fs\Entity\AwsFileViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\aws_bucket_fs\Form\AwsFileForm",
 *       "add" = "Drupal\aws_bucket_fs\Form\AwsFileForm",
 *       "edit" = "Drupal\aws_bucket_fs\Form\AwsFileForm",
 *       "delete" = "Drupal\aws_bucket_fs\Form\AwsFileDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\aws_bucket_fs\AwsFileHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_bucket_fs\AwsFileAccessControlHandler",
 *   },
 *   base_table = "aws_file",
 *   revision_table = "aws_file_revision",
 *   revision_data_table = "aws_file_field_revision",
 *   translatable = FALSE,
 *   admin_permission = "administer aws file entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/aws_bucket_fs/aws_file/{aws_file}",
 *     "add-form" = "/admin/aws_bucket_fs/aws_file/add",
 *     "edit-form" = "/admin/aws_bucket_fs/aws_file/{aws_file}/edit",
 *     "delete-form" = "/admin/aws_bucket_fs/aws_file/{aws_file}/delete",
 *     "version-history" = "/admin/aws_bucket_fs/aws_file/{aws_file}/revisions",
 *     "revision" = "/admin/aws_bucket_fs/aws_file/{aws_file}/revisions/{aws_file_revision}/view",
 *     "revision_revert" = "/admin/aws_bucket_fs/aws_file/{aws_file}/revisions/{aws_file_revision}/revert",
 *     "revision_delete" = "/admin/aws_bucket_fs/aws_file/{aws_file}/revisions/{aws_file_revision}/delete",
 *     "collection" = "/admin/aws_bucket_fs/aws_file",
 *   },
 *   field_ui_base_route = "aws_file.settings"
 * )
 */
class AwsFile extends EditorialContentEntityBase implements AwsFileInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no revision author has been set explicitly,
    // make the aws_file owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * Get the download URL for this file.
   */
  public function getDownloadUrl() {
    try {
      $bucket = $this->getBucket();
      $path = $this->getPath();
      $image_request = \Drupal::service('aws_bucket_fs.manager')->getPresignedUrl('GetObject', 'us-east-2', $bucket, $path);
      $image_url = $image_request->getUri();
      return $image_url;
    }
    catch (\Error $e) {
      return NULL;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Return the bucket.
   */
  public function getBucket() {
    return $this->get('field_bucket')->referencedEntities()[0]->label();
  }

  /**
   * Return the path.
   */
  public function getPath() {
    return $this->get('field_path')->first()->getString();
  }
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Aws file entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Aws file entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Aws file is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
