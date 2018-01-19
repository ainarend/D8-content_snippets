<?php

namespace Drupal\content_snippets\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_snippets",
 *   title = @Translation("Snippet Filter"),
 *   description = @Translation("Replace shortcode with awesome stuff"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class ContentSnippetFilter extends FilterBase{

  public function process($text, $langcode) {

    $snippets = $this->getAllSnippets();

    $pattern = '/\[(' . implode("|", $snippets) . ')?.*(?:(?!\[]).)]/imU';
    $depth = 0;

    while($depth < $this->settings['snippet_depth'] && preg_match_all($pattern, $text, $match)) {

      foreach ($match[0] as $snippet) {

        if (!isset($match[0])) {
          continue;
        }

        // Check if the snippet contains arguements.
        $args = [];
        if (preg_match("/^\[(" . implode("|", array_keys($snippets)) . ") ?(.*)\]$/i", $snippet, $match)) {

          if (!empty($match[2])) {
            $args = $this->getSnippetArguements($snippet, $match);
          }
        }

        $snippet_info = $snippets[$match[1]];

        if (array_key_exists('callback', $snippet_info) && function_exists($snippet_info['callback'])) {
          $replace_data = call_user_func($snippet_info['callback'], $args);
        }

        if (isset($replace_data)) {
          $renderer = \Drupal::service('renderer');
          $text = str_replace($snippet, $renderer->render($replace_data), $text);
        }
      }

      $depth++;

    };

    return new FilterProcessResult($text);
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {

    $form['snippet_depth'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#default_value' => $this->settings['snippet_depth'] ? $this->settings['snippet_depth'] : 4,
      '#description' => $this->t('Specify the snippet recursion depth'),
    ];

    return $form;

  }

  public function getAllSnippets() {

    $snippets = \Drupal::moduleHandler()->invokeAll('snippets_info');

    \Drupal::moduleHandler()->alter('snippets_info', $snippets);

    return $snippets;

  }

  public function getSnippetArguements($snippet, $match) {

    $pattern = '/(\\w+)\s*(\[])?=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';

    preg_match_all($pattern, $match[2], $matches, PREG_SET_ORDER);

    foreach ($matches as $attr) {

      if ($attr[2] == '[]') {
        $args[$attr[1]] = explode(",", trim($attr[3], "\""));
      }

      else {
        $args[$attr[1]] = trim($attr[3], "\"");
      }

    }

    return $args;
  }

}
