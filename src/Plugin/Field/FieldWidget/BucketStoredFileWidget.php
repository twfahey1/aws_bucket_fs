<?php

namespace Drupal\aws_bucket_fs\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'bucket_stored_file_widget' widget.
 *
 * @FieldWidget(
 *   id = "bucket_stored_file_widget",
 *   module = "aws_bucket_fs",
 *   label = @Translation("Bucket stored file widget"),
 *   field_types = {
 *     "bucket_stored_file"
 *   }
 * )
 */
class BucketStoredFileWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $elements['bucket'] = [
      '#title' => t('Bucket'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'aws_bucket_entity',
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('The AWS Bucket to use.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'aws_bucket_fs/field-client-upload';

    $form['file_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => 'Bucket upload',
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

    return $element;
  }

}
