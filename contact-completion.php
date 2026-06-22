<?php
/*
 固定ページ お問い合わせ
 お問い合わせフォーム 完了画面
^\*/
// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

echo '<link rel="stylesheet" href="' . esc_url(get_stylesheet_directory_uri() . '/css/style.css') . '?v=' . filemtime(get_stylesheet_directory() . '/css/style.css') . '">';

// 不正アクセスチェック
if (!$noindexaccess) {
    header("HTTP/1.0 404 Not Found");
    exit();
}
?>
<div class="contact-wrap">

    <div class="contact-title">
        お絵描き民 お問い合わせ
    </div>

    <div class="contact-complete">

        <p class="contact-complete-message">
            メールが正常に送信されました。<br>
            後日メールにてご連絡させていただきます。<br>
            ご返信に時間がかかる場合がございますのでご了承ください。
        </p>

        <a href="<?php echo esc_url(home_url('/')); ?>" class="contact-finish-btn">
            トップページへ戻る
        </a>

    </div>

</div>