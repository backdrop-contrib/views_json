<?php
/**
 * @file
 * Default theme implementation to display Views JSON "Simple" format output.
 *
 * Available variables:
 * - $view: The View object.
 * - $rows: Hierachial array of key=>value pairs to convert to JSON
 * - $options: Array of options for this style
 *
 * @see template_preprocess_views_views_json_style_simple()
 *
 * @ingroup themeable
 */


if (!empty($options["grouping"][0]["field"])) {
  $group = $options["grouping"][0]["field"];
  // If a label is set for the grouped field, get it and use it instead of the machine name.
  // $options uses the labeled name of the field, so we need to match that for grouping.
  if ($options['field_output'] != 'raw') {
    $group_label = $view->query->pager->display->handler->handlers['field'][$group]->options['label'];
    if (strlen($group_label) > 0) {
      $group = $group_label;
    }
  }
  $root = $options["root_object"];
  $top_child = $options["top_child_object"];

  $grouped = array();
  if (!empty($root)) {
    foreach ($rows[$root] as $key => $array) {
      // Array has 3 levels.
      if (!empty($top_child)) {
        $groupnode = $array[$top_child][$group];
        unset($array[$top_child][$group]);
        $grouped[$root][$groupnode][] = $array;
      }
      // Array has 2 levels.
      else {
        $groupnode = $array[$group];
        unset($array[$group]);
        $grouped[$root][$groupnode][] = $array;
      }
    }
  }
  else {
    foreach ($rows as $index => $value) {
      // @todo does a top_child without root make sense?
      if (!empty($top_child)) {
        $groupnode = $value[$top_child][$group];
        unset($value[$top_child][$group]);
      }
      else {
        $groupnode = $value[$group];
        unset($value[$group]);
      }
      $grouped[$groupnode][] = $value;
    }
  }
  $rows = $grouped;
}

// Uncomment everything below for prod
$jsonp_prefix = $options['jsonp_prefix'];
if ($view->override_path) {
  // We're inside a live preview where the JSON is pretty-printed.
  $json = _views_json_encode_formatted($rows, $options);
  if ($jsonp_prefix) $json = "$jsonp_prefix($json)";
  print "<code>$json</code>";
}
else {
  $json = _views_json_json_encode($rows, $bitmask);
  if ($options['remove_newlines']) {
     $json = preg_replace(array('/\\\\n/'), '', $json);
  }

  if (isset($_GET[$jsonp_prefix]) && $jsonp_prefix) {
    $json = check_plain($_GET[$jsonp_prefix]) . '(' . $json . ')';
  }

  if ($options['using_views_api_mode']) {
    // We're in Views API mode.
    print $json;
  }
  else {
    // We want to send the JSON as a server response so switch the content
    // type and stop further processing of the page.
    $content_type = ($options['content_type'] == 'default') ? 'application/json' : $options['content_type'];
    backdrop_add_http_header("Content-Type", "$content_type; charset=utf-8");
    print $json;
    backdrop_page_footer();
    exit;
  }
}
