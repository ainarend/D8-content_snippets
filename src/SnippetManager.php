<?php
/**
 * @file
 * Contains Drupal\content_snippets\SnippetManager.
 */

namespace Drupal\content_snippets;

/**
 * Class SnippetManager
 * @package Drupal\content_snippets
 */
class SnippetManager {

  protected $snippets;

  public function __construct() {
    $this->snippets = $this->gatherSnippets();
  }

  public function getSnippets() {
    return $this->snippets;
  }

  /**
   * Invokes the snippets info hook for all modules to register their snippets.
   *
   * @return array
   */
  public function gatherSnippets() {

    $snippets = \Drupal::moduleHandler()->invokeAll('snippets_info');

    \Drupal::moduleHandler()->alter('snippets_info', $snippets);

    return $snippets;

  }

  /**
   * This looks through the snippet to find its arguments.
   *
   * @param $match
   * @return mixed
   */
  public function getSnippetWithArguments($snippet) {
    // Check if the snippet contains arguements.
    $args = [];
    if (preg_match("/^\[(" . implode("|", array_keys($this->snippets)) . ") ?(.*)\]$/i", $snippet, $matched_snippet)) {

      if (!empty($matched_snippet[2])) {

        $snippet = $matched_snippet[2];

        $pattern = '/(\\w+)\s*(\[])?=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';

        preg_match_all($pattern, $snippet, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
          foreach ($matches as $attr) {

            if ($attr[2] == '[]') {
              $args[$attr[1]] = explode(",", trim($attr[3], "\""));
            } else {
              $args[$attr[1]] = trim($attr[3], "\"");
            }

          }
        }

      }

    }

    return $args;
  }

  public function getPatternWithAllSnippets() {
    $snippets = $this->snippets;
    return '/\[(' . implode("|", array_keys($snippets)) . ')?.*(?:(?!\[]).)]/imU';
  }

  public function getSnippetReplaceData($snippet) {

    $replace_data = '';

    $args = $this->getSnippetWithArguments($snippet);

    //$snippet = ;

    $snippet_info = $this->snippets[$snippet[1]];

    if (array_key_exists('callback', $snippet_info) && function_exists($snippet_info['callback'])) {
      $replace_data = call_user_func($snippet_info['callback'], $args);
    }

    return $replace_data;

  }

}