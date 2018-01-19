<?php

namespace Drupal\content_snippet\Plugin\Filter;

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

    return new FilterProcessResult($text);

  }

}