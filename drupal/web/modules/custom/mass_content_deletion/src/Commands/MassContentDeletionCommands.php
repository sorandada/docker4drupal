<?php

namespace Drupal\mass_content_deletion\Commands;

use Drush\Commands\DrushCommands;
use Drupal\node\Entity\Node;

/**
 * A Drush commandfile.
 *
 * @package Drupal\mass_content_deletion\Commands
 */
class MassContentDeletionCommands extends DrushCommands {

  /**
   * Deletes all specified content.
   *
   * @command mass_content:delete
   * @aliases mcd
   * @usage mass_content:delete
   *   Deletes all specified content.
   */
  
   public function delete() {
    // 開始時間を取得
    $startTime = microtime(true);
  
    // ログに開始時間を記録
    $this->logger()->notice('START...');
  
    $batch_size = 500; // 一度に削除するノード数を指定
    $total_deleted = 0;
  
    do {
      // entityQueryを使用して一度にbatch_size分のノードIDを取得
      $nids = \Drupal::entityQuery('node')
        ->accessCheck(FALSE)
        ->range(0, $batch_size)
        ->execute();
  
      if (!empty($nids)) {
        $entities = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadMultiple($nids);
        \Drupal::entityTypeManager()
          ->getStorage('node')
          ->delete($entities);
        $total_deleted += count($nids);
      }
    } while (!empty($nids));
  
    // 終了時間を取得
    $endTime = microtime(true);
  
    // 開始と終了の時間を計算し表示
    $elapsedTime = $endTime - $startTime;
    $this->logger()->notice(sprintf("Execution time: %.2f seconds", $elapsedTime));
  
    $this->logger()->success(dt('All specified content has been deleted.'));
  
    $this->logger()->notice('END');
  }
  
}

/**
 * 5000件
 * time 90.72 seconds(1000)
 * time 88.67 seconds
 * 
 */

 /**
 * 50000件
 * time 877.93 seconds(1000)
 * time 1270.46 seconds(10000)
 * time 915.29 seconds(500)
 */
