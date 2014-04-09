<?php

/**
 * @file
 * This file contains all theme and preprocess functions
 */

/**
 *
 * @global type $base_url
 * @param array $variables
 *   an array of variables that will be passed to the theme function
 */
function islandora_pdf_preprocess_islandora_pdf(&$variables) {
  drupal_add_js('misc/form.js');
  drupal_add_js('misc/collapse.js');
  $islandora_object = $variables['islandora_object'];
 
  $repository = $islandora_object->repository;
  module_load_include('inc', 'islandora', 'includes/datastream');
  module_load_include('inc', 'islandora', 'includes/utilities');
  module_load_include('inc', 'islandora', 'includes/mods');
  
  
  try {
    $mods = $islandora_object['MODS']->content;
    $ndltd = $islandora_object['NDLTD']->content; //Updated to deliver NDLTD datastream to MODS class.
    $custom = $islandora_object['CUSTOM']->content; //Updated to deliver CUSTOM datastream to MODS class.
    $mods_object = MODS::importFromXMLString($mods, $ndltd, $custom);
    
    //drupal_set_message('<pre>'.print_r($mods_object, TRUE).'</pre>');
    //drupal_set_message('<pre>'.print_r($custom, TRUE).'</pre');

  } catch (Exception $e) {
    drupal_set_message(t('Error retrieving object %s %t', array('%s' => $islandora_object->id, '%t' => $e->getMessage())), 'error', FALSE);
  }
  $variables['islandora_mods'] = isset($mods_object) ? $mods_object : NULL;
  $variables['dc_array'] = isset($mods_object) ? $mods_object->asArray() : array();
  $variables['islandora_object_label'] = $islandora_object->label;
  $variables['theme_hook_suggestions'][] = 'islandora_pdf__' . str_replace(':', '_', $islandora_object->id);
  $variables['parent_collections'] = islandora_get_parents_from_rels_ext($islandora_object);
  global $base_url;
  // full size url
  if (isset($islandora_object['OBJ'])) {
    $full_size_url = url("islandora/object/{$islandora_object->id}/datastream/OBJ/view", array('absolute' => TRUE));
    $variables['islandora_full_url'] = $full_size_url;
  }
  // thumbnail
  if (isset($islandora_object['TN'])) {
    $thumbnail_size_url = url("islandora/object/{$islandora_object->id}/datastream/TN/view");
    $params = array(
      'title' => $islandora_object->label,
      'path' => $thumbnail_size_url
    );
    $variables['islandora_thumbnail_img'] = theme('image', $params);
  }
  // preview image + link
  if (isset($islandora_object['PREVIEW'])) {
    $preview_url = url("islandora/object/{$islandora_object->id}/datastream/PREVIEW/view");
    $params = array(
      'title' => $islandora_object->label,
      'path' => $preview_url
    );
    $variables['islandora_preview_img'] = theme('image', $params);
    $variables['islandora_content'] = l($variables['islandora_preview_img'], $variables['islandora_full_url'], array('html' => TRUE));
    $download_url = 'islandora/object/' . $islandora_object->id . '/datastream/OBJ/download';
    $download_text = t("Download pdf");
    $variables['islandora_download_link'] = l($download_text, $download_url, array('attributes' => array('class' => array('islandora-pdf-link'))));
  }
}
?>
