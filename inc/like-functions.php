<?php
/*
Template Name: lile-functions
固定ページ: データベース操作
*/
?>
<?php
if (!defined('ABSPATH')) exit; // WordPress 以外からの直接アクセスを防止

// 引数 $user_id：Cookieなどで一意に識別されたユーザーID（ログインしていないユーザーでも可）。
// 引数 $unique_id：投稿や質問など、対象のコンテンツを一意に識別するID。
function isGood($user_id, $unique_id)
{
    global $wpdb;
    // 通常、$wpdb->prefix は 'wp_' なので、wp_good というテーブルを指定していることになります。
    $table = $wpdb->prefix . 'good';
    // プレースホルダー %s に対して、それぞれ $user_id, $unique_id を安全に埋め込むSQL文を生成します。つまり SQL インジェクション対策になります。
    // (bool) ...結果の数字（0または1以上）をboolean型（true/false）に変換します。
    return (bool) $wpdb->get_var($wpdb->prepare( // SQLの実行結果から、最初の1つの値（ここではCOUNTの結果）を取得します。
        "SELECT COUNT(*) FROM $table WHERE user_id = %s AND unique_id = %s",
        $user_id,
        $unique_id
    ));
}

// insertGood() は「誰（user_id）が、どの投稿（unique_id）に、いつ（created_date）いいねしたか」を good テーブルに記録します。
function insertGood($user_id, $unique_id)
{
    global $wpdb;
    // 使用するテーブル名を作成。
    $table = $wpdb->prefix . 'good';
    // この行は、$table（= wp_good）テーブルにデータを挿入します
    $wpdb->insert($table, [
        'user_id' => $user_id, // いいねしたユーザーの識別子
        'unique_id' => $unique_id, // いいね対象の一意な識別子（例：投稿IDやUUIDなど）
        'created_date' => current_time('mysql', 1) // いいねをした日時（WordPressの現在時刻、UTCかローカル）1 を渡すことで「GMT（UTC）」の時刻になります。
    ]);
}

// いいねを削除
function deleteGood($user_id, $unique_id)
{
    global $wpdb;
    // WordPressのデータベースアクセス用オブジェクトを使うための宣言。
    $table = $wpdb->prefix . 'good';
    // $user_id と $unique_id が一致するレコードを削除（= いいねの取り消し）。
    $wpdb->delete($table, [
        'user_id' => $user_id,
        'unique_id' => $unique_id
    ]);
}

// 特定の投稿（unique_id）に対して付けられた「いいね」情報を すべて取得 する関数です。
function getGood($unique_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'good';
    // SQLの結果を配列で取得して返します（複数行ある場合に最適）。
    return $wpdb->get_results($wpdb->prepare(
        // SQLインジェクション対策を行いながら、安全に値をSQLに埋め込む方法です。
        "SELECT * FROM $table WHERE unique_id = %s",
        $unique_id
    ));
}
