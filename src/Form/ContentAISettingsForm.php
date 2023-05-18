<?php

namespace Drupal\contentai\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Configure contentai client settings for this site.
 */
class ContentAISettingsForm extends ConfigFormBase {

  /**
  * Config settings.
  *
  * @var string
  */
  const SETTINGS = 'contentai.settings';
  const MODEL = 0;
  const TEMPERATURE = 1;
  const QUESTION = 3;
  const SELECTOR = 2;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contentai_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['option_key'] = [
      '#required' => TRUE,
      '#type' => 'select',
      '#title' => t('Select the option below to configuration'),
      '#options' => [
        'title' => t('Title'),
        'keywords' => t('Keywords'),
        'description' => t('Description')
      ],
      '#ajax' => [
        'event' => 'change',
        'callback' => '::contentai_content_node_suggest'
      ],
    ];

    $form['option_model'] = [
      '#required' => TRUE,
      '#type' => 'select',
      '#title' => t('Select a model'),
      '#options' => [
        'text-babbage-001' => t('text-babbage-001'),
        'text-curie-001' => t('text-curie-001'),
        'text-davinci-003' => t('text-davinci-003')
      ],
    ];

    $form['option_temperature'] = [
      '#required' => TRUE,
      '#type' => 'select',
      '#title' => t('Select a temperature'),
      '#options' => [
        '0' => t('0'),
        '0,4' => t('0,4'),
        '1' => t('1')
      ],
    ];

    $form['meta_tag_field'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Meta tag field selector'),
    ];

    $form['question'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Question'),
      '#description' => $this->t('Use {body} to define where the content is added on question.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable(self::SETTINGS);
    $config->set($form_state->getValue('option_key'), [$form_state->getValue('option_model'), $form_state->getValue('option_temperature'), $form_state->getValue('meta_tag_field'), $form_state->getValue('question')]);
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * The AJAX callback for suggesting taxonomy.
   *
   * @param array $form
   *   The node form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The HTML response.
   */
  function contentai_content_node_suggest(array &$form, FormStateInterface $form_state) {
    $select = $form_state->getValue(['option_key']);

    $contentAIService = \Drupal::service('contentai.contentAIService');

    $response = new AjaxResponse();
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable(self::SETTINGS);

    $values = $config->get($select);
    $response->addCommand(new InvokeCommand('#edit-option-model', 'val', [$values[self::MODEL]]));
    $response->addCommand(new InvokeCommand('#edit-option-temperature', 'val', [$values[self::TEMPERATURE]]));
    $response->addCommand(new InvokeCommand('#edit-question', 'val', [$values[self::QUESTION]]));
    $response->addCommand(new InvokeCommand('#edit-meta-tag-field', 'val', [$values[self::SELECTOR]]));

    return $response;
  }
}
