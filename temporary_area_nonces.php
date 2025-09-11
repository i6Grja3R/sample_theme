<?php
/**
 * bbs_nonces.php
 * JS へ AJAX URL と Nonce を配布するだけの小さなモジュール。
 * - ハンドル名 'bbs-js-handle' のスクリプトがキューに積まれている前提。
 * - submit/confirm で別アクション名にして CSRF を分離。
 */

if (!defined('ABSPATH')) { exit; }                              // 直接アクセス防止

add_action('wp_enqueue_scripts', function () {                  // フロントのスクリプトを吐く段階で実行
    if (!wp_script_is('bbs-js-handle', 'enqueued')) return;     // 指定ハンドルが enqueued でなければ何もしない
    wp_localize_script('bbs-js-handle', 'bbs_vars', [           // JS 変数 bbs_vars を定義
        'ajax_url'       => admin_url('admin-ajax.php'),        // AJAX 送信先 URL
        'nonce_submit'   => wp_create_nonce('bbs_quest_submit'),// submit 用 Nonce
        'nonce_confirm'  => wp_create_nonce('bbs_quest_confirm')// confirm 用 Nonce
    ]);
});
?>
