<?php
/**
 * @file
 * Contains Drupal\content_snippets\Plugin\Filter\ContentSnippetFilter.
 */

namespace Drupal\content_snippets\Plugin\Filter;

use Drupal\content_snippets\SnippetManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Filter(
 *   id = "filter_snippets",
 *   title = @Translation("Content Snippet Filter"),
 *   description = @Translation("Replace shortcode with awesome stuff"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class ContentSnippetFilter extends FilterBase implements ContainerFactoryPluginInterface {

  protected $snippetManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SnippetManager $snippetManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->snippetManager = $snippetManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('snippet_manager')
    );
  }
    /**
   * Filtering for snippets.
   *
   * This method looks through the text to find snippets (with possible arguements)
   * and replaces the snippet witt the response provided by snippet's callback method.
   *
   * @param string $text
   * @param string $langcode
   * @return FilterProcessResult
   */
  public function process($text, $langcode) {

    $snippets = $this->snippetManager->getSnippets();

    $pattern = $this->snippetManager->getPatternWithAllSnippets();
    $depth = 0;

    while($depth < $this->settings['snippet_depth'] && preg_match_all($pattern, $text, $matched_snippet)) {

      if (!empty($matched_snippet[0])) {
        // The matched_snippet contains an array where 0 is the full snippet with [ and ] included.
        foreach ($matched_snippet[0] as $snippet) {

          $replace_data = $this->snippetManager->getSnippetReplaceData($snippet);

          if (isset($replace_data)) {
            $renderer = \Drupal::service('renderer');
            $text = str_replace($snippet, $renderer->render($replace_data), $text);
          }
        }
      }

      $depth++;

    };

    return new FilterProcessResult($text);
  }

  /**
   * Allows defining the maximum allowed recursion of snippets.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $form['snippet_depth'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#default_value' => isset($this->settings['snippet_depth']) ? $this->settings['snippet_depth'] : 4,
      '#description' => $this->t('Specify the snippet recursion depth'),
    ];

    return $form;

  }

}
