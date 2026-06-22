<?php
/*
Template Name: お絵描き民について
*/
require_once get_template_directory() . '/display.php';
set_template_info();
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

            /* ここにQ&Aを入れる */
            echo '<section class="qa-section">';

            /* お絵描き民について */

            echo '<h3 class="qa-category-title"><i class="fas fa-info-circle fa-fw"></i> お絵描き民について</h3>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> お絵描き民はどんなサイトですか？</summary>';
            echo '<div class="qa-answer">';
            echo '<span class="a-mark">A</span> 5ちゃんねる（5ch）などのお絵描きに関するスレッドや投稿をまとめた「まとめサイト」と、実際にイラストを描いて投稿できる「お絵描き掲示板」を併設したサイトです。<br>';
            echo '当サイトでは、「まとめ記事の閲覧ページ」と「お絵描き掲示板ページ」をそれぞれ独立したページとして分けて配信しています。用途に合わせて各ページをお楽しみください。';
            echo '</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 会員登録は必要ですか？</summary>';
            echo '<div class="qa-answer">';
            echo '<span class="a-mark">A</span> まとめ記事の閲覧やお絵描き掲示板の閲覧には登録は不要です。ただし、お絵描き掲示板でイラストを投稿・利用する際には会員登録（無料）が必要となります。';
            echo '</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 18歳未満でも利用できますか？</summary>';
            echo '<div class="qa-answer">';
            echo '<span class="a-mark">A</span> 当サイトは原則として全年齢向けに運営していますが、一部記事には刺激の強い表現が含まれる場合があります。';
            echo '</div>';
            echo '</details>';

            /* お絵描き掲示板について */

            echo '<h3 class="qa-category-title"><i class="fas fa-palette fa-fw"></i> お絵描き掲示板について</h3>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 投稿できる画像形式を教えてください</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> JPG と PNG に対応しています。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 1回に何枚まで投稿できますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 最大3枚まで同時に投稿可能です。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 投稿した画像はいつまで保存されますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 投稿されてから半年間保存されます。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> AI画像（AI生成イラスト）は投稿できますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 申し訳ありませんが、利用規約によりAI生成されたイラストの投稿は禁止しております。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 二次創作イラストは投稿できますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 各作品の権利元が掲げるガイドラインに従ってご投稿ください。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> R18（成人向け）イラストは投稿できますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 性的興奮を目的とした表現や成人向けコンテンツの投稿はご遠慮ください。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 動画は投稿できますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> こちらは今後追加予定の機能となります。</div>';
            echo '</details>';

            /* コメント・掲示板機能 */

            echo '<h3 class="qa-category-title"><i class="fas fa-comments fa-fw"></i> コメント・掲示板機能</h3>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> コメントは匿名で投稿できますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> はい、匿名での投稿が可能です。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> コメントに対する返信機能はありますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> はい、実装しております。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 自分が書いたコメントを削除したいです</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> お手数ですが、掲示板のマイページ機能から削除手続きをお願いいたします。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 荒らしや誹謗中傷を見つけました</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> お問い合わせフォームまたは公式Xまでご連絡ください。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> ユーザーブロック機能はありますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> はい。ブロックしたユーザーはあなたのイラストを見ることができなくなります。</div>';
            echo '</details>';

            /* アカウント */

            echo '<h3 class="qa-category-title"><i class="fas fa-user-circle fa-fw"></i> アカウント・データ</h3>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> パスワードを忘れてしまいました</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> パスワード再発行ページよりお手続きください。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 再発行用のメールが届きません</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 迷惑メールフォルダをご確認ください。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 退会したいです</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 退会専用ページよりお手続きください。</div>';
            echo '</details>';

            /* 相互RSS */

            echo '<h3 class="qa-category-title"><i class="fas fa-rss fa-fw"></i> 相互リンク・相互RSSについて</h3>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 相互リンクや相互RSSは募集していますか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> はい、随時募集しております。お問い合わせフォームよりご連絡ください。</div>';
            echo '</details>';

            /* お問い合わせ */

            echo '<h3 class="qa-category-title"><i class="fas fa-envelope fa-fw"></i> お問い合わせ・その他</h3>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 著作権について教えてください</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> 著作権は各製作者様および権利者様に帰属します。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> サイトの不具合・バグを見つけました</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> お問い合わせフォームよりご連絡ください。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> サイトへの要望はどこから送ればいいですか？</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> お問い合わせフォームから受け付けております。</div>';
            echo '</details>';

            echo '<details class="qa-item">';
            echo '<summary><span class="q-mark">Q</span> 広告掲載について相談したいです</summary>';
            echo '<div class="qa-answer"><span class="a-mark">A</span> お問い合わせフォームよりご連絡ください。</div>';
            echo '</details>';

            echo '</section>';

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

            /* RSS3 */
            echo $trisect_rss_feed[2];
            ?>

        </div><!-- /#right-box -->
    </div><!-- /#main-box -->
</div><!-- /#blog-box -->

<?php get_footer(); ?>