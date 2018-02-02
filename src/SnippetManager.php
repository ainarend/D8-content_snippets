<?php
/**
 * @file
 * Contains Drupal\content_snippets\SnippetManager.
 */

namespace Drupal\content_snippets;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Class SnippetManager
 * @package Drupal\content_snippets
 */
class SnippetManager {

  /**
   * @var RendererInterface
   */
  public $renderer;

  /**
   * @var array
   */
  protected $snippets;

  /**
   * @var ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * SnippetManager constructor.
   * @param RendererInterface $renderer
   * @param ModuleHandlerInterface $moduleHandler
   */
  public function __construct(RendererInterface $renderer, ModuleHandlerInterface $moduleHandler) {
    $this->renderer = $renderer;
    $this->moduleHandler = $moduleHandler;

    $this->snippets = $this->gatherSnippets();
  }

  /**
   * Gets the array of available snippets.
   *
   * @return array
   */
  public function getSnippets() {
    return $this->snippets;
  }

  /**
   * Invokes the snippets info hook for all modules to register their snippets.
   *
   * @return array
   */
  public function gatherSnippets() {

    $snippets = $this->moduleHandler->invokeAll('snippets_info');

    $this->moduleHandler->alter('snippets_info', $snippets);

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
    $all_snippets = $this->snippets;
    if (preg_match("/^\[(" . implode("|", array_keys($all_snippets)) . ") ?(.*)\]$/i", $snippet, $matched_snippet)) {

      $matched_elements = count($matched_snippet);

      if ($matched_elements >= 2) {
        $snippet_title = $matched_snippet[1];
        $snippet = $all_snippets[$snippet_title];
      }

      if ($matched_elements >= 3) {

        $found_arguments = $matched_snippet[2];

        $pattern = '/(\\w+)\s*(\[])?=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';

        preg_match_all($pattern, $found_arguments, $matches, PREG_SET_ORDER);

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

    $snippet['args'] = $args;

    return $snippet;

  }

  /**
   * Returns the patters string for all available snippets.
   *
   * @return string
   */
  public function getPatternWithAllSnippets() {
    $snippets = $this->snippets;
    return '/\[(' . implode("|", array_keys($snippets)) . ')?.*(?:(?!\[]).)]/imU';
  }

  /**
   * Gets the replacement data for the snippet.
   *
   * @param $snippet
   * @return mixed|string
   */
  public function getSnippetReplaceData($snippet) {

    $replace_data = '';

    $snippet = $this->getSnippetWithArguments($snippet);

    if (array_key_exists('callback', $snippet) && function_exists($snippet['callback'])) {
      $replace_data = call_user_func($snippet['callback'], $snippet['args']);
    }

    return $replace_data;

  }

}