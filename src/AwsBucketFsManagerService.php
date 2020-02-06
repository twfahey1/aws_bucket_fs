<?php

namespace Drupal\aws_bucket_fs;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;

/**
 * Class AwsBucketFsManagerService.
 */
class AwsBucketFsManagerService implements AwsBucketFsManagerServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AwsBucketFsManagerService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Retrieves a presigned URL for a bucket.
   *
   * @param string $operation
   *   Can be put, get, post, patch.
   * @param string $bucket
   *   The bucket to use.
   * @param string $key
   *   The key to use, often the file name. e.g. "test/foo.txt"
   *   would be the foo.txt insidfe the test folder.
   */
  public function getPresignedUrl($operation, $region, $bucket, $key) {
    if ($this->validateRequest($operation, $region, $bucket, $key)) {
      try {
        $access_key = Settings::get('s3fs.access_key');
        $secret_key = Settings::get('s3fs.secret_key');
        $credentials = new Credentials($access_key, $secret_key);
        $s3Client = new S3Client([
          'version' => 'latest',
          'region'  => $region,
          'credentials' => $credentials,
        ]);
        $cmd = $s3Client->getCommand($operation, [
          'Bucket' => $bucket,
          'Key' => $key,
        ]);
        $presigned_url = $s3Client->createPresignedRequest($cmd, '+20 minutes');
        return $presigned_url;
      }
      catch (S3Exception $e) {
        \Drupal::logger('aws_bucket_fs')->error($e->getMessage());
      }
      catch (\Exception $e) {
        \Drupal::logger('aws_bucket_fs')->error($e->getMessage());
      }
    }
  }

  /**
   * Validate if upload is allowed.
   */
  public function validateRequest($operation, $region, $bucket, $key) {
    $user = \Drupal::service('current_user');
    // Todo: define permissions based on params.
    return TRUE;
  }

}
