<?php

namespace Drupal\mass_content_creation\Commands;

use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;

class MassContentCreationCommands extends DrushCommands {

  /**
 * @command my_drush_module01:create_content
 * @aliases mcc
 * @param $types
 *   The content types to create. Pass as a comma-separated string for multiple types (e.g., 'page,article').
 * @param $count
 *   The number of nodes to create.
 * @param $title_value
 *   The title value for created nodes.
 * @param $body_value
 *   The body value for created nodes.
 * @usage my_drush_module01:create_content page,article 5000 "page_test" "delicious https://www.drupal.org"
 *   Creates 5000 nodes for both "page" and "article" content types.
 */
  
 public function create($types, $count, $title_value, $body_value) {
  // 開始時間を取得
  $startTime = microtime(true);

  // ログに開始時間を記録
  $this->logger()->notice('START...');

  // Convert the comma-separated string of content types into an array
  $types_array = explode(',', $types);

  $batch_size = 500; // Define the batch size

  foreach ($types_array as $type) {
    $type = trim($type);

    for ($i = 0; $i < $count; $i += $batch_size) {
      $nodes_to_save = [];

      for ($j = $i; $j < $i + $batch_size && $j < $count; $j++) {
        $title = $title_value . ' ' . ($j + 1);
        $body_text = $body_value . ' ' . ($j + 1);

        $node = Node::create([
          'type' => $type,
          'title' => $title,
          'body' => [
            'value' => $body_text,
            'format' => 'basic_html',
          ],
          'status' => 1,
        ]);

        $nodes_to_save[] = $node;
      }

      foreach ($nodes_to_save as $node) {
        $node->save();
      }

      $this->logger()->info('Created nodes ' . ($i + 1) . ' to ' . ($j) . ' of type ' . $type);
    }
  }

  $this->logger()->success('Created ' . $count . ' nodes for content types: ' . $types);

  // 終了時間を取得
  $endTime = microtime(true);

  // 開始と終了の時間を計算し表示
  $elapsedTime = $endTime - $startTime;
  $this->logger()->notice(sprintf("Execution time: %.2f seconds", $elapsedTime));

  $this->logger()->notice('END');
}


}

/**
 * 5000件
 * 
 * time: 62.45 seconds
 * time: 68.24 seconds
 * 
 *  コマンド例
 * drush mcc page 5000 "page_test" "delicious https://www.drupal.org"
 * drush mcc article 5000 "article_test" "delicious https://www.drupal.org"
 * drush mcc page,article 5000 "combined_test" "delicious https://www.drupal.org"
 * 
 */

 /**
 * 50000件
 * 
 * time: 668.97 seconds(100)
 * time: 624.06 seconds(500)
 * time: 622.71 seconds(1000)
 * 
 *  コマンド例
 * drush mcc page 25000 "page_test" "delicious https://www.drupal.org"
 * drush mcc article 25000 "article_test" "delicious https://www.drupal.org"
 * drush mcc page,article 25000 "combined_test" "delicious https://www.drupal.org"
 * 
 */



