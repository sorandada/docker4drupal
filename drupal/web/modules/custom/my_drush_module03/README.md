# Drupal Drush Command Module 03

このリポジトリはDrupalのコマンドラインインタフェイス「Drush」用のカスタムコマンドを含むモジュールを提供します。

# 機能
URLエイリアス「/recipes/*」の「Recipe」コンテンツタイプの「recipe_instruction」フィールドに入力された文字列"minutes"を"mins"に変更します。

* /* は「/から始まる全てのURL」を表現しています。

# 目的

登録済みのコンテンツを編集・保存するためのDrushコマンドを提供します。対象URLはコンテンツに設定されたURL Aliasで、対象言語は英語です。さらに、進行状況や処理結果のログ出力、大量のコンテンツに対するパフォーマンスの検証・考察についても取り組みます。

# インストール
PC：Windows10 Home

Dockerを使用してDrupalの環境を構築しました。詳細な手順は「環境構築ヒント」を参照しました。

参考資料1：[Docker4Drupal を使用したローカル環境](!https://wodby.com/docs/1.0/stacks/drupal/local/)


## 手順

1. WindowsにDocker Desktop for Windowsをインストールする

2. WindowsにWSL2をインストールし、Ubuntu 22を利⽤できるようにする

3. Ubuntu 22上にdocker, docker-compose, makeをインストールする

4. https://github.com/wodby/docker4drupal をgit cloneする

5. docker-compose.override.ymlを編集する ( 3カ所ある「codebase:/var/www/html」を「./drupal:/var/www/html 」に変更 )

6. “make up”コマンドで起動

7. http://drupal.docker.localhost:8000/ を開く


# Requirement

* Windows10 Home
* wsl 2
* Ubuntu 22.04
* Docker version 20.10.24, build 297e128
* Docker Compose version v2.17.2
* GNU Make 4.3
* その他は[スタック](https://github.com/wodby/docker4drupal)で確認

# 使い方

docker-compose exec --user 82 php vendor/bin/drush XXX を　drush XXX　に短縮
```bash
alias drush='docker-compose exec --user 82 php vendor/bin/drush'
```

カスタムモジュールの有効化
```bash
drush en my_drush_module03 -y
```

Drushコマンドを利用して、登録済みのコンテンツを編集・保存します。具体的なコマンドの使用方法は以下の通りです。
```bash
drush my_drush_module03:update_recipe_instruction
```

or
```bash
drush mdm03
```

このコマンドは、URLエイリアス「/recipes/*」の「Recipe」コンテンツタイプの「recipe_instruction」フィールドに入力された文字列"minutes"を"mins"に変更します。


# ログ出力

進行状況や処理結果のログは、Drushコマンドを実行した際のコンソールに出力されます。

# パフォーマンス検証・考察
大量のコンテンツ（例えば5万件）に対するパフォーマンスを検証し、考察します。詳細な結果と考察は「[performance.md]()」を参照してください。


# Author

* Sora Nakaza
* NIT
* s202065@nishitech.ac.jp

# License

"hoge" is under [MIT license](https://en.wikipedia.org/wiki/MIT_License).
