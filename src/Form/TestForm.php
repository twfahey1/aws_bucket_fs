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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
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
    $access_key = Settings::get('s3fs.access_key');
    $secret_key = Settings::get('s3fs.secret_key');
    $credentials = new Credentials($access_key, $secret_key);

    $bucket = 'test-bucket-a4816';
    $keyname = $form_state->getValue('name_of_upload');
    $s3Client = new S3Client([
      'version' => 'latest',
      'region'  => 'us-east-2',
      'credentials' => $credentials,
    ]);
  
    $cmd = $s3Client->getCommand('PutObject', [
      'Bucket' => $bucket,
      'Key' => 'test/drupal-test.png',
    ]);

    $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
    $presigned_url = $request->getUri();

    $form['debug'] = [
      '#markup' => 'requestUri: ' . $presigned_url,
    ];

    $form['#attached']['library'][] = 'aws_bucket_fs/client-upload';
    $form['#attached']['drupalSettings']['presignedUrl'] = $presigned_url->__toString();

    $form['submit'] = [
      '#markup' => '<div id="submitupload"><h4>Submit upload</h4></div>',
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
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }

    $access_key = Settings::get('s3fs.access_key');
    $secret_key = Settings::get('s3fs.secret_key');
    $credentials = new Credentials($access_key, $secret_key);

    $bucket = 'test-bucket-a4816';
    $keyname = $form_state->getValue('name_of_upload');
    $s3 = new S3Client([
      'version' => 'latest',
      'region'  => 'us-east-2',
      'credentials' => $credentials,
    ]);

    try {
        // Upload data.
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => 'test.html',
            'Body'   => 'Hello, world!',
            'ACL'    => 'public-read'
        ]);

        // Print the URL to the object.
        echo $result['ObjectURL'] . PHP_EOL;
    } catch (S3Exception $e) {
        drupal_set_message($e->getMessage());
    }
  }

}
