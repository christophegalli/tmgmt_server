<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TMGMTServerController.
 *
 * @package Drupal\tmgmt_server\Controller
 */
class TMGMTServerController extends ControllerBase {

  /**
   * Addtranslation.
   *
   * @return string
   *   Return Hello string.
   */
  public function addRemoteTranslation(Request $Request) {

    $response['data'] = 'Antwort: ' . $Request->get('galli');
    return  new JsonResponse(getallheaders()) ;
  }

}
