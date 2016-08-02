<?php

/**
 * @file
 * Contains tmgmt_server_client.page.inc.
 *
 * Page callback for TMGMT Server Client entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for TMGMT Server Client templates.
 *
 * Default template: tmgmt_server_client.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_tmgmt_server_client(array &$variables) {
  // Fetch TMGMTServerClient Entity Object.
  $tmgmt_server_client = $variables['elements']['#tmgmt_server_client'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
