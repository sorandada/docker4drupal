# 高速化

このドキュメントでは、大量のコンテンツを効率よく処理するための高速化の取り組みを記述します。

# 高速化の手法
## 1. バッチ処理: データを一定量ごとにまとめて処理します。これにより、一度に大量のデータを処理することを避け、メモリ使用量を抑制します。

 以下のコードをコピペし、実行。

```php
<?php

namespace Drupal\my_drush_module01\Commands;

use Drush\Commands\DrushCommands;
use Drupal\node\Entity\Node;

/**
 * Custom Drush commands for the My Drush Module 01.
 */
class MyDrushModuleCommands01 extends DrushCommands {

  /**
   * 「基本ページ」と「記事」コンテンツタイプの「body」フィールドに入力された文字列"delicious"を"yummy"に、"https://www.drupal.org"を"https://WWW.DRUPAL.ORG"に変更する、カスタム Drush コマンドの作成.
   *
   * @command my_drush_module01:update_body
   * @aliases mdm01
   * @usage my_drush_module01:update_body
   *   Update page and article body fields.
   */

   public function myDrushModule01() {
    // 開始時間を取得
    $startTime = microtime(true);
  
    // ログに開始時間を記録
    $this->logger()->notice('START...');
  
    //ノードを更新するための変数の初期化
    $updated = FALSE;
    $success_nodes_count = 0;
    $total_nodes = 0;
    $batch_size = 500;
    $current_page = 0;
  
    do {
      //EntityQueryを使用して、「基本ページ」と「記事」コンテンツタイプのノードで、「body」フィールドに"delicious"または"https://www.drupal.org"を含むものを検索。検索は500件ずつ取得するようにページングされている。
      $query = \Drupal::entityQuery('node');
      $or_group = $query->orConditionGroup()
        ->condition('body', '%delicious%', 'LIKE')
        ->condition('body', '%https://www.drupal.org%', 'LIKE');
  
      $query = $query
        ->condition($or_group)
        ->condition('type', ['page', 'article'], 'IN')
        ->range($current_page * $batch_size, $batch_size)
        ->accessCheck(FALSE);
        
      $nids = $query -> execute();
  

      //  検索結果のノードをループして更新
      $nodes = Node::loadMultiple($nids);
      $total_nodes += count($nodes);
  
      foreach ($nodes as $node) {
        $body_value = $node->body->value;
  
        $new_body_value = str_replace(['delicious', 'https://www.drupal.org'], ['yummy', 'https://WWW.DRUPAL.ORG'], $body_value);

        // ボディ値が実際に変更されたかどうかをチェック
        if ($new_body_value !== $body_value) {
          $updated = true;
          // 更新されたボディ値をエンティティに戻す。
          $node->body->value = $new_body_value;
          $node->save();

          $updated = true;
          $success_nodes_count++;
        } 
      }
  
      $current_page++;
      //検索結果がなくなるまで処理を繰り返す
    } while (!empty($nodes));
  
    // 処理結果のログ：成功
    if ($updated) {
      $this->logger()->success('Successfully updated!');
    } else {
      // 処理結果のログ：失敗
      $this->logger()->error('No nodes were found that contain a content type ("basic page" or "article") with a string ("delicious" or "https://www.drupal.org") in the field "body".');
    }
  
    // タスクの処理を終了ログ
    $this->logger()->notice(sprintf('%d of %d content types updated.', $success_nodes_count, $total_nodes));
  
    // 終了時間を取得
    $endTime = microtime(true);
  
    // 開始と終了の時間を計算し表示
    $elapsedTime = $endTime - $startTime;
    $this->logger()->notice(sprintf("Execution time: %.2f seconds", $elapsedTime));
  
    $this->logger()->notice('END');
  }
}
```

Drushコマンドを利用して、登録済みのコンテンツを編集・保存します。具体的なコマンドの使用方法は以下の通りです。
```bash
drush my_drush_module01:update_body
```

or
```bash
drush mdm01
```

このコマンドは、「基本ページ」と「記事」コンテンツタイプの「body」フィールドに入力された文字列"delicious"を"yummy"に、"https://www.drupal.org"を"https://WWW.DRUPAL.ORG"に変更します。


# 高速化の結果

* 処理時間1：1189.03 seconds(バッチサイズ100)
* 処理時間2：1036.63 seconds(バッチサイズ1000)
* 処理時間3：1018.32 seconds(バッチサイズ500)


バッチ処理を導入することで、実行時間は約18分(1081.32 seconds)に短縮されました。これは、元の実行時間の約2分の減少です。


