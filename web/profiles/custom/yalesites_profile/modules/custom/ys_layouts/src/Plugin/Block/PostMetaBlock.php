<?php

namespace Drupal\ys_layouts\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Controller\TitleResolver;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Block for post meta data that appears above posts.
 *
 * @Block(
 *   id = "post_meta_block",
 *   admin_label = @Translation("Post Meta Block"),
 *   category = @Translation("YaleSites Layouts"),
 * )
 */
class PostMetaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Controller\TitleResolver
   */
  protected $titleResolver;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $taxonomyTermStorage;

  /**
   * Constructs a new PostMetaBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Controller\TitleResolver $title_resolver
   *   The title resolver.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Entity\EntityStorageInterface $taxonomyTermStorage
   *   The taxonomy term storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, TitleResolver $title_resolver, RequestStack $request_stack, DateFormatter $date_formatter, EntityStorageInterface $taxonomyTermStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->titleResolver = $title_resolver;
    $this->requestStack = $request_stack;
    $this->dateFormatter = $date_formatter;
    $this->taxonomyTermStorage = $taxonomyTermStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
    $configuration,
    $plugin_id,
    $plugin_definition,
    $container->get('current_route_match'),
    $container->get('title_resolver'),
    $container->get('request_stack'),
    $container->get('date.formatter'),
    $container->get('entity_type.manager')->getStorage('taxonomy_term'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->requestStack->getCurrentRequest()->attributes->get('node');
    if (!($node instanceof NodeInterface)) {
      return [];
    }

    $title = NULL;
    $author = NULL;
    $publishDate = NULL;
    $dateFormatted = NULL;
    $externalSourceLabel = NULL;
    $externalSourceLabelUrl = NULL;

    $route = $this->routeMatch->getRouteObject();

    if ($route) {
      // Post fields.
      $title = $node->getTitle();
      $author = ($node->field_author->first()) ? $node->field_author->first()->getValue()['value'] : NULL;
      $publishDate = strtotime($node->field_publish_date->first()->getValue()['value']);
      $dateFormatted = $this->dateFormatter->format($publishDate, '', 'c');
      $taxId = ($node->field_external_source_label->first()) ? $node->field_external_source_label->first()->getValue()['target_id'] : NULL;
      if ($taxId) {
        $term = $this->taxonomyTermStorage->load($taxId);
        if ($term) {
          $externalSourceLabel = $term->getName();
          $externalSourceLabelUrlField = $term->field_link->first();
          if ($externalSourceLabelUrlField) {
            $externalSourceLabelUrl = $term->field_link->first()->getValue()['uri'];
          }
        }
      }
    }

    return [
      '#theme' => 'ys_post_meta_block',
      '#label' => $title,
      '#author' => $author,
      '#date_formatted' => $dateFormatted,
      '#external_source_label' => $externalSourceLabel,
      '#external_source_label_url' => $externalSourceLabelUrl,
    ];
  }

}
