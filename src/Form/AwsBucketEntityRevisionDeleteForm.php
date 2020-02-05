<?php

namespace Drupal\aws_bucket_fs\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Aws bucket entity revision.
 *
 * @ingroup aws_bucket_fs
 */
class AwsBucketEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Aws bucket entity revision.
   *
   * @var \Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface
   */
  protected $revision;

  /**
   * The Aws bucket entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $awsBucketEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->awsBucketEntityStorage = $container->get('entity_type.manager')->getStorage('aws_bucket_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aws_bucket_entity_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.aws_bucket_entity.version_history', ['aws_bucket_entity' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $aws_bucket_entity_revision = NULL) {
    $this->revision = $this->AwsBucketEntityStorage->loadRevision($aws_bucket_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->AwsBucketEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Aws bucket entity: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Aws bucket entity %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.aws_bucket_entity.canonical',
       ['aws_bucket_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {aws_bucket_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.aws_bucket_entity.version_history',
         ['aws_bucket_entity' => $this->revision->id()]
      );
    }
  }

}
