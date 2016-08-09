<?php

namespace Drupal\tmgmt_server\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the TMGMT Server Client entity.
 *
 * @ingroup tmgmt_server
 *
 * @ContentEntityType(
 *   id = "tmgmt_server_client",
 *   label = @Translation("TMGMT Server Client"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tmgmt_server\TMGMTServerClientListBuilder",
 *     "views_data" = "Drupal\tmgmt_server\Entity\TMGMTServerClientViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\tmgmt_server\Form\TMGMTServerClientForm",
 *       "add" = "Drupal\tmgmt_server\Form\TMGMTServerClientForm",
 *       "edit" = "Drupal\tmgmt_server\Form\TMGMTServerClientForm",
 *       "delete" = "Drupal\tmgmt_server\Form\TMGMTServerClientDeleteForm",
 *     },
 *     "access" = "Drupal\tmgmt_server\TMGMTServerClientAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tmgmt_server\TMGMTServerClientHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "tmgmt_server_client",
 *   admin_permission = "administer tmgmt server client entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/tmgmt_server_client/{tmgmt_server_client}",
 *     "add-form" = "/tmgmt_server_client/add",
 *     "edit-form" = "/tmgmt_server_client/{tmgmt_server_client}/edit",
 *     "delete-form" = "/tmgmt_server_client/{tmgmt_server_client}/delete",
 *   },
 *   field_ui_base_route = "tmgmt_server_client.settings"
 * )
 */
class TMGMTServerClient extends ContentEntityBase implements TMGMTServerClientInterface {

  use EntityChangedTrait;

  /**
   * Character count for keys.
   */
  const TMGMT_KEY_LENGTH = 64;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
      'owner' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setKeys() {
    $this->set('client_id', user_password($this::TMGMT_KEY_LENGTH));
    $this->set('secret', user_password($this::TMGMT_KEY_LENGTH));
  }

  /**
   * {@inheritdoc}
   */
  public function getClientId() {
    return $this->get('client_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getSecret() {
    return $this->get('secret');
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the TMGMT Server Client entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Client.'))
      ->setSettings(array(
        'max_length' => 32,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ));

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel('Description')
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ));

    $fields['URL'] = BaseFieldDefinition::create('string')
      ->setLabel(t('URL'))
      ->setDescription(t('Provide the URL of the client system.'))
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ));

    $fields['client_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Client ID'))
      ->setDescription(t('The public ID for this client.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ));

    $fields['client_secret'] = BaseFieldDefinition::create('string')
      ->setLabel(t(' Client Secret'))
      ->setDescription(t('The secret key for this client.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ));

    $fields['owner'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDescription(t('The owner of the client .'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
