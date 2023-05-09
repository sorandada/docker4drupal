# パフォーマンス検証・考察

このドキュメントでは、大量のコンテンツ（例えば5万件）に対するパフォーマンスの検証と考察・高速化を記述します。

# 検証方法
## 1. コンテンツを5万件(「基本ページ(2.5万件)」「記事(2.5万件)」)生成し、その中のbodyフィールドに"delicious"と"https://www.drupal.org"を含むように設定します。

カスタムモジュール(コンテンツを作成)の有効化
```bash
drush en mass_content_creation -y
```

どちらかのコマンドでコンテンツを作成できます。
```bash
drush my_drush_module01:create_content page,article 25000 "test" "delicious https://www.drupal.org"
```

or
```bash
drush mcc page,article 25000 "test" "delicious https://www.drupal.org"
```

## 2. 以下のコードをコピペし、実行。

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


  public function updateBody() {

    // タスクの処理を開始ログ
    $this->logger()->notice('Starting task...');

    // 開始時間を取得
    $startTime = microtime(true);

    // 全てのコンテンツタイプを取得
    $all_node_query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->execute();
    $all_node = Node::loadMultiple($all_node_query);

    // フィールド「body」に文字列"delicious"または"https://www.drupal.org"が含まれるコンテンツタイプ(「基本ページ」または「記事」)をすべて読み込む.
    $query = \Drupal::entityQuery('node')
      ->condition('type', ['page', 'article'], 'IN');
    
    $or_group = $query
      ->orConditionGroup()
      ->condition('body.value', 'delicious', 'CONTAINS')
      ->condition('body.value', 'https://www.drupal.org', 'CONTAINS');
    
    $query = $query
      ->condition($or_group)
      ->accessCheck(FALSE)
      ->execute();

    $nodes = Node::loadMultiple($query);

    $updated = false; // ノードが更新されたかどうかをチェック
    $total_nodes = count($all_node);
    $success_nodes_count = 0;

    if (!empty($nodes)){
      // 各ノードのbodyフィールドを更新.
      foreach ($nodes as $node) {
        // ボディフィールドの値を取得
        $body_value = $node->get('body')->value;
        // "delicious" を "yummy"に、"https://www.drupal.org" を "https://WWW.DRUPAL.ORG"に変換する.
        $new_body_value = str_replace(['delicious', 'https://www.drupal.org'], ['yummy', 'https://WWW.DRUPAL.ORG'], $body_value);

        // ボディ値が実際に変更されたかどうかをチェック
        if ($new_body_value !== $body_value) {
          $updated = true;
          // 更新されたボディ値をエンティティに戻す。
          $node->get('body')->value = $new_body_value;
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


# 検証結果

* 処理時間1：1269 seconds
* 処理時間2：1193.2 seconds
* 処理時間3：1131.2 seconds


実行時間は約20分(1197.8 seconds)でした。全てのコンテンツが正しく更新され、エラーは発生しませんでした。

# 考察

大量のコンテンツを処理する際には、実行時間が大幅に増加します。これは、各コンテンツを個別に処理する必要があるためです。

この問題を改善するために、バッチ処理を導入することを検討しました。


