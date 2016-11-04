<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\TMGMTException;
use Drupal\tmgmt_server\Entity\TMGMTServerClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt_server\Entity\TMGMTServerRemoteSource;
use Drupal\Component\Utility\Crypt;
use Drupal\tmgmt\Entity\Translator;

/**
 * Class TMGMTServerController.
 *
 * @package Drupal\tmgmt_server\Controller
 */
class TMGMTServerController extends ControllerBase {

  /**
   * Defined character count for key.
   */
  const TMGMT_AUTH_KEY_LENGTH = 64;

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
   *   When translation request fails.
   */
  public function translationJob(Request $request) {
    /* @var Job $job */
    $headers = getallheaders();

    $job_data = [
      'label' => (string) $request->get('label'),
      'from' => $request->get('from'),
      'to' => $request->get('to'),
      'items' => $request->get('items'),
      'job_comment' => $request->get('comment'),
      'user_agent' => $headers['User-Agent'],
    ];

    // Save job data into a local entity to be retrieved by item->getData.
    $sources = [];

    foreach ($job_data['items'] as $key => $item) {
      $item['label'] = $job_data['label'];
      $item['cid'] = 0;
      $item['source_language'] = $job_data['from'];
      $item['target_language'] = $job_data['to'];
      $item['uid'] = \Drupal::currentUser();

      // Rebuilding the label the was filtered out for the transfer.
      foreach ($item['data'] as $field_key => $field_value) {
        $item['data'][$field_key]['#label'] = $field_key;
      }
      $item['data'] = serialize($item['data']);
      $item['user_agent'] = $job_data['user_agent'];
      $item['langcode'] = $job_data['from'];

      $sources[$key] = TMGMTServerRemoteSource::create($item);
      $sources[$key]->save();
    }

    // Create translation job for this translation request.
    $job = Job::create([
      'label' => $job_data['label'],
      'uid' => 1,
      'source_language' => $job_data['from'],
      'target_language' => $job_data['to'],
      'translator' => \Drupal::config('tmgmt_server.settings')
        ->get('default_translator'),
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

    // Request translation, only if the translator is set.
    // Otherwise, leave it as 'submitted'.
    if ($job->hasTranslator()) {
      $transaction = \Drupal::service('database')->startTransaction();
      if ($job->requestTranslation() === FALSE) {
        $transaction->rollback();
        throw new TMGMTException('Translation request cannot be executed.');
      }
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
   * This path/method is only for compatibility reasons with D7 client.
   * The D8 client only uses pullRemoteItem.
   *
   * @param TMGMTServerRemoteSource $tmgmt_server_remote_source
   *   Corresponding job item.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response including Json encoded job item data.
   *
   * @throws TMGMTException
   *   If 0 or more than 1 job items are returned ny the entity query.
   */
  public function pullTranslation(TMGMTServerRemoteSource $tmgmt_server_remote_source) {
    /* @var array $item_ids */

    // Return job item data when provided with the remote source id.
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

  public function pullRemoteItem (JobItem $tmgmt_job_item) {

    // Return job item data when provided with the job item id.
    $response = [];
    $response['data'] = $tmgmt_job_item->getData();
    return new JsonResponse($response);
  }

  /**
   * Build the response for case of failure.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The completed response.
   */
  protected function failResponse() {
    $response = new Response(
      'Authentications failed',
      Response::HTTP_UNAUTHORIZED
    );
    return $response;
  }

  public function languagePairsIndex(Request $request) {

    /** @var \Drupal\tmgmt\Entity\Translator $translator */
    $headers = getallheaders();

    if (!$this->authenticate($headers['Authenticate'])) {
      return $this->failResponse();
    }

    $languages = array();
    $default_translator = \Drupal::config('tmgmt_server.settings')->get('default_translator');
    $translator = Translator::load($default_translator);

    if(isset($translator)) {
      if (empty($source_language)) {
        // We need to collect target languages for each of our local language.
        foreach (\Drupal::languageManager()->getLanguages() as $key => $info) {
          foreach ($translator->getSupportedTargetLanguages($key) as $target_language) {
            $languages[] = array(
              'source_language' => $key,
              'target_language' => $target_language,
            );
          }
        }
      }
      else {
        foreach ($translator->getSupportedTargetLanguages($source_language) as $target_language) {
          $languages[] = array(
            'source_language' => $source_language,
            'target_language' => $target_language,
          );
        }
      }
    }
    $response_data = [
      'status' => 'ok',
      'data' => $languages,
    ];

    return new JsonResponse($response_data);
  }

  /**
   * Find Server Client entity by its id.
   *
   * @param int $id
   *   Id we are looking for.
   *
   * @return TMGMTServerClient
   *   The found entity or NULL.
   */
  public function findServerClientById($id) {
    /** @var array $ids */
    $ids = \Drupal::entityQuery('tmgmt_server_client')
      ->condition('client_id', $id)
      ->execute();

    if (count($ids) != 1) {
      return NULL;
    }

    return TMGMTServerClient::load(array_shift($ids));
  }

  /**
   * Get the parts of the authentication string and check correct format.
   *
   * @param string $auth_string
   *   The string passed for ahtientication.
   *
   * @return array
   *   The correct parts or NULL.
   */
  public function parseAuthString($auth_string) {

    if (empty($auth_string)) {
      return NULL;
    }

    $parts = explode('@', $auth_string);

    if (count($parts) != 3 || strlen($parts[0]) != $this::TMGMT_AUTH_KEY_LENGTH) {
      return NULL;
    }

    if (!is_numeric($parts[2])) {
      return NULL;
    }

    $return = array(
      'client_id' => $parts[0],
      'secret' => $parts[1],
      'timestamp' => $parts[2],
    );

    return $return;
  }

  /**
   * Check if authentication string is correct.
   *
   * @param string $auth_string
   *   The string passed to the server.
   *
   * @return \Drupal\tmgmt_server\Entity\TMGMTServerClient
   *   The corresponding entity or null.
   */
  public function authenticate($auth_string) {

    if (!$auth_parts = $this->parseAuthString($auth_string)) {
      return NULL;
    }

    $server_client = $this->findServerClientById($auth_parts['client_id']);
    if (empty($server_client)) {
      return NULL;
    }

    // Build secret fom stored keys.
    $secret = Crypt::hmacBase64($auth_parts['timestamp'], $server_client->getClientSecret());
    if ($secret != $auth_parts['secret']) {
      return NULL;
    }
    else {
      // Authentication succeeded.
      return $server_client;
    }
  }

}
