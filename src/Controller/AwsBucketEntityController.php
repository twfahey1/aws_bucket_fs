<?php

namespace Drupal\aws_bucket_fs\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AwsBucketEntityController.
 *
 *  Returns responses for Aws bucket entity routes.
 */
class AwsBucketEntityController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a Aws bucket entity revision.
   *
   * @param int $aws_bucket_entity_revision
   *   The Aws bucket entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($aws_bucket_entity_revision) {
    $aws_bucket_entity = $this->entityTypeManager()->getStorage('aws_bucket_entity')
      ->loadRevision($aws_bucket_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('aws_bucket_entity');

    return $view_builder->view($aws_bucket_entity);
  }

  /**
   * Page title callback for a Aws bucket entity revision.
   *
   * @param int $aws_bucket_entity_revision
   *   The Aws bucket entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($aws_bucket_entity_revision) {
    $aws_bucket_entity = $this->entityTypeManager()->getStorage('aws_bucket_entity')
      ->loadRevision($aws_bucket_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $aws_bucket_entity->label(),
      '%date' => $this->dateFormatter->format($aws_bucket_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Aws bucket entity.
   *
   * @param \Drupal\aws_bucket_fs\Entity\AwsBucketEntityInterface $aws_bucket_entity
   *   A Aws bucket entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AwsBucketEntityInterface $aws_bucket_entity) {
    $account = $this->currentUser();
    $aws_bucket_entity_storage = $this->entityTypeManager()->getStorage('aws_bucket_entity');

    $build['#title'] = $this->t('Revisions for %title', ['%title' => $aws_bucket_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all aws bucket entity revisions") || $account->hasPermission('administer aws bucket entity entities')));
    $delete_permission = (($account->hasPermission("delete all aws bucket entity revisions") || $account->hasPermission('administer aws bucket entity entities')));

    $rows = [];

    $vids = $aws_bucket_entity_storage->revisionIds($aws_bucket_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\aws_bucket_fs\AwsBucketEntityInterface $revision */
      $revision = $aws_bucket_entity_storage->loadRevision($vid);
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $aws_bucket_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.aws_bucket_entity.revision', [
            'aws_bucket_entity' => $aws_bucket_entity->id(),
            'aws_bucket_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $aws_bucket_entity->link($date);
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
              'url' => Url::fromRoute('entity.aws_bucket_entity.revision_revert', [
                'aws_bucket_entity' => $aws_bucket_entity->id(),
                'aws_bucket_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.aws_bucket_entity.revision_delete', [
                'aws_bucket_entity' => $aws_bucket_entity->id(),
                'aws_bucket_entity_revision' => $vid,
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

    $build['aws_bucket_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
