# Content Snippets

Allows usage of shortcodes/snippets. Comes with out-of-the box support of following snippets:
```
[views id="who_s_online" display="who_s_online_block"]
[node id="1"]
[term id="3242"]
```
Supports Contextual Filters for Views as well.
```
[views id="taxonomy_term" display="page_1" context_filters="2"]
```

Has a very simple API for other modules to declare Snippets as well.
 

## Usage
### Declaring snippets.
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

The arguments passed with the snippet are available for the callback function in the $args variable, as shown in the example.

### Using in Filters
1. Enable the Content Snippets filter for any appropriate text filters.
2. For nodes or anywhere else where the text filter is applied, use the snippet like this:
```
[recet_nodes limit="10"]
```

The snippet gets replaced within the text by the markuo defined in mymodule_recent_nodes_snippet().

### Using in Twig template files
To render the snippet in the twig see the following example:
```Twig
{{ snippet('[recent_nodes limit="10" title="Recently updated nodes"]') }}
```
