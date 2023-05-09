<?php

namespace Drupal\my_drush_module04\Commands;

use Drush\Commands\DrushCommands;
use \Drupal\node\Entity\Node;

/**
 * Custom Drush commands for the My Drush Module 04.
 */

class MyDrushModuleCommands04 extends DrushCommands {

  /**
   * Update the Title field of all nodes that don't have a URL alias starting with "/recipes", changing the string "delicious" to "yummy".
   *
   * @command my_drush_module04:update_title
   * @aliases mdm04
   * @usage my_drush_module04:update_title
   *   Update all_contens title fields.
   */

  public function updateTitle() {

    // タスクの処理を開始ログ
    $this->logger()->notice('Starting task...');

    // 全てのコンテンツタイプを取得
    $all_node_query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->execute();
    $all_node = Node::loadMultiple($all_node_query);

    $path_query = \Drupal::entityQuery('path_alias')
      ->condition('alias', '/recipes%', 'LIKE')
      ->accessCheck(FALSE)
      ->execute();

    $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadMultiple($path_query);

    $recipe_node_ids = [];
    foreach ($aliases as $alias) {
      $system_path = $alias->getPath();
      if (preg_match('/^\/node\/(\d+)/', $system_path, $matches)) {
        $recipe_node_ids[] = $matches[1];
      }
    }

    $target_node_ids = array_diff($all_node_query, $recipe_node_ids);

    $updated = false;
    $total_nodes = count($all_node);
    $success_nodes_count = 0;

    if (!empty($target_node_ids)) {

      $query = \Drupal::entityQuery('node')
        ->condition('nid', $target_node_ids, 'IN')
        ->condition('title.value', 'delicious', 'CONTAINS')
        ->accessCheck(FALSE)
        ->execute();
  
      $nodes = Node::loadMultiple($query);
  
      if (!empty($nodes)) {
        foreach ($nodes as $node) {
          $updated = true;
          $title_value = $node->get('title')->value;
          $title_value = str_replace('delicious', 'yummy', $title_value);
          $title_value = strip_tags($title_value);
  
          $node->set('title', $title_value);
          $node->save();

          $success_nodes_count++;
          $updated = true;
          // 進行状況のログ
          $this->logger()->notice(sprintf('Node %d updated successfully.', $node->id()));
        }
      } else {
        $this->logger()->error();
      }
      
      if ($updated) {
        // 処理結果のログ：成功
        $this->logger()->success('Successfully updated body!');
      }

    } else {
      // 処理結果のログ：失敗
      $this->logger()->error('No nodes found excluding alias "/recipes/*" and containing "delicious" in title field.');
    }

  // タスクの処理を終了ログ
  $this->logger()->notice(sprintf('%d of %d content types updated.', $success_nodes_count, $total_nodes));
  $this->logger()->notice('END');
  }
}
