<?php

namespace Drupal\ys_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Adds a search form block.
 *
 * @Block(
 *   id = "ys_search_form_block",
 *   admin_label = @Translation("YaleSites Search Form Block"),
 *   category = @Translation("YaleSites Core"),
 * )
 */
class YaleSitesSearchFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'ys_search_form',
    ];
  }

}
