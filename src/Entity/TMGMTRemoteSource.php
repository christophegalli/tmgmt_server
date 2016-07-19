<?php

namespace Drupal\tmgmt_server\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Remote source entity.
 *
 * @ingroup tmgmt_server
 *
 * @ContentEntityType(
 *   id = "tmgmt_server_remote_source",
 *   label = @Translation("Remote source"),
 *   base_table = "tmgmt_server_remote_source",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 * )
 */
class TMGMTRemoteSource extends ContentEntityBase {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
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
  public function getTranslation($langcode) {
    // @todo: check if there is extende function to this.
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setDescription(t('The ID of the Remote source entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setDescription(t('The UUID of the Remote source entity.'))
      ->setReadOnly(TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setDescription(t('The name of the Remote source entity.'))
      ->setDefaultValue('');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['cid'] = BaseFieldDefinition::create('integer')
      ->setDescription(t('The id of the remote client tis belongs to.'))
      ->setLabel(t('Remote Client ID'));

    $fields['source_language'] = BaseFieldDefinition::create('string')
      ->setDescription(t('Source language'))
      ->setDefaultValue('')
      ->setSetting('max_length', 12);

    $fields['target_language'] = BaseFieldDefinition::create('string')
      ->setDescription(t('Target language'))
      ->setDefaultValue('')
      ->setSetting('max_length', 12);

    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setDescription(t('Serialized Data'))
      ->setDefaultValue('');

    $fields['callback'] = BaseFieldDefinition::create('string')
      ->setDescription(t('The callback URL that should be used to inform the remote client about the finished translation.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 255);

    $fields['user_agent'] = BaseFieldDefinition::create('string')
      ->setDescription(t('Form where is the called being executed.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 255);

    $fields['comment'] = BaseFieldDefinition::create('string')
      ->setDescription(t('The comment sent with the job'))
      ->setDefaultValue('')
      ->setSetting('max_length', 255);

    $fields['reference'] = BaseFieldDefinition::create('string')
      ->setDescription(t('The client reference for the data to be translated.'))
      ->setDefaultValue('')
      ->setSetting('max_length', 30);

    $fields['state'] = BaseFieldDefinition::create('integer')
      ->setDescription(t('The state of the remote source.'))
      ->setRequired(TRUE)
      ->setDefaultValue(0);


    return $fields;
  }

}
