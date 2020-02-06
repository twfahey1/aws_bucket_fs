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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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

    $form['file'] = [
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

    $form['file_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => array('::uploadToS3'),
      '#ajax' => [
        'callback' => '::uploadFile',
        'wrapper' => 'file-fieldset-wrapper',
      ],
    ];

    $form['submit'] = [
      '#markup' => '<div class="button" id="submitupload"><h4>Submit upload</h4></div>',
    ];

    $form['actions']['submit']['#submit'] = [];
    $form['actions']['submit']['#ajax'] = [
      'callback' => '::doAjaxSave', // don't forget :: when calling a class method.
      //'callback' => [$this, 'myAjaxCallback'], //alternative notation
      'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
      'wrapper' => 'file-fieldset-wrapper', // This element is updated with this AJAX callback.
      'progress' => [
        'type' => 'throbber',
        'message' => $this->t('Verifying entry...'),
      ],
      '#limit_validation_errors' => array(),
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
   */
  public function doAjaxSave(array $form, FormStateInterface $form_state) {
    $file = $form_state->getValue('file');
    $all_files = $this->getRequest()->files->get('files', []);
    $file = $all_files['file'];
    $file_path = $file->getRealPath();
    // TODO: Confirm via service / permissions user can upload here.
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'uploadCallback', ['This is the new text!']));
    return $response;
  }

  /**
   * Does an Ajax based save.
   *
   * This will trigger upload to S3 with desired values.
   */
  public function doSave(array $form, FormStateInterface $form_state) {
    // If we want to execute AJAX commands our callback needs to return
    // an AjaxResponse object. let's create it and add our commands.
    $response = 'foo';

    // Finally return the AjaxResponse object.
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
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
    $form_state->setRedirect('entity.aws_file.canonical', ['aws_file' => $entity->id()]);
  }

}
