<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\TMGMTException;
use GuzzleHttp\Psr7\Response;
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
   *
   * @return Response
   *   New job in JSON.
   */
  public function receiveTranslationJob(array $job_data) {
    /* @var  Job $job */
    /* @var  JobItem $job_item */

    return $job;
  }

  /**
   * TranslationJob.
   *
   * @param Request $request
   *   Arriving post request. Send from Guzzle.
   *
   * @return string
   *   Return result code.
   *   If successful, return relation table in body.
   *
   * @throws TMGMTException
   *   When LocalTask creation fails.
   */
  public function translationJob(Request $request) {
    /* @var Job $job */
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
      $item['uid'] = \Drupal::currentUser();
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

    // Use mapping table to relate client and remote job items.
    $mapping_table = [];

    // Create job items for each remote source.
    foreach ($sources as $key => $source) {
      $remote_item = $job->addItem('remote', 'tmgmt_server_remote_source', $source->id());
      $mapping_table[$key] = $remote_item->id();
    }

    // Request translation locally.
    $transaction = \Drupal::service('database')->startTransaction();
    if ($job->requestTranslation() === FALSE) {
      $transaction->rollback();
      throw new TMGMTException('Local Task cannot be created');
    }

    // The job has been successfully submitted.
    $response_data = [
      'status' => 'ok',
      'reference' => $job->id(),
      'remote_mapping' => $mapping_table,
    ];
    $response = [
      'data' => $response_data,
    ];

    return new JsonResponse($response);
  }

  /**
   * Pull translation data form remote source, return to client.
   *
   * @param TMGMTRemoteSource $tmgmt_server_remote_source
   *   Corresponding job item.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response including Json encoded job item data.
   *
   * @throws TMGMTException
   *   If 0 or more than 1 job items are returned ny the entity query.
   */
  public function pullTranslation(TMGMTRemoteSource $tmgmt_server_remote_source) {
    /* @var array $item_ids */

    $item_ids = \Drupal::entityQuery('tmgmt_job_item')
      ->condition('item_type', 'tmgmt_server_remote_source')
      ->condition('item_id', $tmgmt_server_remote_source->id())
      ->execute();

    if ($item_ids) {
      if (count($item_ids) == 1) {
        // Found the corresponding job item.
        $remote_job_item = JobItem::load(array_shift($item_ids));
        $response = ['data' => $remote_job_item->getData()];

        return new JsonResponse($response);
      }
      throw new TMGMTException('Multiple job items for remote source @rsid',
        array('rsid' => $tmgmt_server_remote_source->id()));
    }

    throw new TMGMTException('No job item available for remote source @rsid',
      array('rsid' => $tmgmt_server_remote_source->id()));
  }
}
