<?php

namespace Drupal\contentai\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * The Translation Form.
 */
class TranslationAIForm extends FormBase {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The route service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $route;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\auto_node_translate\Form\TranslationForm object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The route service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The Current User service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module_handler service.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config,
    CurrentRouteMatch $route_match,
    TimeInterface $time,
    AccountProxyInterface $current_user,
    ModuleHandlerInterface $module_handler
  ) {
    $this->languageManager = $language_manager;
    $this->config = $config;
    $this->route = $route_match;
    $this->time = $time;
    $this->currentUser = $current_user;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('datetime.time'),
      $container->get('current_user'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contentai_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $languages = $this->languageManager->getLanguages();

    $form['translate'] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Languages to Translate'),
      '#closed' => FALSE,
      '#tree' => TRUE,
    ];

    foreach ($languages as $language) {
      $languageId = $language->getId();

      if($languageId !== $node->langcode->value) {
        $label = ($node->hasTranslation($languageId)) ? $this->t('overwrite translation') : $this->t('new translation');
        
        $form['translate'][$languageId] = [
          '#type' => 'checkbox',
          '#title' => $this->t('@lang (@label)', [
            '@lang' => $language->getName(),
            '@label' => $label,
          ]),
        ];
      }
    }

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Translate'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $connectAIService = \Drupal::service('contentai.contentAIService');
    $testConnection = $connectAIService->validateConnectAPI();

    if(empty($testConnection)) {
      $this->messenger()->addError('Error, please verify if API key is configured properly or server is available.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->route->getParameter('node');
    $translations = $form_state->getValues()['translate'];

    foreach ($translations as $lid => $value) {
      if($value) {
        $this->autoNodeTranslateNode($node, $lid);
      }
    }

      $form_state->setRedirect('system.admin_content');
  }

  /**
   * Translates node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to translate.
   * @param mixed $languageId
   *   The language id.
   */
  public function autoNodeTranslateNode(Node $node, $languageId) {
    $languageFrom = $node->langcode->value;
    $fields = $node->getFields();
    $node_trans = $this->getTranslatedNode($node, $languageId);
    $translatedTypes = $this->getTextFields();

    foreach ($fields as $field) {
      $fieldType = $field->getFieldDefinition()->getType();
      $fieldName = $field->getName();
  
      if($fieldName == "title" || $fieldName == "body") {
        if(in_array($fieldType, $translatedTypes)) {
          $languageName = locale_get_display_language($languageId, 'en');
          $translatedValue = $this->translateTextField($field, $fieldType, $fieldName, $languageName);
          $node_trans->set($fieldName, $translatedValue);
          $node_trans->set('status', Node::NOT_PUBLISHED);
        }
      }
    }

    $success_message = "Translation for $languageName done successfully.";

    $this->messenger()->addStatus($success_message);

    $node->setNewRevision(TRUE);
    $node->setRevisionCreationTime($this->time->getRequestTime());
    $node->setRevisionUserId($this->currentUser->id());
    $node->save();
  }

  /**
   * Gets or adds translated node.
   *
   * @param mixed $node
   *   The node.
   * @param mixed $languageId
   *   The language id.
   *
   * @return mixed
   *   the translated node.
   */
  public function getTranslatedNode(&$node, $languageId) {
    return $node->hasTranslation($languageId) ? $node->getTranslation($languageId) : $node->addTranslation($languageId);
  }

  /**
   * Translates text field.
   *
   * @param mixed $field
   *   The field to translate.
   * @param string $fieldType
   *   The field type.
   * @param mixed $fieldName
   *   The field name that will be translated.
   * @param mixed $languageName
   *   The language that will be used for translation.
   */
  public function translateTextField($field, $fieldType, $fieldName, $languageName) {  
    $translatedValue = [];
    $values = $field->getValue();

    foreach ($values as $key => $text) {
      if(!empty($text['value'])) {
        $textToTranslate = $text['value'];

        if($fieldName === "title") {
          $question = "Get the $languageName translation of the text below and remove the HTML tags: '{body}'";
        }
        elseif($fieldName === "body") {
          $question = "Get the $languageName translation of the text below and do not remove the HTML tags: '{body}'";
        }
        else {
          $question = "Get the $languageName translation of the text below: '{body}'";
        }

        $contentAIService = \Drupal::service('contentai.contentAIService');
        $translatedText = $contentAIService->processContent($textToTranslate, $question);

        if(in_array($fieldType, ['string', 'text']) && (strlen($translatedText) > 255)) {
          $translatedText = mb_substr($translatedText, 0, 255);
        }

        $translatedValue[$key]['value'] = $translatedText;

        if(isset($text['format'])) {
          $translatedValue[$key]['format'] = $text['format'];
        } 
      }
      else {
        $translatedValue[$key] = [];
      }
    }

    return $translatedValue;
  }

  /**
   * Returns text fields.
   */
  public function getTextFields() {
    return [
      'string',
      'string_long',
      'text',
      'text_long',
      'text_with_summary',
    ];
  }
}
