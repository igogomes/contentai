<?php

namespace Drupal\contentai\Services;

use Drupal\openai\Utility;

/**
 * Service to help get content to OpenAI.
 */
class ContentAIService {

  const MODEL = 0;
  const TEMPERATURE = 1;
  const QUESTION = 3;

  /**
   * Validating API AI connection.
   */
  public function validateConnectAPI() {
    $client = \Drupal::service('openai.client');

    $options = [
      'model' => 'text-davinci-003',
      'prompt' => '',
      'temperature' => 1,
      'max_tokens' => 2048,
    ];

    $response = $client->completions()->create($options);
    $result = $response->toArray();

    return json_encode($result["choices"][0]["text"]);
  }

  /**
   * Get AI content.
   */
  public function processContent(string $body = '', string $question = '', Array $options = []) {
    $client = \Drupal::service('openai.client');

    if(count($options) === 0) {
      $options = [
        'model' => 'text-davinci-003',
        'prompt' => str_replace('{body}', $body, $question) ,
        'temperature' => 1,
        'max_tokens' => 2048,
      ];
    }

    $response = $client->completions()->create($options);
    $result = $response->toArray();

    return trim($result["choices"][0]["text"]) ?? t('No terms could be generated from the provided input.');
  }

  /**
   * Get AI content.
   */
  public function getContent(string $body = '', Array $fields = [], string $custom_question = '') {
    $fields_texts = [];
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable('contentai.settings');

    foreach ($fields as $field) {
      $question = ($custom_question != '') ? $custom_question : $config->get($field)[self::QUESTION];

      $fields_texts[$field] = $this->processContent($body, $question, [
        'model' => $config->get($field)[self::MODEL],
        'prompt' => str_replace('{body}', $body, $question) ,
        'temperature' => intval($config->get($field)[self::TEMPERATURE]),
        'max_tokens' => 2048,
      ]);
    }

    return $fields_texts;
  }
}
