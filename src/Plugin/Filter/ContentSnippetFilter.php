<?php
/**
 * @file
 * Contains Drupal\content_snippets\Plugin\Filter\ContentSnippetFilter.
 */

namespace Drupal\content_snippets\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_snippets",
 *   title = @Translation("Content Snippet Filter"),
 *   description = @Translation("Replace shortcode with awesome stuff"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class ContentSnippetFilter extends FilterBase {

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

    $snippets = $this->getAllSnippets();

    $pattern = '/\[(' . implode("|", $snippets) . ')?.*(?:(?!\[]).)]/imU';
    $depth = 0;

    while($depth < $this->settings['snippet_depth'] && preg_match_all($pattern, $text, $matched_snippet)) {

      if (!empty($matched_snippet[0])) {
        // The matched_snippet contains an array where 0 is sthe full snippet with [ and ] included.
        foreach ($matched_snippet[0] as $snippet) {

          // Check if the snippet contains arguements.
          $args = [];
          if (preg_match("/^\[(" . implode("|", array_keys($snippets)) . ") ?(.*)\]$/i", $snippet, $matched_snippet)) {

            if (!empty($matched_snippet[2])) {

              $args = $this->getSnippetArguements($matched_snippet);

            }

          }

          $snippet_info = $snippets[$matched_snippet[1]];

          if (array_key_exists('callback', $snippet_info) && function_exists($snippet_info['callback'])) {
            $replace_data = call_user_func($snippet_info['callback'], $args);
          }

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
      '#default_value' => $this->settings['snippet_depth'] ? $this->settings['snippet_depth'] : 4,
      '#description' => $this->t('Specify the snippet recursion depth'),
    ];

    return $form;

  }

  /**
   * Invokes the snippets info hook for all modules to register their snippets.
   *
   * @return array
   */
  public function getAllSnippets() {

    $snippets = \Drupal::moduleHandler()->invokeAll('snippets_info');

    \Drupal::moduleHandler()->alter('snippets_info', $snippets);

    return $snippets;

  }

  /**
   * This looks thorugh the s
   *
   * @param $match
   * @return mixed
   */
  public function getSnippetArguements($snippet) {

    $pattern = '/(\\w+)\s*(\[])?=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';

    preg_match_all($pattern, $snippet, $matches, PREG_SET_ORDER);

    if (!empty($matches)) {
      foreach ($matches as $attr) {

        if ($attr[2] == '[]') {
          $args[$attr[1]] = explode(",", trim($attr[3], "\""));
        }

        else {
          $args[$attr[1]] = trim($attr[3], "\"");
        }

      }
    }

    return $args;
  }

}
