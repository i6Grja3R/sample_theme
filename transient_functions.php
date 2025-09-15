<?php

/**
 * フロントJSの読み込みと、AJAX用 nonce / admin-ajax.php の注入
 * - submit と confirm で “別アクション名” の nonce を配布
 * - テンプレートが bbs_quest_input.php のページだけで読み込み（必要に応じて外してください）
 */
add_action('wp_enqueue_scripts', function () {

    // ▼ このテンプレートのページだけで実行したい場合（任意）
    if (function_exists('is_page_template') && !is_page_template('transient_input.php')) {
        return; // 別ページでは何もしない
    }

    // ▼ フロントJSを enqueue（パスはあなたの環境に合わせて変更）
    //   第1引数: ハンドル名（JS識別子）
    //   第2引数: URL（テーマ配下の /js/bbs.js を想定）
    //   第3引数: 依存（jQuery不要なら空配列 [] でOK）
    //   第4引数: バージョン（キャッシュ対策。ファイル更新時に変更推奨）
    //   第5引数: フッターで読み込むか（true 推奨）
    wp_enqueue_script(
        'bbs-js-handle',
        get_template_directory_uri() . '/js/bbs.js',
        [],             // ['jquery'] が必要ならここに追加
        '1.0.0',
        true
    );

    // ▼ submit 用に配る値（admin-ajax のURL と nonce）
    //   JS 側からは window.bbs_vars.ajax_url / window.bbs_vars.nonce で参照
    wp_localize_script('bbs-js-handle', 'bbs_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bbs_quest_submit'),   // ← サーバ側の wp_verify_nonce(...,'bbs_quest_submit') と一致させる
    ]);

    // ▼ confirm 用に配る値（別アクションの nonce）
    //   JS 側からは window.bbs_confirm_vars.ajax_url / window.bbs_confirm_vars.nonce で参照
    wp_localize_script('bbs-js-handle', 'bbs_confirm_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bbs_quest_confirm'),  // ← サーバ側の wp_verify_nonce(...,'bbs_quest_confirm') と一致させる
    ]);
});
