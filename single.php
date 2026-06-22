<?php
/*
Template Name: 投稿ページ
Template Post Type: post
*/
require_once get_template_directory() . '/display.php';
set_template_info();
?>
<?php
if (is_single() && !is_user_logged_in() && !isBot()) { // 個別記事 かつ ログインしていない かつ 非ボット
    set_post_views_days(); //アクセスをカウントする
}
?>

<?php get_header(); ?>
<!--ここから自作-->
<div id="blog-box" class="clearfix">
    <!-- ブログ開始 -->
    <!-- ▼　週間ランキング ▼ -->
    <?php display_week_ranking(); ?>
    <div id="main-box">
        <!-- メインボックス開始 -->
        <!--メイン左ボックス-->
        <div id="left-box">
            <!--週間カテゴリーランキング-->
            <div class="popular-keywords">
                <div class="side-title">人気キーワード(pop keywords)</div>
                <?php display_category_ranking(); ?>
            </div>
            <!--3日間ランキング-->
            <?php display_3day_ranking(); ?>
            <!--最近のコメント-->
            <?php display_comment(); ?>
            <!--アーカイブ-->
            <?php display_archive(); ?>
            <p id="sampleOutput"></p>
        </div><!-- /#left-box -->
        <!-- ▼　RSS記事右 ▼ -->
        <div id="right-box">
            <!-- ▼　RSS記事上 ▼ -->
            <!-- ▼　検索欄 ▼ -->
            <!--get_search_form()-->
            <?php display_search_form(); ?>
            <?php
            // 記事投稿コンテンツ表示
            function bbs_display_matome_block($text_key, $image_key)
            {
                // 取得した「現在の投稿オブジェクト」を変更することが危険です。
                global $post;

                // ループ内で使用して投稿IDを取得する関数
                // $post があるなら使う→無ければ get_the_ID()
                $post_id = isset($post->ID) ? (int) $post->ID : get_the_ID();

                // 投稿や固定ページの下段にあるカスタムフィールドのデータを取得する関数
                $text      = get_post_meta($post_id, $text_key, true);
                $image_url = get_post_meta($post_id, $image_key, true);

                if ($text === '' && $image_url === '') {
                    return '';
                }

                $html = '<div class="matome-box">';

                if ($text !== '') {
                    $colors = [
                        '#e57fa0',
                        '#6699ff',
                        '#001fcf',
                        '#ff66cc',
                        '#ff3399'
                    ];

                    // 引数の配列のキーをランダムで返す
                    $random_color = $colors[array_rand($colors)];

                    $html .= '<div class="matome-text">';
                    $html .= '<span style="color:' . esc_attr($random_color) . ';">';
                    $html .= nl2br(esc_html($text));
                    $html .= '</span>';
                    $html .= '</div>';
                }

                if ($image_url !== '') {
                    // ワードプレスのuploadsフォルダの情報を取得する
                    $upload_dir = wp_upload_dir();
                    // URLの末尾にスラッシュ（/）を付けるかどうかをWordPressの設定に従って自動で調整する関数
                    $baseurl = trailingslashit($upload_dir['baseurl']);
                    // データベース保存や内部処理用にURLをエスケープするための関数
                    $image_url = esc_url_raw($image_url);

                    // 先頭一致 → 0
                    $is_own_upload = strpos($image_url, $baseurl) === 0;
                    $is_image_file = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $image_url);

                    if ($is_own_upload && $is_image_file) {
                        $html .= '<div class="matome-image">';
                        $html .= '<img src="' . esc_url($image_url) . '" alt="" class="matome-img" loading="lazy">';
                        $html .= '</div>';
                    }
                }

                $html .= '</div>';

                return $html;
            }

            function bbs_display_matome_lines($text_key)
            {
                global $post;

                $post_id = isset($post->ID) ? (int) $post->ID : get_the_ID();
                // 指定されたカスタムフィールド（$text_key）から、テキストデータを1つの文字列として取得
                $text = get_post_meta($post_id, $text_key, true);

                if ($text === '') {
                    return '';
                }

                // 1行以上の空行（改行コード \R とその間のスペース \s*）を区切り文字として、テキストをレスごとのブロックに分割
                $blocks = preg_split('/\R\s*\R/u', trim($text));

                // 出力用のHTML文字列を初期化（まとめレス一覧を包む枠）
                $html = '<div class="matome-res-list">';

                // 分割された各レスのブロックを1つずつループ処理
                foreach ($blocks as $block) {
                    // ブロックの前後の不要な余白や改行を削除
                    $block = trim($block);

                    // 中身が空のブロックであれば、処理をスキップして次のブロックへ
                    if ($block === '') {
                        continue;
                    }

                    // ブロック内の改行コード（\R）ごとに、文字列を行単位の配列に分割
                    $lines = preg_split('/\R/u', $block);

                    // 配列から最初の1行（レス番号や名前などがパイプ「|」で区切られた行）を切り出し、残りを本文とする
                    $meta_line = array_shift($lines);
                    // 残った行（本文）を改行コード（\n）で再度組み立て、前後の余白を削除
                    $body = trim(implode("\n", $lines));

                    // メタ情報の行をパイプ記号「|」で分割し、配列にする
                    $meta_parts = explode('|', $meta_line);

                    // 各メタ情報を取得。存在しない（不足している）場合は初期値を設定し、前後の余白を削除
                    $res_no = isset($meta_parts[0]) ? trim($meta_parts[0]) : '';
                    $name   = isset($meta_parts[1]) ? trim($meta_parts[1]) : '名無しさん';
                    $date   = isset($meta_parts[2]) ? trim($meta_parts[2]) : '';
                    $id     = isset($meta_parts[3]) ? trim($meta_parts[3]) : '';

                    $html .= '<div class="matome-res">';

                    $html .= '<div class="matome-res-meta">';

                    // レス番号があれば、HTMLエスケープ（XSS対策）を施して追加
                    if ($res_no !== '') {
                        $html .= '<span class="matome-res-no">' . esc_html($res_no) . ':</span> ';
                    }
                    $html .= '<span class="matome-res-name">' . esc_html($name) . '</span> ';
                    if ($date !== '') {
                        $html .= '<span class="matome-res-date">' . esc_html($date) . '</span> ';
                    }
                    if ($id !== '') {
                        $html .= '<span class="matome-res-id">' . esc_html($id) . '</span>';
                    }
                    $html .= '</div>'; // メタ情報エリアの閉じタグ

                    // 本文が空でなければ、本文エリアの出力処理を行う
                    if ($body !== '') {

                        $body_colors = [
                            '#333', // 黒
                            '#006600', // 緑
                            '#dc143c', // 赤
                            '#0000cc', // 青
                            '#990099', // 紫
                            '#cc3300', // 茶
                            '#e52d77', // ピンク
                        ];

                        // 【バグ修正】本文のCRC32ハッシュを計算。32bit環境での負数エラーを防ぐためabs()で絶対値にし、
                        // カラーリストの総数で割った余りを使って、本文の内容に応じた一意の文字色（インデックス）を決定
                        $random_body_color = $body_colors[abs(crc32($body)) % count($body_colors)];

                        // 1. 先に本文全体をHTMLエスケープ（XSS対策）する
                        // 2. その後、エスケープ済みの「&gt;&gt;1文字以上の数字」を検索し、アンカー装飾用のspanタグで囲む置換を行う
                        $body = preg_replace(
                            '/&gt;&gt;([0-9]+)/',
                            '<span class="matome-anchor">&gt;&gt;$1</span>',
                            esc_html($body)
                        );

                        // 本文を表示するdivタグを追加。
                        // style属性に渡すカラーコードは安全のため esc_attr() でエスケープし、本文の改行コードを <br /> タグに変換（nl2br）して出力
                        $html .= '<div class="matome-res-body" style="color:' .
                            esc_attr($random_body_color) .
                            ';">' .
                            nl2br($body) .
                            '</div>';
                    }

                    $html .= '</div>';
                }

                $html .= '</div>';

                return $html;
            }

            //ここから追加
            $rss_table_name = get_rss_table_name(4);

            $allowed_tables = [
                'single_rss_feed',
                'double_rss_feed',
                'triple_rss_feed',
                'trisect_rss_feed',
            ];

            // 「許可されたRSSテーブル以外は使わせない」ためのセキュリティコード
            if (!in_array($rss_table_name, $allowed_tables, true)) {
                wp_die('不正なリクエストです。');
            }
            // var_dump($rss_table_name);

            $block_per_page = 3; /* ページ当たりブロック数 */
            $limitSect1 = 5; /* ひとつ目のRSS件数 */
            $limitSect2 = 4; /* ふたつ目のRSS件数 */
            $limitSect3 = 4; /* みっつ目のRSS件数 */
            $rss_per_block = $limitSect1 + $limitSect2 + $limitSect3; /* ブロックあたりRSS件数 */
            $rss_per_page = $block_per_page * $rss_per_block; /* ページ当たりRSS件数 */
            $rss_offset = 0;
            $sql = "SELECT * FROM {$rss_table_name} ORDER BY date DESC LIMIT %d,%d";
            $query = $wpdb->prepare($sql, $rss_offset, $rss_per_page);
            $rss_items = $wpdb->get_results($query);

            // echo '<div style="color:red; font-weight:bold;">RSS件数: ' . count($rss_items) . '</div>';

            $trisect_rss_feed = array();
            for ($i = 0; $i < $block_per_page; ++$i) {
                $contentA = '';
                $contentB = '';
                $contentC = '';
                for ($j = 0; $j < $rss_per_block; ++$j) {
                    $item_index = $i * $rss_per_block + $j;
                    if ($item_index >= count($rss_items)) {
                        break;
                    }
                    $item = $rss_items[$item_index];

                    /* RSSのURL・タイトル・画像・カテゴリを安全に出力する */
                    $link      = esc_url($item->link);
                    $title_t   = esc_html($item->title);
                    $subject_t = esc_html($item->subject);

                    if (empty($item->img)) {
                        $img = esc_url(home_url('/wp-content/uploads/2022/07/1-19.jpg'));
                    } else {
                        $img = esc_url($item->img);
                    }

                    $title   = "<strong><a href=\"{$link}\">{$title_t}</a></strong>";
                    $image   = "<a href=\"{$link}\"><img src=\"{$img}\" width=\"100\" alt=\"\"></a>";
                    $subject = "<a href=\"{$link}\">{$subject_t}</a>";
                    if ($j < $limitSect1) {
                        $contentA .= "<li class=\"sitelink\">{$title}</li>"; // タイトルのみ
                    } elseif ($j < $limitSect1 + $limitSect2) {
                        $contentB .= "<li class=\"sitelink2\"><figure class=\"snip\"><figcaption>{$image}<br>{$title}<p class=\"btn\">{$subject}</p></figcaption></figure></li>"; // 画像と画像の下にタイトル
                    } else {
                        $contentC .= '<li class="sitelink3">';
                        $contentC .= '<a class="sitelink3-link" href="' . $link . '">';
                        $contentC .= '<img src="' . $img . '" width="100" alt="">';
                        $contentC .= '<strong>' . $title_t . '</strong>';
                        $contentC .= '</a>';
                        $contentC .= '</li>';
                    }
                }
                $content = '<div class="rssBlock">';
                $content .= "<ul class=\"wiget-rss\">{$contentA}</ul>";
                $content .= "<ul class=\"wiget-rss\">{$contentB}</ul>";
                $content .= "<ul class=\"wiget-rss\">{$contentC}</ul>";
                $content .= '</div>';
                $trisect_rss_feed[] = $content;
            }

            /* RSS1 */
            echo $trisect_rss_feed[0];

            /* 広告 */
            echo '<span class="banner-1">
<a href="https://af.moshimo.com/af/c/click?a_id=3493027&p_id=2312&pc_id=4967&pl_id=38392&guid=ON"
rel="nofollow"
referrerpolicy="no-referrer-when-downgrade">
<img src="https://image.moshimo.com/af-img/1762/000000038392.png"
width="200"
height="200"
style="border:none;">
</a>
<img src="https://i.moshimo.com/af/i/impression?a_id=3493027&p_id=2312&pc_id=4967&pl_id=38392"
width="1"
height="1"
style="border:none;">
</span>';

            /* 記事タイトル */
            echo the_title('<h2 class="postpage-title">', '</h2>', true);

            /* メタ情報開始 */
            echo '<div class="postpage-meta">';

            /* 日付 */
            $date = get_the_time('Y/m/d');
            $mobile_date = get_the_time('Y/m/d H:i');

            echo '<div class="meta-item published">';
            echo '<span class="fa-clock fa-fw"></span>';
            echo '<span class="meta-text" data-mobile-date="' . esc_attr($mobile_date) . '">' . esc_html($date) . '</span>';
            echo '</div>';

            /* カテゴリ */
            echo '<div class="meta-item category-link">';
            echo '<span class="fa-folder fa-fw"></span>';
            echo '<span class="meta-text">';
            // 現在の投稿に設定されているカテゴリをリンク付きで表示する関数
            the_category(' ');
            echo '</span>';
            echo '</div>';

            /* コメント数 */
            echo '<div class="meta-item comment-count">';
            echo '<span class="fa-comment fa-fw"></span>';
            echo '<a href="#comments">';
            comments_number('0', '1', '%');
            echo '</a>';
            echo '</div>';

            echo '</div>';

            /* 記事前半 */
            echo '<div class="first-content">';
            // 記事を前半・後半に分けるコード
            echo wp_kses_post(get_extended($post->post_content)['main']);

            echo bbs_display_matome_lines('matome_text_before');
            echo '</div>';

            /* RSS2 */
            echo $trisect_rss_feed[1];

            /* 広告 */
            echo '<span class="banner-2">
<a href="https://af.moshimo.com/af/c/click?a_id=3493027&p_id=2312&pc_id=4967&pl_id=38392&guid=ON"
rel="nofollow"
referrerpolicy="no-referrer-when-downgrade">
<img src="https://image.moshimo.com/af-img/1762/000000038392.png"
width="200"
height="200"
style="border:none;">
</a>
<img src="https://i.moshimo.com/af/i/impression?a_id=3493027&p_id=2312&pc_id=4967&pl_id=38392"
width="1"
height="1"
style="border:none;">
</span>';

            /* 記事後半 */
            echo '<div class="secound-content">';
            echo wp_kses_post(get_extended($post->post_content)['extended']);

            echo bbs_display_matome_lines('matome_text_after');
            echo '</div>';

            /* RSS3 */
            echo $trisect_rss_feed[2];


            /* カスタムフィールドの取得 */
            $team = get_post_meta($post->ID, 'team', true);
            /* 投稿オブジェクトの取得 */
            if ('red' === $team) {
                $post_red = $post; /* 赤（現在） */
                $post_blue = get_adjacent_post(true, '', true); /* 青（現在の次） */
                $post = $post_blue; /* 現在を青に置きかえる */
                $post_green = get_adjacent_post(true, '', true); /* 緑（現在の次：青の次） */
                $post = $post_red; /* 現在を赤に戻す */
                $tn = 3;
            } elseif ('blue' === $team) {
                $post_blue = $post; /* 青（現在） */
                $post_red = get_adjacent_post(true, '', false); /* 赤（現在の前） */
                $post_green = get_adjacent_post(true, '', true); /* 緑（現在の次） */
                $tn = 3;
            } elseif ('green' === $team) {
                $post_green = $post; /* 緑（現在） */
                $post_blue = get_adjacent_post(true, '', false); /* 青（現在の前） */
                $post = $post_blue; /* 現在を青に置きかえる */
                $post_red = get_adjacent_post(true, '', false); /* 赤（現在の前：青の前） */
                $post = $post_green; /* 現在を緑に戻す */
                $tn = 3;
            } elseif ('white' === $team) {
                $post_white = $post; /* 白（現在） */
                $post_black = get_adjacent_post(true, '', true); /* 黒（現在の次） */
                $tn = 2;
            } elseif ('black' === $team) {
                $post_black = $post; /* 黒（現在） */
                $post_white = get_adjacent_post(true, '', false); /* 白（現在の前） */
                $tn = 2;
            } else {
                $post_single = $post;
                $tn = 1;
            }
            /* コメントオブジェクトの取得 */
            $args = [
                'author__not_in' => '1', /* 管理者を除く */
                'status' => 'approve', /* 承認済み */
                'type' => 'comment', /* コメント */
                'orderby' => '',/* 順番 */
            ];

            /* コメント見出し帯 */
            /* コメント数リンク href="#comments" の移動先 */
            echo '<h3 id="comments" class="comment-section-title">';
            echo '「' . esc_html(get_the_title()) . '」へのコメント';
            echo '</h3>';

            /**
             * コメント日時をYouTube風の相対表示へ変換
             * 例：1分前 / 2時間前 / 3日前 / 2週間前 / 4か月前 / 1年前
             */
            if (!function_exists('irasuto_comment_relative_time')) { // 【安全】関数の重複定義による致命的なシステムエラー（500エラー）を未然に防ぐお決まりの安全策
                function irasuto_comment_relative_time($comment_date_gmt)
                {
                    // 【安全】引数を強制的に文字列型にキャスト(型変換)し、前後の余計な空白を削除。
                    // 万が一、不正なオブジェクトや配列が送り込まれても、ここで安全な文字列に変換されるためシステムは落ちません。
                    $comment_date_gmt = trim((string) $comment_date_gmt);

                    // 【安全】データベース上の「空データ」や「不完全な初期値」を厳密（===）に検知。
                    if ($comment_date_gmt === '' || $comment_date_gmt === '0000-00-00 00:00:00') {
                        return ''; // 不正なデータはここで安全に弾き、これ以降の計算処理には進ませません（早期リターン）。
                    }

                    // comment_date_gmt はGMT日時なので、strtotime側にもGMTと明示する
                    // 【安全】単なる文字列の結合処理。SQLクエリではないため、SQLインジェクションなどの脆弱性は一切発生しません。
                    $comment_timestamp = strtotime($comment_date_gmt . ' GMT');

                    // 【安全】日時の解析に失敗（ゴミ文字列が渡された場合など）した際の戻り値「false」を型まで含めて厳格にチェック。
                    if ($comment_timestamp === false) {
                        return ''; // 解析できない不正データはここで安全に終了させます。
                    }

                    // 現在時刻との差分秒数を取得
                    // 【安全】万が一、未来の日時が渡されて計算結果が「マイナス」になっても、max(0, ...) によって強制的に「0」に補正。
                    // 想定外の負の数によるループバグやシステム異常を防ぐ優れた防衛策です。
                    $diff = max(0, time() - $comment_timestamp);

                    // 【安全】WordPressが内部で持っている安全な時間定数（DAY_IN_SECONDSなど）をベースに秒数を算出。
                    $week_in_seconds  = DAY_IN_SECONDS * 7;
                    $month_in_seconds = DAY_IN_SECONDS * 30;
                    $year_in_seconds  = DAY_IN_SECONDS * 365;

                    // 1. 【分前】1時間（3600秒）未満の判定
                    if ($diff < HOUR_IN_SECONDS) {
                        // 【安全】システム側で計算した数値と、固定文字列（'分前'）だけを結合して返しています。
                        // ユーザーが入力した悪意あるスクリプト（XSS）が紛れ込む余地が1ミリもないため、完全に安全です。
                        return max(1, (int) floor($diff / MINUTE_IN_SECONDS)) . '分前';
                    }

                    // 2. 【時間前】1日未満の判定
                    if ($diff < DAY_IN_SECONDS) {
                        return max(1, (int) floor($diff / HOUR_IN_SECONDS)) . '時間前'; // 【安全】外部入力値をそのまま出力しない安全な構造
                    }

                    // 3. 【日前】1週間未満の判定
                    if ($diff < $week_in_seconds) {
                        return max(1, (int) floor($diff / DAY_IN_SECONDS)) . '日前'; // 【安全】同上
                    }

                    // 4. 【週間前】1ヶ月未満の判定
                    if ($diff < $month_in_seconds) {
                        return max(1, (int) floor($diff / $week_in_seconds)) . '週間前'; // 【安全】同上
                    }

                    // 5. 【か月前】1年未満の判定
                    if ($diff < $year_in_seconds) {
                        return max(1, (int) floor($diff / $month_in_seconds)) . 'か月前'; // 【安全】同上
                    }

                    // 6. 【年前】1年以上が経過している場合
                    // 【安全】こちらも完全にシステムが生成した「〇年前」という固定テキストのみを返すため、100%安全です。
                    return max(1, (int) floor($diff / $year_in_seconds)) . '年前';
                }
            }

            /* コメントの表示 */
            if (3 === $tn) {
                display_single_comment($post_red, $comments_red);
                display_single_comment($post_blue, $comments_blue);
                display_single_comment($post_green, $comments_green);
            } elseif (2 === $tn) {
                display_single_comment($post_white, $comments_white);
                display_single_comment($post_black, $comments_black);
            } else {
                $comments_single = get_comments([
                    'post_id' => $post_single->ID,
                    'status'  => 'approve',
                    'orderby' => 'comment_date',
                    'order'   => 'ASC',
                ]);
                display_single_comment($post_single, $comments_single);
            }

            echo '<div id="comment-form-home">';

            /* WordPress標準のコメント投稿フォームを出力・カスタマイズする関数 */
            comment_form([
                // 返信エリアの上のタイトル（通常「コメントを残す」など）を空文字にして非表示にする
                'title_reply'          => '',
                // 他のコメントへの返信時に表示されるタイトル（通常「〜へ返信する」など）を空文字にして非表示にする
                'title_reply_to'       => '',
                // 返信を途中でやめたいときに表示される「キャンセル」のリンクテキストを指定
                'cancel_reply_link'    => '返信をキャンセル',

                // コメント本文を入力する textarea 欄のHTMLを独自のものに置き換える
                'comment_field' =>
                '<p class="comment-form-comment">' .
                    '<label for="comment">コメント</label>' .
                    // 必須入力（required）に指定し、最大文字数を1600文字までに制限
                    '<textarea id="comment" name="comment" required maxlength="800"></textarea>' .
                    '</p>',

                // 名前やメールアドレスなどの入力欄（fields）のカスタマイズ
                'fields' => [
                    // 「名前（制作者）」の入力欄のHTMLを独自のものに置き換える（※メール・URL欄は記述がないため非表示、またはデフォルトになります）
                    'author' =>
                    '<p class="comment-form-author">' .
                        '<label for="author">名前</label>' .
                        // 未入力時のプレースホルダーを「名無し」に設定、ブラウザの自動補完（name）を有効化、最大文字数を50文字までに制限
                        '<input id="author" name="author" type="text" value="" placeholder="名無し" autocomplete="name" maxlength="50">' .
                        '</p>',

                    // 「次回のコメントで使用するためブラウザーに自分の名前、メールアドレス、サイトを保存する。」を出さない
                    // このサイトではメールアドレス欄・サイトURL欄を使わないため、表示すると利用者に誤解を与える
                    'cookies' => '',
                ],

                // コメント送信ボタンに表示されるテキストを指定
                'label_submit' => 'コメントを投稿する',

                // フォームの前に表示される案内文（通常「メールアドレスは公開されません」など）を空文字にして非表示にする
                'comment_notes_before' => '',
                // フォームの後に表示される案内文（注意書きなど）を空文字にして非表示にする
                'comment_notes_after'  => '',
                // ログイン状態のユーザーに対して表示される案内文（通常「○○としてログイン中」など）を空文字にして非表示にする
                'logged_in_as'         => '',
            ]);

            echo '</div>';


            // 返信ツリー表示処理
            function display_comment_replies_tree($comments_by_parent, $parent_id, $comment_post_id, $reaction_counts, $depth = 1)
            {
                // ネスト（階層）が20を超えた際
                if ($depth > 20) {
                    return;
                }

                if (empty($comments_by_parent[$parent_id])) {
                    return;
                }

                // 変数 $depth の値と「3」を比較し、小さい方の値を出力する
                $display_depth = min($depth, 3);
                $reply_count = count($comments_by_parent[$parent_id]);

                echo '<div class="reply-list depth-' . esc_attr($display_depth) . '">';

                // 指定された親コメントID（$parent_id）に紐づく返信（子コメント）の配列をループ処理
                foreach ($comments_by_parent[$parent_id] as $index => $reply) {
                    // 返信コメントのIDを整数型（int）に強制変換して安全に取得
                    $reply_id = (int) $reply->comment_ID;
                    // コメント作成者の名前を取得。空であれば「名無し」をデフォルト値にする
                    $reply_author = empty($reply->comment_author) ? '名無し' : $reply->comment_author;
                    // データベース保存用の日時フォーマット（MySQL形式）を「年/月/日(曜日) 時:分:秒」の形式に変換
                    $reply_date = mysql2date('Y/m/d(D) H:i:s', $reply->comment_date);

                    // --- 各種CSSクラスの判定 ---
                    // 階層（深さ）が1（最初の返信層）かつ、10件目（インデックス9）以降の返信であれば、隠し要素用のクラスを付与
                    $extra_class = ($depth === 1 && $index >= 9) ? ' is-hidden-reply' : '';
                    // この返信コメントに対して、さらに「子コメント（孫コメント）」が存在するかどうかでクラスを分岐
                    $has_children_class = !empty($comments_by_parent[$reply_id]) ? ' has-children' : '';
                    // ループの最後（最後の返信）か、まだ後ろに続く返信があるかでクラスを分岐（※$reply_countは外側で定義されている前提）
                    $last_sibling_class = ($index === $reply_count - 1) ? ' is-last-sibling' : ' has-next-sibling';

                    // 返信コメント全体を包むdivタグを出力。各CSSクラスやIDは安全にHTML属性エスケープ（esc_attr）を適用
                    echo '<div class="reply-item' . esc_attr($extra_class . $has_children_class . $last_sibling_class) . '" id="div-comment-' . esc_attr($reply_id) . '">';

                    // メタ情報（現代風：名前 + 相対時間）を表示するエリア
                    // 「名前:」「投稿日:」「長い日付」は横幅を取りすぎるため出さない
                    echo '<div class="reply-meta-line">';
                    echo '<span class="comment-author-name">' . esc_html($reply_author) . '</span>';
                    echo '<span class="comment-relative-time">' . esc_html(irasuto_comment_relative_time($reply->comment_date_gmt)) . '</span>';
                    echo '</div>';

                    // 返信コメントの本文の先頭にある「>>123」のようなアンカー（レス番指定）の文字列を正規表現で削除
                    $reply_text = preg_replace(
                        '/^>>\d+\s*/',
                        '',
                        $reply->comment_content
                    );

                    // 不要なアンカーを削ったコメント本文をHTMLエスケープして出力
                    echo '<div class="reply-content">'
                        . esc_html($reply_text)
                        . '</div>';

                    // リアクション（いいね・ダメね）の件数を取得。データがなければ0を代入（?? はPHP7以降のヌル合体演算子）
                    $reply_good = $reaction_counts[$reply_id]['good'] ?? 0;
                    $reply_bad  = $reaction_counts[$reply_id]['bad'] ?? 0;

                    // リアクションボタンや返信リンクを配置する行を出力
                    echo '<div class="comment-action-row reply-action-row">';

                    // 「いいね（good）」ボタンの出力
                    echo '<button type="button" class="comment-reaction-button comment-good-button" data-comment-id="' . esc_attr($reply_id) . '" data-reaction="good">';
                    echo '<span class="material-icons">thumb_up</span>';
                    echo '<span class="comment-good-count">' . esc_html($reply_good) . '</span>';
                    echo '</button>';

                    // 「ダメね（bad）」ボタンの出力
                    echo '<button type="button" class="comment-reaction-button comment-bad-button" data-comment-id="' . esc_attr($reply_id) . '" data-reaction="bad">';
                    echo '<span class="material-icons">thumb_down</span>';
                    echo '<span class="comment-bad-count">' . esc_html($reply_bad) . '</span>';
                    echo '</button>';

                    // 返信フォーム（#respond）へスクロールさせるためのリンクを出力（JavaScript等で処理するためのdata属性付き）
                    echo '<a href="#respond"'
                        . ' class="custom-flat-reply-link"'
                        . ' data-rootid="' . esc_attr($reply_id) . '"'
                        // . ' data-mention=">>' . esc_attr($reply_id) . '"'
                        . '>返信</a>';

                    echo '</div>';

                    // もしこの返信コメントに、さらに深い下層コメント（孫コメントなど）が存在する場合の処理
                    if (!empty($comments_by_parent[$reply_id])) {
                        // 下層にあるコメントの総数を計算
                        $child_count = count($comments_by_parent[$reply_id]);

                        // 下層コメントを開閉するためのアコーディオンボタンを出力
                        echo '<button type="button" class="reply-toggle-button" data-close-text="' . esc_attr($child_count) . '件の返信">';
                        echo esc_html($child_count) . '件の返信';
                        echo '</button>';

                        // 下層コメントを表示するエリアを出力（初期状態は非表示クラス is-hidden-children が付いている）
                        echo '<div class="reply-children is-hidden-children">';
                        // 自分自身（この関数）を再帰的に呼び出すことで、何階層でも自動的にツリー構造を深掘りして出力する
                        display_comment_replies_tree($comments_by_parent, $reply_id, $comment_post_id, $reaction_counts, $depth + 1);
                        echo '</div>';
                    }

                    echo '</div>'; // 返信コメント1件を包むdivの閉じタグ
                }

                // 階層が1（最初の返信層）かつ、返信の総数が9件より多い場合、省略された残りの返信を表示するためのボタンを出力
                if ($depth === 1 && $reply_count > 9) {
                    echo '<button type="button" class="reply-more-button" data-open-text="返信を非表示" data-close-text="' . esc_attr($reply_count - 9) . '件の返信を表示">';
                    echo esc_html($reply_count - 9) . '件の返信を表示';
                    echo '</button>';
                }

                echo '</div>';
            }

            //コメント表示処理
            function display_single_comment($post, $comments)
            {
                global $wpdb;

                // コメントがない場合は何も表示しない
                if (empty($comments)) {
                    return;
                }

                echo '<ol>';

                $comments_by_parent = [];
                $comment_ids = [];

                foreach ($comments as $comment) {
                    $comment_id = (int) $comment->comment_ID;
                    $parent_id  = (int) $comment->comment_parent;

                    $comments_by_parent[$parent_id][] = $comment;
                    $comment_ids[] = $comment_id;
                }

                $reaction_counts = [];

                // 処理対象のコメントID配列（$comment_ids）が空でなければ、データベースからリアクション数を一括取得する
                if (!empty($comment_ids)) {

                    // コメントIDの数だけ「%d」（数値用のプレースホルダー）をカンマ区切りで生成（例: "%d,%d,%d"）
                    $placeholders = implode(',', array_fill(0, count($comment_ids), '%d'));

                    // 各コメントに紐づくリアクションの種類（good/badなど）ごとの件数を集計するSQL文を構築
                    $sql = "
        SELECT comment_id, reaction_type, COUNT(*) AS cnt
        FROM {$wpdb->prefix}comment_reactions
        WHERE comment_id IN ($placeholders)
        GROUP BY comment_id, reaction_type
    ";

                    // $wpdb->prepare でSQL文に安全に値を組み込み（SQLインジェクション対策）、クエリを実行して結果を取得
                    $rows = $wpdb->get_results(
                        $wpdb->prepare($sql, ...$comment_ids)
                    );

                    // 取得したデータベースの行をループ処理し、PHP側で扱いやすい連想配列に並び替える
                    foreach ($rows as $row) {
                        $cid  = (int) $row->comment_id; // コメントIDを整数型にキャスト
                        $type = $row->reaction_type;    // リアクションのタイプ（good または bad）

                        // $reaction_counts[コメントID][リアクションタイプ] = 件数 の形で保存
                        $reaction_counts[$cid][$type] = (int) $row->cnt;
                    }
                }

                // コメントの通し番号の初期値（※このコード内では直接表示に使われていませんが、カウント用）
                $comment_number = 1;

                // 親コメント（紐づく親がないコメント＝最上階層のコメント）の配列を取得。なければ空の配列を代入
                $parent_comments = $comments_by_parent[0] ?? [];

                // 親コメントを1件ずつループ処理して出力
                foreach ($parent_comments as $comment) {
                    // コメント作成者の名前を取得。空の場合は「匿名」をデフォルト値にする
                    $comment_author = empty($comment->comment_author)
                        ? '匿名'
                        : $comment->comment_author;

                    // コメントIDと、そのコメントが投稿された記事のIDを整数型にキャストして取得
                    $comment_id      = (int) $comment->comment_ID;
                    $comment_post_id = (int) $comment->comment_post_ID;

                    $colors = [
                        '#e57fa0',
                        '#6699ff',
                        '#001fcf',
                        '#ff66cc',
                        '#ff3399'
                    ];

                    // カラーリストの中からランダムに1つの色を決定（※このコード内ではまだ使用されていません）
                    $random_color = $colors[array_rand($colors)];

                    // リストアイテム（liタグ）と記事（articleタグ）の開始。ID属性は安全にエスケープして出力
                    echo '<li>';
                    echo '<article id="div-comment-' . esc_attr($comment_id) . '">';
                    // DBに入っているコメント日時→Y/m/d(D) H:i:s 形式に変換
                    $comment_date = mysql2date('Y/m/d(D) H:i:s', $comment->comment_date);

                    // メタ情報（現代風：名前 + 相対時間）を表示するエリア
                    // 「名前:」「投稿日:」「長い日付」は横幅を取りすぎるため出さない
                    echo '<div class="comment-meta-line">';

                    // 名前をHTMLエスケープして出力
                    echo '<span class="comment-author-name">'
                        . esc_html($comment_author)
                        . '</span>';

                    echo '<span class="comment-relative-time">'
                        . esc_html(irasuto_comment_relative_time($comment->comment_date_gmt))
                        . '</span>';

                    echo '</div>';

                    // コメント本文の先頭にある「>>123」のようなアンカー（レス指定）を正規表現で削除
                    $comment_text = preg_replace(
                        '/^>>\d+\s*/',
                        '',
                        $comment->comment_content
                    );

                    // 不要なアンカーを削ったコメント本文をHTMLエスケープして出力
                    echo '<div class="comment-body-text">'
                        . esc_html($comment_text)
                        . '</div>';

                    // リアクションボタンなどを配置するアクション行を出力
                    echo '<div class="comment-action-row">';

                    // このコメントの「いいね（good）」と「ダメね（bad）」の数を配列から取得。データがなければ0にする
                    $good_count = $reaction_counts[$comment_id]['good'] ?? 0;
                    $bad_count  = $reaction_counts[$comment_id]['bad'] ?? 0;

                    // 「いいね」ボタンの出力（各カスタムデータ属性は esc_attr で、カウント数は esc_html でエスケープ）
                    echo '<button type="button" class="comment-reaction-button comment-good-button" data-comment-id="' . esc_attr($comment_id) . '" data-reaction="good">';
                    echo '<span class="material-icons">thumb_up</span>';
                    echo '<span class="comment-good-count">' . esc_html($good_count) . '</span>';
                    echo '</button>';

                    // 「ダメね」ボタンの出力
                    echo '<button type="button" class="comment-reaction-button comment-bad-button" data-comment-id="' . esc_attr($comment_id) . '" data-reaction="bad">';
                    echo '<span class="material-icons">thumb_down</span>';
                    echo '<span class="comment-bad-count">' . esc_html($bad_count) . '</span>';
                    echo '</button>';

                    // echo '<a class="comment-reply-link" href="#respond" data-commentid="' . esc_attr($comment_id) . '" data-postid="' . esc_attr($comment_post_id) . '" data-belowelement="div-comment-' . esc_attr($comment_id) . '" data-respondelement="respond">返信</a>';
                    // 返信フォーム（#respond）へのリンクを出力
                    echo '<a href="#respond"'
                        . ' class="custom-flat-reply-link"'
                        . ' data-rootid="' . esc_attr($comment_id) . '"'
                        // . ' data-mention=">>' . esc_attr($comment_id) . '"'
                        . '>返信</a>';

                    echo '</div>'; // アクション行の閉じタグ

                    // 別で定義されている再帰関数を呼び出し、この親コメントに紐づくすべての返信（子・孫コメント）をツリー状に出力
                    display_comment_replies_tree(
                        $comments_by_parent,
                        $comment_id,
                        $comment_post_id,
                        $reaction_counts,
                        1 // 階層の深さ（depth）の初期値として 1 を渡す
                    );

                    echo '</article>';
                    echo '</li>';

                    /* コメント番号を増やす */
                    $comment_number++;
                }

                echo '</ol>';
            }
            ?>

        </div><!-- /#right-box -->
    </div><!-- /#main-box -->
</div><!-- /#blog-box -->

<script>
    // HTMLの読み込みと解析が完了し、DOMツリーが完成した時点で実行
    document.addEventListener('DOMContentLoaded', function() {

        document.addEventListener('click', function(e) {

            /* ==========================================
               1. 返信の返信（子スレッド）を開閉する処理
               ========================================== */
            // クリックされた要素、またはその親要素から「.reply-toggle-button」を探す
            const toggleButton = e.target.closest('.reply-toggle-button');

            if (toggleButton) {
                // ボタンクリックによるデフォルトの挙動（リンク遷移やフォーム送信など）を防止する
                e.preventDefault();

                // クリックされたボタンのすぐ後ろにある兄弟要素（＝子コメントを包むdiv）を取得
                const children = toggleButton.nextElementSibling;

                // 後続の要素がなければ何もしない
                if (!children) {
                    return;
                }

                // 子コメントの要素に対して 'is-hidden-children' クラスを切り替える（あれば削除、なければ追加）
                // toggle() は切り替えた結果（非表示になったら true、表示されたら false）を返す
                const isHidden = children.classList.toggle('is-hidden-children');

                // 現在の状態に合わせてボタン内のテキストを書き換える（安全な textContent を使用）
                toggleButton.textContent = isHidden ?
                    toggleButton.dataset.closeText : // 非表示になったら「○件の返信」にする
                    '返信を非表示'; // 表示されたら「返信を非表示」にする

                return; // 処理が一致したためここで終了
            }

            /* ==========================================
               2. 10件目以降の大量の返信を開閉する処理
               ========================================== */
            // クリックされた要素、またはその親要素から「.reply-more-button」を探す
            const moreButton = e.target.closest('.reply-more-button');

            if (moreButton) {
                e.preventDefault();

                // ボタンが所属している大元の返信リスト「.reply-list」を取得
                const replyList = moreButton.closest('.reply-list');

                if (!replyList) {
                    return;
                }

                // リストの直下にある、非表示対象のコメント（.is-hidden-reply）をすべて取得
                const hiddenReplies = replyList.querySelectorAll(':scope > .reply-item.is-hidden-reply');
                // ボタンに 'is-open' クラスを切り替え、現在の開閉状態（true / false）を取得
                const isOpen = moreButton.classList.toggle('is-open');

                // 非表示にされていたコメントたちをループ処理し、状態に合わせて表示・非表示を切り替える
                hiddenReplies.forEach(function(reply) {
                    // 開いた状態なら 'block' で表示、閉じた状態ならスタイルを消してCSSの初期状態（非表示）に戻す
                    reply.style.display = isOpen ? 'block' : '';
                });

                // ボタン内のテキストを開閉状態に合わせて書き換える
                moreButton.textContent = isOpen ?
                    moreButton.dataset.openText :
                    moreButton.dataset.closeText;

                return; // 処理が一致したためここで終了
            }

            /* ==========================================
               3. コメント返信フォームの移動処理
               ========================================== */
            // クリックされた要素が「返信」リンク（.custom-flat-reply-link）かどうかを判定
            const link = e.target.closest('.custom-flat-reply-link');

            if (!link) {
                return; // 返信リンク以外のクリックならここで処理を抜ける
            }

            e.preventDefault(); // リンクのデフォルト挙動（#respondへのページ内ジャンプ）をキャンセル
            e.stopImmediatePropagation(); // 同じ要素に設定された他のクリックイベントの発生を止める

            // 必要なDOM要素（フォーム本体、元の位置の親、テキストエリア、親ID保存用の隠しインプット）をまとめて取得
            const respond = document.getElementById('respond');
            const home = document.getElementById('comment-form-home');
            const textarea = document.getElementById('comment');
            const parentInput = document.getElementById('comment_parent');

            // リンクの data-rootid から返信先のコメントIDを10進数の整数として取得
            const rootId = parseInt(link.dataset.rootid, 10);

            // 取得したIDが正しい整数で、かつ0より大きいかチェック（セキュリティ・バグ対策）
            if (!Number.isInteger(rootId) || rootId <= 0) {
                return;
            }
            // const mention = link.dataset.mention;
            // 返信先となるコメント要素（article）を取得
            const rootArticle = document.getElementById('div-comment-' + rootId);

            // 画面上に必要な要素が1つでも欠けていれば処理を中断
            if (!respond || !home || !textarea || !parentInput || !rootArticle) {
                return;
            }

            /* 【同じ「返信」ボタンをもう一度押した場合（キャンセル・元に戻す処理）】 */
            if (parentInput.value === String(rootId)) {
                parentInput.value = '0'; // 親コメントIDを0（なし）にリセット
                textarea.value = ''; // 入力中の文字をクリア

                // フォームやテキストエリアに直接付与したスタイル（幅や余白など）を消去
                respond.removeAttribute('style');
                textarea.removeAttribute('style');

                // 返信時用のツリー線消去クラスを削除
                respond.classList.remove('reply-form-no-tree-line');

                // フォームを元の位置（ページ下部などの comment-form-home 内）に戻す
                home.appendChild(respond);
                return;
            }

            /* 【新しく「返信」ボタンを押した場合（フォーム移動処理）】 */
            parentInput.value = rootId; // 隠しインプットに返信先コメントのIDをセット
            textarea.value = ''; // テキストエリアを空にする
            // rootArticle.appendChild(respond);
            // 返信フォームが縮まないようにフォームは返信先コメントの 直後 に移動
            /* いったん前回の位置調整をリセット */
            respond.removeAttribute('style');
            textarea.removeAttribute('style');

            /* ★最重要：返信先コメント（article）のすぐ後ろ（直後）へフォームをごっそり移動 */
            rootArticle.after(respond);

            // フォーム内の実際の form タグを取得し、上下に余白用のスペーサー要素（div）を生成・配置する
            const commentForm = respond.querySelector('#commentform');

            if (commentForm) {
                let formSpacer = document.getElementById('commentform-bottom-spacer');

                if (!formSpacer) {
                    formSpacer = document.createElement('div');
                    formSpacer.id = 'commentform-bottom-spacer';
                }

                commentForm.after(formSpacer); // フォームの下に配置

                let formTopSpacer = document.getElementById('commentform-top-spacer');

                if (!formTopSpacer) {
                    formTopSpacer = document.createElement('div');
                    formTopSpacer.id = 'commentform-top-spacer';
                }

                // フォームの上に配置
                commentForm.before(formTopSpacer);
            }

            // ツリー線を非表示にするスタイルクラスをフォームに付与
            respond.classList.add('reply-form-no-tree-line');

            /* DOM移動後、ブラウザに位置計算させてから幅を固定 */
            /* 移動後にブラウザが位置の再計算を終えたタイミング（次の描画フレーム）で、ダイナミックに幅や位置を固定する */
            requestAnimationFrame(function() {
                const rightBox = document.getElementById('right-box');
                const rightStyle = window.getComputedStyle(rightBox);

                // メインコンテンツエリア（right-box）の現在の位置とサイズを取得
                const rightBoxRect = rightBox.getBoundingClientRect();
                const respondRect = respond.getBoundingClientRect();

                // 余白や枠線の幅を数値として安全に取得（取得できなければ0）
                const paddingLeft = parseFloat(rightStyle.paddingLeft) || 0;
                const paddingRight = parseFloat(rightStyle.paddingRight) || 0;
                const borderLeft = parseFloat(rightStyle.borderLeftWidth) || 0;
                const borderRight = parseFloat(rightStyle.borderRightWidth) || 0;

                // フォームが本来合わせるべき左端の位置と横幅を計算
                const targetLeft = rightBoxRect.left + borderLeft + paddingLeft;
                const targetWidth = rightBoxRect.width - borderLeft - borderRight - paddingLeft - paddingRight;
                // コメントの階層が深くなっても、フォームが右に縮んでしまわないようにマイナスマージンで左に押し戻す量を計算
                const moveLeft = respondRect.left - targetLeft;

                // --- フォームが画面幅いっぱいに綺麗に収まるようスタイルを強制上書き ---
                respond.style.marginLeft = '-' + moveLeft + 'px';
                respond.style.width = targetWidth + 'px';
                respond.style.maxWidth = 'none';
                respond.style.boxSizing = 'border-box';
                respond.style.background = '#fff';
                respond.style.position = 'relative';
                respond.style.zIndex = '10';

                // テキストエリアも横幅いっぱいに広げる
                textarea.style.width = '100%';
                textarea.style.maxWidth = 'none';
                textarea.style.boxSizing = 'border-box';

                // 移動が完了したら、即座にテキストエリアにカーソルを合わせる（フォーカス）
                textarea.focus();
            });

        }, true); // キャプチャリングフェーズでのイベント監視

    });
</script>

<?php get_footer(); ?>