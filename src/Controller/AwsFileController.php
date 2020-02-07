<?php

namespace Drupal\aws_bucket_fs\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\aws_bucket_fs\Entity\AwsFileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AwsFileController.
 *
 *  Returns responses for Aws file routes.
 */
class AwsFileController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Aws file revision.
   *
   * @param int $aws_file_revision
   *   The Aws file revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($aws_file_revision) {
    $aws_file = $this->entityTypeManager()->getStorage('aws_file')
      ->loadRevision($aws_file_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('aws_file');

    return $view_builder->view($aws_file);
  }

  /**
   * Page title callback for a Aws file revision.
   *
   * @param int $aws_file_revision
   *   The Aws file revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($aws_file_revision) {
    $aws_file = $this->entityTypeManager()->getStorage('aws_file')
      ->loadRevision($aws_file_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $aws_file->label(),
      '%date' => $this->dateFormatter->format($aws_file->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Aws file.
   *
   * @param \Drupal\aws_bucket_fs\Entity\AwsFileInterface $aws_file
   *   A Aws file object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AwsFileInterface $aws_file) {
    $account = $this->currentUser();
    $aws_file_storage = $this->entityTypeManager()->getStorage('aws_file');

    $build['#title'] = $this->t('Revisions for %title', ['%title' => $aws_file->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all aws file revisions") || $account->hasPermission('administer aws file entities')));
    $delete_permission = (($account->hasPermission("delete all aws file revisions") || $account->hasPermission('administer aws file entities')));

    $rows = [];

    $vids = $aws_file_storage->revisionIds($aws_file);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\aws_bucket_fs\AwsFileInterface $revision */
      $revision = $aws_file_storage->loadRevision($vid);
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $aws_file->getRevisionId()) {
          $link = $this->l($date, new Url('entity.aws_file.revision', [
            'aws_file' => $aws_file->id(),
            'aws_file_revision' => $vid,
          ]));
        }
        else {
          $link = $aws_file->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => Url::fromRoute('entity.aws_file.revision_revert', [
                'aws_file' => $aws_file->id(),
                'aws_file_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.aws_file.revision_delete', [
                'aws_file' => $aws_file->id(),
                'aws_file_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          
        }

        $rows[] = $row;
    }

    $build['aws_file_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
