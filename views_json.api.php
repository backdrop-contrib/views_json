<?php
/**
 * @file
 * Describe hooks provided by the Views datasource module.
 */

/**
 * Alters rendered json row.
 *
 * @param array $field_output
 *   The output rendered by _views_json_render_fields().
 * @param view $view
 *   The view that is being rendered.
 * @param stdClass $row
 *   Raw data collected by views_plugin_json_style().
 *
 * @see _views_json_render_fields()
 * @see views_plugin_json_style()
 */
function hook_views_json_render_row_alter(array &$field_output, view $view, $row) {
  if (isset($row->field_entity_reference[0]['raw']['entity'])) {
    $entity = $row->field_entity_reference[0]['raw']['entity'];
    // Note: $field_output is modified by reference, it is not returned.
    $field_output['field_entity_reference']->content = array(
      'type' => $entity->type,
      'title' => $entity->title,
      'entity_id' => $entity->entity_id,
    );
  }
}
