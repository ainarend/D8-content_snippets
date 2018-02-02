<?php
/**
 * @file
 * Contains Drupal\content_snippets\SnippetTwigExtension.
 */

namespace Drupal\content_snippets;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_ExtensionInterface;

/**
 * Class SnippetTwigExtension
 * @package Drupal\content_snippets
 */
class SnippetTwigExtension extends \Twig_Extension implements ContainerInjectionInterface {

  protected $snippetManager;

  public function __construct(SnippetManager $snippetManager) {
    $this->snippetManager = $snippetManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('snippet_manager')
    );
  }

  /**
   * {@inheritdoc}
   * This function must return the name of the extension. It must be unique.
   */
  public function getName() {
    return 'snippet';
  }

  /**
   * In this function we can declare the extension function
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('snippet',
        [$this, 'snippet'],
        [
          'is_safe' => array('html')
        ]
      )];
  }

  /**
   * The php function to load a given block
   */
  public function snippet($snippet) {
    return [
      '#type' => 'markup',
      '#markup' => '<ul><li>My second node</li></ul>'
    ];
  }

}