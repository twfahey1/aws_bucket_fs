<?php

namespace Drupal\aws_bucket_fs\Form;

use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;

/**
 * Form controller for Aws file edit forms.
 *
 * @ingroup aws_bucket_fs
 */
class AwsFileForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The AWS Bucket manager service.
   *
   * @var \Drupal\aws_bucket_fs\AwsBucketFsManagerService
   */
  protected $awsBucketManager;

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  private $sessionManager;

  /**
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    $instance->awsBucketManager = $container->get('aws_bucket_fs.manager');
    $instance->tempstoreFactory = $container->get('tempstore.private');
    $instance->sessionManager = $container->get('session_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->tempStoreFactory = \Drupal::service('tempstore.private');
    $this->store = $this->tempStoreFactory->get('multistep_data');

    /* @var \Drupal\aws_bucket_fs\Entity\AwsFile $entity */
    $form = parent::buildForm($form, $form_state);

    $form['#attached']['library'][] = 'aws_bucket_fs/upload-to-s3-file-entity';

    $form['file_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => 'Bucket upload',
      '#attributes' => [
        'id' => 'file-fieldset-wrapper',
      ],
    ];

    if (!$this->entity->isNew()) {
      $form['file_fieldset']['edit_file'] = [
        '#type' => 'checkbox',
        '#title' => 'Replace file',
        '#attributes' => [
          'class' => [
            'replace-aws-file',
          ]
        ]
      ];
    }

    $form['react-component'] = [
      '#markup' => '<div id="basic-app"></div>',
    ];

    $form['file_fieldset']['file'] = [
      '#type' => 'file',
      '#attributes' => [
        'id' => 'theFile',
      ],
    ];

    $form['file_fieldset']['status'] = [
      '#prefix' => '<div id="status">Status',
      '#markup' => '
      <div class="progress">
            <div class="bar"></div>
            <div id="percent"></div>
      </div>',
      '#suffix' => '</div>',
    ];

    if (!$this->entity->isNew()) {
      $replace_is_checked_state = [
        'invisible' => [
          ':input[class="replace-aws-file form-checkbox"]' => ['checked' => FALSE],
        ],
        'visible' => [
          ':input[class="replace-aws-file form-checkbox"]' => ['checked' => TRUE],
        ],
      ];
      $form['file_fieldset']['file']['#states'] = $replace_is_checked_state;

    }

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::doAjaxSave', // don't forget :: when calling a class method.
      'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
      'wrapper' => 'file-fieldset-wrapper', // This element is updated with this AJAX callback.
      'progress' => [
        'type' => 'throbber',
        'message' => $this->t('Verifying entry...'),
      ],
      '#limit_validation_errors' => [],
    ];

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    return $form;
  }

  /**
   * Does an Ajax based save.
   *
   * This will trigger upload to S3 with desired values.
   * 
   * If the user is adding a new file, we need to do the upload to S3 after verifying permissions.
   * If the user is editing an existing file, we'll need to determine what they are changing.
   * If the want to edit the actual file, we should acknowledge they've added something new to 
   * the file element, and possibly remove the old file to do some cleanup.
   * 
   * If they are changing the file location, we'll have to move the file via S3 SDK, and
   * update the values accordingly.
   */
  public function doAjaxSave(array $form, FormStateInterface $form_state) {
    $file = $form_state->getValue('file');
    $all_files = $this->getRequest()->files->get('files', []);
    $operation = $form_state->getFormObject()->getOperation();
    if (empty($all_files)) {
      $file_is_present = FALSE;
    }
    else {
      $file_is_present = TRUE;
    }
    if ($file_is_present == FALSE && $operation == "add") {
      // No file is present in the file form element. We need a file to create new.
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand("Error", ['#markup' => "You need to add a file to proceed."]));
      return $response;
    }

    if ($file_is_present == TRUE) {
      $file = $all_files['file'];
      $local_file_path = $file->getClientOriginalName();
    }
    if ($operation == "edit") {
      $new_bucket_id = $form_state->getValue('field_bucket')[0]['target_id'];
      $new_bucket_entity = $this->entityTypeManager->getStorage('aws_bucket_entity')->load($new_bucket_id);
      /** @var \Drupal\aws_bucket_fs\Entity\AwsFile */
      $original_entity = $this->entityTypeManager->getStorage('aws_file')->load($this->entity->id());
      $original_bucket = $original_entity->getBucket();
      $region = "us-east-2";
      $original_key = $original_entity->getPath();
      $new_bucket = $new_bucket_entity->label();
      $new_key = $form_state->getValue('field_path')[0]['value'];
      $response = new AjaxResponse();

      if ($file_is_present) {
        // Editing with a new file, so upload it and delete the old file.
        $file_form_selector = '#' . $form['file_fieldset']['file']['#attributes']['id'];
        $response->addCommand(new InvokeCommand(NULL, 'deleteAndUpload', [
          $file_form_selector, $region, $original_bucket, $new_bucket, $original_key, $new_key,
        ]));
        $this->store->set('aws_upload_triggered', 1);
        return $response;
      }

      // Since the file itself isn't changing, just invoke a renameCallback.
      $response->addCommand(new InvokeCommand(NULL, 'renameCallback', [
        $region, $original_bucket, $new_bucket, $original_key, $new_key,
      ]));
      $this->store->set('aws_upload_triggered', 1);
      return $response;
    }

    if ($operation == "add") {
      $bucket_id = $form_state->getValue('field_bucket')[0]['target_id'];
      $bucket_entity = $this->entityTypeManager->getStorage('aws_bucket_entity')->load($bucket_id);
      $bucket = $bucket_entity->label();
      $path_to_store = $form_state->getValue('field_path')[0]['value'];

      // Get the file form selector, assumed to be an ID.
      $file_form_selector = '#' . $form['file_fieldset']['file']['#attributes']['id'];
      $response = new AjaxResponse();
      $response->addCommand(new InvokeCommand(NULL, 'uploadCallback', [
        $file_form_selector, $local_file_path, $bucket, $path_to_store,
      ]));
      $this->store->set('aws_upload_triggered', 1);
      return $response;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $upload_triggered = $this->store->get('aws_upload_triggered') ?? 0;

    if ($upload_triggered == 1) {
      $entity = $this->entity;

      // Save as a new revision if requested to do so.
      if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
        $entity->setNewRevision();

        // If a new revision is created, save the current user as revision author.
        $entity->setRevisionCreationTime($this->time->getRequestTime());
        $entity->setRevisionUserId($this->account->id());
      }
      else {
        $entity->setNewRevision(FALSE);
      }

      $status = parent::save($form, $form_state);

      switch ($status) {
        case SAVED_NEW:
          $this->messenger()->addMessage($this->t('Created the %label Aws file.', [
            '%label' => $entity->label(),
          ]));
          break;

        default:
          $this->messenger()->addMessage($this->t('Saved the %label Aws file.', [
            '%label' => $entity->label(),
          ]));
      }
      $this->store->delete('aws_upload_triggered');
      $form_state->setRedirect('entity.aws_file.canonical', ['aws_file' => $entity->id()]);
    }

  }

}
