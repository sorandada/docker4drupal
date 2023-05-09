<?php

namespace Drupal\my_drush_module02\Commands;

use Drush\Commands\DrushCommands;
use \Drupal\node\Entity\Node;

/**
 * Custom Drush commands for the My Drush Module 02.
 */
class MyDrushModuleCommands02 extends DrushCommands {

  /**
   * 「基本ページ」コンテンツタイプの「title」フィールドに入力された文字列"Umami"を"this site"に変更する、カスタム Drush コマンドの作成.
   *
   * @command my_drush_module02:update_title
   * @aliases mdm02
   * @usage my_drush_module02:update_title
   *   Update page title fields.
   */
  public function updateTitle() {

    // タスクの処理を開始ログ
    $this->logger()->notice('Starting task...');

    // 全てのコンテンツタイプを取得
    $all_node_query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->execute();
    $all_node = Node::loadMultiple($all_node_query);

    // フィールド「title」に文字列"Umami"が含まれるコンテンツタイプ「基本ページ」をすべて読み込む.
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'page', 'IN')
      ->condition('title.value', 'Umami', 'CONTAINS')
      ->accessCheck(FALSE)
      ->execute();

    $nodes = Node::loadMultiple($query);

    $updated = false; // ノードが更新されたかどうかをチェック
    $total_nodes = count($all_node);
    $success_nodes_count = 0;

    if (!empty($nodes)){
    // 各ノードのtitleフィールドを更新する.
      foreach ($nodes as $node) {
        // titleフィールドの値を取得する。
        $title_value = $node->get('title')->value;
        // "Umami" を "this site"に変換する.
        $new_title_value = str_replace('Umami', 'this site', $title_value);

        // title値が実際に変更されたかどうかをチェック
        if ($new_title_value !== $title_value) {
          // 更新されたtitle値をエンティティに戻す。
          $node->get('title')->value = $new_title_value;
          $node->save();

          $success_nodes_count++;
          $updated = true;
          // 進行状況のログ
          $this->logger()->notice(sprintf('Node %d updated successfully.', $node->id()));
        }
      }

      // 処理結果のログ：成功
      if ($updated) {
        $this->logger()->success('Successfully updated!');
      }
    }  else {
      // 処理結果のログ：失敗
      $this->logger()->error('No nodes were found that contain the content type "basic page" with the string "Umami" in the field "title".');
      }

    // タスクの処理を終了ログ
    $this->logger()->notice(sprintf('%d of %d content types updated.', $success_nodes_count, $total_nodes));
    $this->logger()->notice('END');

  }
}
