<?php

namespace Drupal\mass_recipe_content_creation\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class MassRecipeContentCreationCommands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MassRecipeContentCreationCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Creates multiple recipe contents.
   *
   * @param int $num
   *   The number of contents to create.
   * @param string $title
   *   The title of the content.
   * @param string $instruction
   *   The instruction of the recipe.
   * @param string $alias
   *   The URL alias of the content.
   *
   * @command mass_recipe_content_creation:mrcc
   * @aliases mrcc
   * @usage drush mrcc 10 "Delicious recipe" "Cook for 10 minutes" "/recipes/delicious-recipe"
   */
  public function createMultipleContents($num, $title, $instruction, $alias) {
    $node_storage = $this->entityTypeManager->getStorage('node');

    for ($i = 0; $i < $num; $i++) {
      $node = $node_storage->create([
        'type' => 'recipe',
        'title' => $title . ' ' . ($i + 1),
        'recipe_instruction' => $instruction,
        'path' => ['alias' => $alias . '-' . ($i + 1)],
      ]);

      $node->save();

      $this->logger()->success(dt('Created content: @title.', ['@title' => $node->label()]));
    }
  }
}
