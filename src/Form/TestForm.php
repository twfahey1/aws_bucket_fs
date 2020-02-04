<?php

namespace Drupal\aws_bucket_fs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;

/**
 * Class TestForm.
 */
class TestForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\aws_bucket_fs\AwsBucketFsManagerService definition.
   * 
   * @var \Drupal\aws_bucket_fs\AwsBucketFsManagerService
   */
  protected $awsBucketFsManagerService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->awsBucketFsManagerService = $container->get('aws_bucket_fs.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // This is an example of getting an object out of the store.
    // The image in this case is in a folder "test", called "drupal-test.png".
    // It can't be accessed normally (the bucket is "private", by default), but
    // with the presigned request, we get an accessible URL that is valid for
    // a certain amount of time.
    $bucket = 'test-bucket-a4816';
    $test_image_key = 'test/drupal-test.png';
    $image_request_example = $this->awsBucketFsManagerService->getPresignedUrl('GetObject', 'us-east-2', $bucket, $test_image_key);
    $form['debug_get_image'] = [
      '#markup' => '<h3>Example retrieved image:</h3><img src="' . $image_request_example->getUri() . '"></img>',
    ];
  
    // Example of upload. The attached JS will send a request to our REST API
    // endpoint with the local user file information, and will initiate the
    // upload directly to S3.
    $form['#attached']['library'][] = 'aws_bucket_fs/client-upload';
    $form['hacky_markup'] = [
      '#markup' => '<h4>Upload a file to bucket</h4><br>
      <form id="s3form" method="post" enctype="multipart/form-data">
        <input id="theFile" type="file" name="files[]" multiple />
      </form>
      <div class="progress">
          <div class="bar"></div >
          <div id="percent">0%</div >
      </div>

      <div id="status"></div>
      ',
      '#allowed_tags' => [
        'form',
        'input',
        'div',
        'h4',
        'br',
      ],
    ];

    $form['submit'] = [
      '#markup' => '<div class="button" id="submitupload"><h4>Submit upload</h4></div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
