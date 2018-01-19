# Content Snippets

Allows usage of shortcodes

## Usage
```php
/**
 * Implements hook_snippets_info().
 */
function mymodule_snippets_info() {
  $snippets = [];
  $snippets['recent_nodes'] = [
    'title' => t('Recent Nodes'),
    'description' => t('List of recently updated nodes'),
    'callback' => 'mymodule_recent_nodes_snippet'
  ];
  return $snippets;
}

/**
 * The callback method for the recent_nodes snippet.
 *
 * @param $args
 * @return
 */
function mymodule_recent_nodes_snippet($args) {
  return [
    '#type' => 'markup',
    '#markup' => '<ul><li>My first node</li></ul>'
  ];
}
```