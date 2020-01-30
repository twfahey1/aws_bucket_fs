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

    // TODO: Hardcoded bucket name here.
    $bucket = 'test-bucket-a4816';
    $keyname = $form_state->getValue('name_of_upload');
    $s3Client = new S3Client([
      'version' => 'latest',
      'region'  => 'us-east-2',
      'credentials' => $credentials,
    ]);

    // This is an example of getting an object out of the store.
    // The image in this case is in a folder "test", called "drupal-test.png".
    // It can't be accessed normally (the bucket is "private", by default), but
    // with the presigned request, we get an accessible URL that is valid for
    // a certain amount of time.
    $cmd = $s3Client->getCommand('GetObject', [
      'Bucket' => $bucket,
      'Key' => 'test/drupal-test.png',
    ]);

    $image_request_example = $s3Client->createPresignedRequest($cmd, '+20 minutes');
    $form['debug_get_image'] = [
      '#markup' => '<h3>Example retrieved image:</h3><img src="' . $image_request_example->getUri() . '">',
    ];
  
    // This is an example of creating a put request. This URL generated
    // allows a client to post directly to the bucket. This bypasses
    // any server limitations on file size, timeouts, etc. We can pass
    // the URL to Javascript safely, and allow client to put large files.
    $cmd = $s3Client->getCommand('PutObject', [
      'Bucket' => $bucket,
      'Key' => 'test/foobar.txt',
    ]);

    $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
    $presigned_url = $request->getUri();

    $form['debug'] = [
      '#markup' => 'PutObject requestUri: ' . $presigned_url,
    ];

    $form['#attached']['library'][] = 'aws_bucket_fs/client-upload';
    $form['#attached']['drupalSettings']['presignedUrl'] = $presigned_url->__toString();

    $form['reactwidget'] = [
      '#markup' => '<h3>testing react widget</h3><div id="root"></div>',
    ];

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
