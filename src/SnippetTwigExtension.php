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

  /**
   * @var SnippetManager
   */
  protected $snippetManager;

  /**
   * SnippetTwigExtension constructor.
   * @param SnippetManager $snippetManager
   */
  public function __construct(SnippetManager $snippetManager) {
    $this->snippetManager = $snippetManager;
  }

  /**
   * For DI.
   *
   * @param ContainerInterface $container
   * @return static
   */
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
   * Declare the twig extension.
   *
   * @return array|\Twig_SimpleFunction[]
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
   * Renders the snippet replacement data, if it there's any.
   *
   * @param $snippet
   * @return \Drupal\Component\Render\MarkupInterface
   * @throws \Exception
   */
  public function snippet($snippet) {
    $render_data = $this->snippetManager->getSnippetReplaceData($snippet);

    if (isset($render_data)) {
      return $this->snippetManager->renderer->render($render_data);
    }
    return $snippet;
  }

}