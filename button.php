<?php
// set_query_var() + get_query_var() を使ってテンプレートにデータを渡す
// バリデーション & フォールバック
// $post_id = intval(get_query_var('post_id')); // intval()でどんな値「文字列」でも整数に変換してから計算
// $user_id = get_query_var('user_id');
$unique_id = get_query_var('unique_id'); // 例: ?unique_id=5b4cd832-fbdf-11ef-bf39-525400c78958
$user_id = sanitize_text_field($user_id_raw); // ユーザー入力データを安全に処理するために使用

// DBへの問い合わせ
// データ取得（$is_liked / $good_count の事前取得	無駄なDBアクセスを減らしつつ可読性も向上。）
// 指定された $user_id のユーザーが、指定された $post_id の投稿に「いいね」しているか確認します。
// isGood() は true または false を返します。
$is_liked = isGood($user_id, $unique_id);
// 指定された投稿IDに対する「いいね」の全レコードをDBから取得します。
// getGood() は一度だけ呼び、count() にも使い回すことで、無駄なDBアクセスを減らす。
$good_entries = getGood($unique_id);
// 万が一 $good_entries が null や false の場合に備えて安全対策。
// 配列であればその数（＝いいね数）を取得、それ以外は 0 を返す。
$good_count = is_array($good_entries) ? count($good_entries) : 0;

// クラスや属性の安全な出力
// いいね済みなら 'quest-likeButton active' に。していなければ 'quest-likeButton' のまま。
$button_classes = 'quest-likeButton' . ($is_liked ? ' active' : '');
// SVGアイコンの見た目変更用。例えば、liked は赤色のハート、liker は灰色のハートなど。
$icon_classes = $is_liked ? 'liked' : 'liker';
// いいね数を表示する <span> に付けるクラス。たとえば、like over は強調表示、like cancel は淡く表示するなど。
$count_classes = $is_liked ? 'like over' : 'like cancel';
?>

<button
    class="<?php echo esc_attr($button_classes); ?>"
    data-uniqueid="<?php echo esc_attr($unique_id); ?>"
    aria-label="Like button">
    <svg version="1.1" id="レイヤー_1" class="likeButton-icon <?php echo esc_attr($icon_classes); ?>"
        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
        y="0px" viewBox="0 0 256 256" style="enable-background:new 0 0 256 256;" xml:space="preserve">
        <style type="text/css">
            .st0 {
                fill: #FFFFFF;
                stroke: #000000;
                stroke-width: 8.7931;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-miterlimit: 10;
            }
        </style>
        <path class="st0" d="M101.5,175.5c3.9,5.9,16.6,9.3,16.6,9.3h58.6c13.2-4.4,5.9-17.1,5.9-17.1s7.8-1,11.2-9.3
	c3.4-8.3-3.4-12.7-3.4-12.7s10.3-1.5,10.3-11.2c0-12.2-11.7-10.3-11.7-10.3s10.7,1,10.7-11.7s-11.2-10.7-11.2-10.7h-40.1
	c0,0,2.9-8.3,3.4-14.7c0.5-6.4,2.9-18.6-7.8-28.8s-17.1-4.4-17.1-4.4v22.5c0,0,0.1,5.1-3.4,10.1l-15.3,29.7l-6.7,4.6" />
        <path class="st0" d="M101.5,120.8v59.6c0,0,0.5,7.3-4.9,7.3H65.4c0,0-6.4-0.5-6.4-4.9v-60.1c0,0,0.5-8.3,4.9-8.3h30.8
	C94.7,114.4,101.5,113.4,101.5,120.8z" />
    </svg>
    <span class="likeCount <?php echo esc_attr($count_classes); ?>">
        <?php echo esc_html($good_count); ?>
    </span>
</button>