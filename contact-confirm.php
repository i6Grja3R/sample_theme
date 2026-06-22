<?php
/*
 固定ページ お問い合わせ
 お問い合わせフォーム 確認画面
*/
// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

echo '<link rel="stylesheet" href="' . esc_url(get_stylesheet_directory_uri() . '/css/style.css') . '?v=' . filemtime(get_stylesheet_directory() . '/css/style.css') . '">';

// 不正アクセスチェック
if (!$noindexaccess) {
  header("HTTP/1.0 404 Not Found");
  exit();
} ?>

<script>
  //2重送信防止スクリプト
  var flg_Submit = false;

  function Fnk_DoubleSubmit() {
    if (flg_Submit) {
      alert("処理中です。");
      return false;
    } else {
      flg_Submit = true;
      return true;
    }
  }
</script>

<?php
$subject_value = isset($subject) ? htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') : '';
$namae_value   = isset($namae) ? htmlspecialchars($namae, ENT_QUOTES, 'UTF-8') : '';
$email_value   = isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '';
$message_value = isset($message) ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : '';
?>

<?php
echo '<form class="contact-confirm-form" name="toiawase2" method="post" action="' . esc_url(home_url('/contact/')) . '" onsubmit="return Fnk_DoubleSubmit();">';

echo '<div class="contact-title">お絵描き民 お問い合わせ</div>';

echo '<p class="contact-confirm-message">入力された内容をご確認の上、送信ボタンをクリックしてください。</p>';

echo '<div class="contact-confirm-table">';

echo '<div class="contact-confirm-row">';
echo '<div class="contact-confirm-label">件名</div>';
echo '<div class="contact-confirm-value">' . $subject_value . '</div>';
echo '<input name="subject" type="hidden" value="' . $subject_value . '">';
echo '</div>';

echo '<div class="contact-confirm-row">';
echo '<div class="contact-confirm-label">お名前</div>';
echo '<div class="contact-confirm-value">' . $namae_value . '</div>';
echo '<input name="namae" type="hidden" value="' . $namae_value . '">';
echo '</div>';

echo '<div class="contact-confirm-row">';
echo '<div class="contact-confirm-label">メールアドレス</div>';
echo '<div class="contact-confirm-value">' . $email_value . '</div>';
echo '<input name="email" type="hidden" value="' . $email_value . '">';
echo '</div>';

echo '<div class="contact-confirm-row">';
echo '<div class="contact-confirm-label">メッセージ</div>';
echo '<div class="contact-confirm-value">' . nl2br($message_value) . '</div>';
echo '<input name="message" type="hidden" value="' . $message_value . '">';
echo '</div>';

echo '</div>';

echo '<div class="contact-confirm-buttons">';
echo '<input type="button" value="前に戻る" onclick="history.back();">';
echo '<input type="submit" value="送信する">';
echo '</div>';

wp_nonce_field('contact_completion', 'contact_nonce');

echo '<input type="hidden" name="js_enabled" value="1">';
echo '<input type="hidden" name="action" value="completion">';
echo '</form>';
?>