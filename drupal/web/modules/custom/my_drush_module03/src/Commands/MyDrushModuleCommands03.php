<?php

namespace Drupal\my_drush_module03\Commands;

use Drush\Commands\DrushCommands;
use \Drupal\node\Entity\Node;

/**
 * Custom Drush commands for the My Drush Module 03.
 */
class MyDrushModuleCommands03 extends DrushCommands {

  /**
   * URLエイリアス「/recipes/*」の「Recipe」コンテンツタイプの「recipe_instruction」フィールドに入力された文字列"minutes"を"mins"に変更する、カスタム Drush コマンドの作成.
   *
   * @command my_drush_module03:update_recipe_instruction
   * @aliases mdm03
   * @usage my_drush_module03:update_recipe_instruction
   *   Update recipe recipe_instruction fields.
   */
  public function updateRecipe_instruction() {

    // タスクの処理を開始ログ
    $this->logger()->notice('Starting task...');

    // 全てのコンテンツタイプを取得
    $all_node_query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->execute();
    $all_node = Node::loadMultiple($all_node_query);

    // URLエイリアスに一致するパスを検索する
    $path_query = \Drupal::entityQuery('path_alias')
      ->condition('alias', '/recipes%', 'LIKE')
      ->accessCheck(FALSE)
      ->execute();

    // URLエイリアスを元に、対応するノードIDを取得する
    $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadMultiple($path_query);
    $node_ids = [];
    foreach ($aliases as $alias) {
      $system_path = $alias->getPath();
      if (preg_match('/^\/node\/(\d+)/', $system_path, $matches)) {
        $node_ids[] = $matches[1];
      }
    }

    $updated = false;
    $total_nodes = count($all_node);
    $success_nodes_count = 0;

    if (!empty($node_ids)) {
      // Recipeコンテンツタイプで、指定されたノードIDに対応するエンティティを検索する
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'recipe')
        ->condition('nid', $node_ids, 'IN')
        ->condition('recipe_instruction.value', 'minutes', 'CONTAINS')
        ->accessCheck(FALSE)
        ->execute();

      $nodes = Node::loadMultiple($query);

      // 各ノードのrecipe_instructionフィールドを更新する.
      foreach ($nodes as $node) {
        // recipe_instructionフィールドの値を取得する。
        $recipe_instruction_value = $node->get('recipe_instruction')->value;
        // "minutes" を "mins"に変換する.
        $updated_recipe_instruction_value = str_replace('minutes', 'mins', $recipe_instruction_value);

        // 更新前と更新後の値が異なる場合、更新されたrecipe_instruction値をエンティティに戻し、ノードを保存
        if ($recipe_instruction_value !== $updated_recipe_instruction_value) {
          $updated_recipe_instruction_value = strip_tags($updated_recipe_instruction_value);
          $node->set('recipe_instruction', $updated_recipe_instruction_value);
          $node->save();

          $updated = true; // ノードが更新された場合、フラグをtrueに設定
          $success_nodes_count++;
          // 進行状況のログ
          $this->logger()->notice(sprintf('Node %d updated successfully.', $node->id()));
        }
      }

      // 処理結果のログ：成功
      if ($updated) {
        $this->logger()->success('Successfully updated!');
      } else {
        // 処理結果のログ：失敗
        $this->logger()->error('No nodes found with alias "/recipes/*" and containing "minutes" in recipe_instruction field.');
      }
    } else {
      // 処理結果のログ：失敗
      $this->logger()->error('No nodes found with alias "/recipes/*" and containing "minutes" in recipe_instruction field.');
    }
    // タスクの処理を終了ログ
    $this->logger()->notice(sprintf('%d of %d content types updated.', $success_nodes_count, $total_nodes));
    $this->logger()->notice('END');
  }
}
