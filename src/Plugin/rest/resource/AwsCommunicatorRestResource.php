<?php

namespace Drupal\aws_bucket_fs\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Site\Settings;
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "aws_communicator_rest_resource",
 *   label = @Translation("Aws communicator rest resource"),
 *   uri_paths = {
 *     "create" = "/aws-crr/v1/endpoint"
 *   }
 * )
 */
class AwsCommunicatorRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('aws_bucket_fs');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * Responds to POST requests.
   *
   * @param string $payload
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($payload) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    // if (!$this->currentUser->hasPermission('access content')) {
    //     throw new AccessDeniedHttpException();
    // }

    $local_file_path = $payload['local_file_path'][0]['value'];
    $bucket = $payload['bucket'][0]['value'];
    $path_to_store = $payload['path_to_store'][0]['value'];

    $access_key = Settings::get('s3fs.access_key');
    $secret_key = Settings::get('s3fs.secret_key');
    $credentials = new Credentials($access_key, $secret_key);

    $s3Client = new S3Client([
      'version' => 'latest',
      'region'  => 'us-east-2',
      'credentials' => $credentials,
    ]);

    // This is an example of creating a put request. This URL generated
    // allows a client to post directly to the bucket. This bypasses
    // any server limitations on file size, timeouts, etc. We can pass
    // the URL to Javascript safely, and allow client to put large files.
    $cmd = $s3Client->getCommand('PutObject', [
      'Bucket' => $bucket,
      'Key' => $path_to_store,
    ]);

    $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
    $presigned_url = $request->getUri()->__toString();

    $return_payload = [
      'presigned_url' => $presigned_url,
      'path_to_save' => $path_to_store,
    ];

    return new ModifiedResourceResponse($return_payload, 200);
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $payload = ['foo'];
    return new ResourceResponse($payload, 200);
  }

}
