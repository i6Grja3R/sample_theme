<?php
/*
 固定ページ お問い合わせ
 お問い合わせフォーム メール送信
*/
// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

// 不正アクセスチェック
if (!$noindexaccess) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

#-------------------------------------------------------------------------------------------
# メール送信処理１（お客様への返信メール）
#-------------------------------------------------------------------------------------------
// メール本文

/*
$subject = $_POST['subject'];
$namae = $_POST['namae'];
$email = $_POST['email'];
$message = $_POST['message'];
$action = $_POST['action'];
*/

$mailbody = "この度はお問い合わせいただきありがとうございます。
1週間以上経過しても返信がない場合は、
お手数をお掛けいたしますが、下記までお問い合わせください。

件名：{$subject}
お名前：{$namae}
メールアドレス：{$email}

────────────────────────────────
　＜お問い合わせ先＞
　" . WEBMST_NAME . "
　E-MAIL: " . WEBMST_MAIL . "
────────────────────────────────";

// 件名とフッター
$mail_subject = WEBMST_NAME . ' お問い合わせ自動返信：' . $subject;
$headers = "Reply-To: " . mb_encode_mimeheader(WEBMST_NAME) . "<" . WEBMST_MAIL . ">\n";
$headers .= "Return-Path: " . WEBMST_MAIL . "<" . WEBMST_MAIL . ">\n";
$headers .= "From:" . mb_encode_mimeheader(WEBMST_NAME) . "<" . WEBMST_MAIL . ">\n";

// メール送信（失敗時：強制終了）
$usrmail_result = mb_send_mail($email, $mail_subject, $mailbody, $headers);
if (!$usrmail_result) die("お客様へのメール送信に失敗しました。<br />\n
                         誠に申し訳ございませんがこちらまでご連絡ください。“" . WEBMST_MAIL . "”");

#-------------------------------------------------------------------------------------------
# メール送信処理２（送信先は $mailto宛）
#-------------------------------------------------------------------------------------------
// 件名を設定
$subject = WEBMST_NAME . "お問い合わせ自動返信" . $_POST['subject'];

// Headerとbodyとsubjectを設定（送信元はお客様 $email）
// Header設定
// ※Reply-To はユーザーのメールアドレスにする
$headers = "Reply-To: " . $email . "\n";

// ※Return-Path はサイトメール
$headers .= "Return-Path: " . WEBMST_MAIL . "\n";

// ※From はサイトメール
$headers .= "From:" . mb_encode_mimeheader(WEBMST_NAME) .
    "<" . WEBMST_MAIL . ">\n";

// メール本文
$mailbody = "サイトよりお問い合わせを受け付けました。

────────────────────────────────

■名前： $namae 様より

■メールアドレス： $email

■メッセージ：
$message

────────────────────────────────
";

// メール送信実行
if (!empty($mailto)) {
    $sendmail_result = mb_send_mail($mailto, $subject, $mailbody, $headers);

    if (!$sendmail_result) {
        die("<p>メール送信に失敗しました。<br>\n誠に申し訳ございませんが最初から操作をやり直してください。</p>");
    }
} else {
    die("<p>メールを送信する事が出来ませんでした。<br>\n誠に申し訳ございませんが“" . WEBMST_MAIL . "”へ直接メールにて<br>お問い合わせしていただけますようお願い申し上げます。</p>");
}
