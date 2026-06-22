<?php
/*
Template Name: 免責事項
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
            /* ここに免責事項を入れる */
            ?>
            <section class="qa-section privacy-policy-section disclaimer-section">
                <h2 class="comment-section-title privacy-policy-title disclaimer-title">免責事項</h2>

                <p class="privacy-policy-meta">制定日：YYYY年MM月DD日</p>
                <p class="privacy-policy-meta">最終更新日：YYYY年MM月DD日</p>
                <p class="privacy-policy-meta">運営者：お絵描き民運営</p>

                <p class="privacy-policy-text">本免責事項は、当サイト「お絵描き民」（以下「当サイト」といいます）に掲載する情報、外部リンク、広告、コメント、掲示板投稿、画像・動画等の取扱いおよび責任範囲について定めるものです。</p>
                <p class="privacy-policy-text">当サイトをご利用いただいた場合、本免責事項の内容をご確認いただいたものとみなします。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第1条　掲載情報について</h3>
                <p class="privacy-policy-text">当サイトでは、掲載内容について可能な限り正確な情報を掲載するよう努めますが、その正確性、完全性、最新性、有用性、安全性等を保証するものではありません。</p>
                <p class="privacy-policy-text">掲載内容には、誤情報、古い情報、利用者の解釈により評価が分かれる内容が含まれる場合があります。</p>
                <p class="privacy-policy-text">当サイトに掲載された情報の利用は、利用者ご自身の判断と責任において行ってください。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第2条　損害等の責任について</h3>
                <p class="privacy-policy-text">当サイトの掲載内容、コメント、掲示板投稿、外部リンク、広告リンクその他当サイトの利用により生じた損害、トラブル、不利益等について、当サイトの故意または重過失による場合を除き、当サイトは責任を負いかねます。</p>
                <p class="privacy-policy-text">利用者間または利用者と第三者との間で生じたトラブルについては、当事者間で解決していただくものとします。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第3条　外部リンクについて</h3>
                <p class="privacy-policy-text">当サイトには、外部サイトへのリンクが含まれる場合があります。</p>
                <p class="privacy-policy-text">外部リンク先の内容、正確性、安全性、合法性、サービス内容、個人情報の取扱い等については、各外部サイトの運営者が管理するものであり、当サイトは責任を負いません。</p>
                <p class="privacy-policy-text">外部サイトを利用する場合は、各サイトの利用規約、プライバシーポリシー、免責事項等をご確認ください。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第4条　広告・アフィリエイトについて</h3>
                <p class="privacy-policy-text">当サイトでは、第三者配信の広告サービス、アフィリエイトプログラム、成果報酬型広告等を利用する場合があります。</p>
                <p class="privacy-policy-text">広告またはアフィリエイトリンクから移動した先の商品、サービス、契約、決済、配送、トラブル等については、広告主またはリンク先事業者の責任において管理されるものであり、当サイトは責任を負いません。</p>
                <p class="privacy-policy-text">商品・サービスの購入、登録、利用等を行う場合は、利用者ご自身の判断と責任において行ってください。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第5条　コメント・掲示板投稿について</h3>
                <p class="privacy-policy-text">当サイトのコメント欄、掲示板その他利用者が投稿できる機能に投稿された内容は、各投稿者本人の責任によるものです。</p>
                <p class="privacy-policy-text">当サイトは、投稿内容を常時監視する義務を負うものではありません。</p>
                <p class="privacy-policy-text">投稿内容が法令違反、権利侵害、誹謗中傷、個人情報の公開、荒らし行為、その他当サイトが不適切と判断する内容に該当する場合、事前通知なく削除、非表示、利用制限等の対応を行う場合があります。</p>
                <p class="privacy-policy-text">掲示板への投稿ルール、禁止事項、削除基準、利用停止等については、別途定める「掲示板ガイドライン」または「掲示板利用規約」に従うものとします。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第6条　著作権・肖像権等について</h3>
                <p class="privacy-policy-text">当サイトに掲載している文章、画像、動画、イラスト、ロゴ、その他著作物の著作権・肖像権・商標権その他の権利は、各権利者に帰属します。</p>
                <p class="privacy-policy-text">当サイトは、著作権、肖像権、商標権その他第三者の権利を侵害する目的で運営するものではありません。</p>
                <p class="privacy-policy-text">掲載内容に問題がある場合、権利者ご本人または正当な代理人より、お問い合わせフォームからご連絡ください。内容を確認のうえ、必要に応じて削除、修正、掲載停止等の対応を行います。</p>
                <p class="privacy-policy-text">権利確認や事実確認のため、該当箇所、権利を有することが分かる情報、連絡先等の提示をお願いする場合があります。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第7条　削除依頼・権利侵害の申立てについて</h3>
                <p class="privacy-policy-text">削除依頼、権利侵害の申立て、掲載内容に関するお問い合わせは、当サイトのお問い合わせフォームよりお願いいたします。</p>
                <p class="privacy-policy-text">申立て内容によっては、確認および対応までに時間を要する場合があります。</p>
                <p class="privacy-policy-text">当サイトが必要と判断した場合、掲載内容の削除、修正、非表示、リンク削除等の対応を行いますが、すべての申立てに対して削除等を保証するものではありません。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第8条　サービスの変更・停止について</h3>
                <p class="privacy-policy-text">当サイトは、サーバー障害、システム障害、保守作業、仕様変更、外部サービスの停止、災害、その他やむを得ない事情により、事前の告知なくサイトの全部または一部を変更、停止、中断、終了する場合があります。</p>
                <p class="privacy-policy-text">これにより利用者に生じた損害について、当サイトの故意または重過失による場合を除き、当サイトは責任を負いかねます。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第9条　本免責事項の変更について</h3>
                <p class="privacy-policy-text">当サイトは、法令の変更、運営方針の見直し、サービス内容の変更等により、本免責事項を変更することがあります。</p>
                <p class="privacy-policy-text">変更後の免責事項は、本ページに掲載した時点から効力を生じるものとします。</p>

                <h3 class="qa-category-title privacy-policy-heading disclaimer-heading">第10条　お問い合わせ</h3>
                <p class="privacy-policy-text">本免責事項に関するお問い合わせ、削除依頼、権利侵害に関するご連絡は、当サイトのお問い合わせフォームよりお願いいたします。</p>
                <p class="privacy-policy-text">お問い合わせURL：<a href="<?php echo esc_url(home_url('/contact/')); ?>">お問い合わせ</a></p>
            </section>
            <?php

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