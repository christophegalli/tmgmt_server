<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt_local\Entity\LocalTask;

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
  /** @var  Job $job */

    $job_data = $Request->request->get('data');

    $job = Job::create();

    $



    $response['data'] = $job_data;
    return  new JsonResponse($response);
  }

}
