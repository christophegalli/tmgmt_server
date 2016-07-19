<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tmgmt\Entity\JobItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt_server\Entity\TMGMTRemoteSource;

/**
 * Class TMGMTServerController.
 *
 * @package Drupal\tmgmt_server\Controller
 */
class TMGMTServerController extends ControllerBase {

  /**
   * Create Job from data transferred by the client.
   *
   * @param array $job_data
   *    Date received from client.
   */
  public function receiveTranslationJob(array $job_data) {
    /** @var  Job $job */
    /** @var  JobItem $job_item */


    return $job;
  }

  /**
   * TranslationJob.
   *
   * @param Request $request
   *   Arriving post request. Send from Guzzle.
   * @return string
   *   Return result code.
   *   If successful, return relation table in body.
   */
  public function translationJob(Request $request) {

    $headers = getallheaders();

    $job_data = [
      'label' => $request->get('label'),
      'from' => $request->get('from'),
      'to' => $request->get('to'),
      'items' => $request->get('items'),
      'job_comment' => $request->get('comment'),
      'user_agent' => $headers['User-Agent'],
    ];

    // Save job data into a local entity to be retrieved by item->getData.
    $sources = [];

    foreach ($job_data['items'] as $key => $item) {
      $item['cid'] = 0;
      $item['source_language'] = $job_data['from'];
      $item['target_language'] = $job_data['to'];
      $item['uid'] = 1;
      $item['data'] = serialize($item['data']);
      $item['user_agent'] = $job_data['user_agent'];
      $item['langcode'] = $job_data['from'];

      $sources[$key] = TMGMTRemoteSource::create($item);
      $sources[$key]->save();
    }

    // Create translation job for this translation request.
    $job = Job::create([
      'label' => $job_data['label'],
      'uid' => 1,
      'source_language' => $job_data['from'],
      'target_language' => $job_data['to'],
      'translator' => 'local',
    ]);

    // This will be saved in the following addItem() call.
    $job->settings = ['job_comment' => $job_data['job_comment']];

    // Create job items for each remote source.
    foreach ($sources as $key => $source) {
      $job->addItem('remote', 'tmgmt_server_remote_source', $source->id());
    }

    // Request translation locally.
    $transaction = \Drupal::service('database')->startTransaction();
    if ($job->requestTranslation() === FALSE) {
      $transaction->rollback();
    }

    // The job has been successfully submitted.
    $response_data = [
      'status' => 'ok',
      'reference' => $job->id(),
    ];
    $response = [
      'data' => $response_data,
    ];


    return new JsonResponse($response);

  }

  /**
   * Pull translation data form remote source, return to client.
   *
   * @param \Drupal\tmgmt\Entity\JobItem $tmgmt_job_item
   *   Corresponding job item.
   */
  public function pullTranslation(JobItem $tmgmt_job_item) {
    //$remoteJobItem = JobItem::load($tmgmt_job_item->get)
  }
}
