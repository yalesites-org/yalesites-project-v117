<?php

namespace Drupal\ys_views_basic;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityDisplayRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service for managing the Views Basic plugins.
 */
class ViewsBasicManager extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Allowed entity types for users to select.
   *
   * @var array
   */
  const ALLOWED_ENTITIES = [
    'node_type',
    'media_type',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a new ViewsBasicManager object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepository $entity_display_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * Returns an array of entity type machine names and the human readable name.
   */
  public function entityTypeList() {
    $entityTypes = [];

    foreach (self::ALLOWED_ENTITIES as $type) {
      $types = $this->entityTypeManager()
        ->getStorage($type)
        ->loadMultiple();
      foreach ($types as $machine_name => $object) {
        $nodeType = $object->getEntityTypeId();
        $value = "$nodeType.$machine_name";
        $entityTypes[$value] = $object->label();
      }
    }

    return $entityTypes;
  }

  /**
   * Returns an array of view mode machine names and the human readable name.
   */
  public function viewModeList() {
    $viewModes = [];

    $view_modes = $this->entityTypeManager()
      ->getStorage('entity_view_mode')
      ->loadMultiple();
    foreach ($view_modes as $machine_name => $object) {
      $pattern = "/^node./";
      if (preg_match($pattern, $machine_name) && $object->status()) {
        $viewModes[$machine_name] = $object->label();
      }
    }

    return $viewModes;
  }

  /**
   * Returns an entity label given an entity type and machine name.
   */
  public function getEntityLabel($type) {
    $nodeInfo = explode(".", $type);
    $bundleLabel = $this->entityTypeManager
      ->getStorage($nodeInfo[0])
      ->load($nodeInfo[1])
      ->label();
    return $bundleLabel;
  }

  /**
   * Returns a view mode label given an view mode type stored in the params.
   */
  public function getViewModeLabel($type) {
    $viewModeInfo = explode(".", $type);
    return $this->entityDisplayRepository->getViewModes($viewModeInfo[0])[$viewModeInfo[1]]['label'];
  }

  /**
   * Returns a default value for a parameter to auto-select one in the list.
   */
  public function getDefaultParamValue($type, $params) {
    $paramsDecoded = json_decode($params, TRUE);
    switch ($type) {
      // @todo Change this to better support multiple entity types.
      case 'types':
        $defaultParam = $paramsDecoded['filters']['types'][0];
        break;

      default:
        $defaultParam = $paramsDecoded[$type];
        break;
    }
    return $defaultParam;
  }

}
