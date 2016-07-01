<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tmgmt\Entity\JobItem;
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
   * Create Job from data transferred by the client
   * @param array $job_data
   */
  public function createJobFromData (array $job_data) {
    /** @var  Job $job */
    /** @var  JobItem $job_item */
    
    $job = Job::create();
    return $job;
  }

  
  /**
   * Addtranslation.
   *
   * @return string
   *   Return result code.
   *   If successful, return relation table in body.
   */
  public function translationJob (Request $Request) {

    $job_data = [
      'from' => $Request->get('from'),
      'to' => $Request->get('to'),
      'items' => $Request->get('items'),
      'comment' => $Request->get('comment'),
    ];

    $job = $this->createJobFromData($job_data);
    
    $response['test'] = $job_data;
    return  new JsonResponse($response);

  }
}
