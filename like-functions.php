<?php
if (!defined('ABSPATH')) {
    exit; // WordPress 以外からの直接アクセスを防止
}
// inc は **include（含める）**の略で、WordPressテーマやプラグインで「処理専用のファイル」をまとめるディレクトリとしてよく使われます。
// inc/ 以下のPHPファイルはテンプレートファイルではないので、直接アクセスされない構造にしやすい。というセキュリティ上のメリットがあります。
// 指定ユーザーが特定の投稿に「いいね」したかどうかを確認する関数。
function isGood($user_id, $post_id)
{
    global $wpdb;
    return $wpdb->get_var(
        // good テーブルに、該当ユーザーと投稿の組み合わせが存在するかを調べます。
        $wpdb->prepare("SELECT COUNT(*) FROM good WHERE unique_id = %s AND post_id = %d", $user_id, $post_id)
        // 結果が 1 件以上なら true を返します。
    ) > 0;
}

// wpdb::get_results() を使用して good テーブルから投稿IDに一致する行をすべて取得し、結果を「オブジェクトの配列」として返すもの。
function getGood($post_id)
{
    global $wpdb;
    // get_results() は、SQLクエリの結果が複数行になることを前提にしている。
    return $wpdb->get_results(
        // 特定の投稿に対する「いいね」全体（全ユーザー分）を取得。
        $wpdb->prepare("SELECT * FROM good WHERE post_id = %d", $post_id)
    );
}

//「ユーザーが投稿に対して『いいね』をした」という情報を、データベースの good テーブルに新しく記録（挿入）します。
function insertGood($user_id, $post_id)
{
    global $wpdb;
    return $wpdb->insert('good', [ // 連想配列の形で、カラム名をキー、対応する値をバリューとして指定します。
        'unique_id'    => sanitize_text_field($user_id), // 「いいね」したユーザーの一意のID（UUIDなど）を格納します。ユーザーIDの中に悪意のある文字やタグが混入しないようにサニタイズ（無害化）しています。
        'post_id'      => intval($post_id), // 「いいね」された投稿のIDを整数型に変換して格納しています。intval() で数字として扱い、SQLインジェクションや型の不整合を防ぎます。
        'created_date' => current_time('mysql') // 「いいね」した日時をMySQLの日時フォーマット（YYYY-MM-DD HH:MM:SS）で現在時刻を記録しています。
    ]);
}

// 特定のユーザーが特定の投稿に対してつけた「いいね（good）」を削除する処理です。
function deleteGood($user_id, $post_id)
{
    global $wpdb;
    return $wpdb->delete('good', [
        'unique_id' => sanitize_text_field($user_id), // いいねしたユーザーのID（通常はUUIDやログイン中のセッションIDなど）
        'post_id'   => intval($post_id) // いいねされた投稿のID（数値）
    ]);
}
