<?php
/*
Template Name: プライバシーポリシー
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
            /* ここにプライバシーポリシーを入れる */
            ?>
            <section class="qa-section privacy-policy-section">
                <h2 class="comment-section-title privacy-policy-title">プライバシーポリシー</h2>
                <p class="privacy-policy-meta">制定日：YYYY年MM月DD日</p>
                <p class="privacy-policy-meta">最終更新日：YYYY年MM月DD日</p>
                <p class="privacy-policy-meta">運営者：お絵描き民運営</p>
                <p class="privacy-policy-text">本プライバシーポリシーは、当サイト「お絵描き民」（以下「当サイト」といいます）における、個人情報、Cookie、アクセスログその他利用者に関する情報の取扱いについて定めるものです。</p>
                <p class="privacy-policy-text">本ポリシーは、まとめ記事ページ、お絵描き掲示板ページ、お問い合わせフォーム、コメント欄、その他当サイト内の各機能に適用されます。</p>
                <p class="privacy-policy-text">なお、掲示板への投稿ルール、禁止事項、削除基準、利用停止等については、本ポリシーではなく、別途定める「掲示板ガイドライン」または「掲示板利用規約」に従うものとします。</p>
                <h3 class="qa-category-title privacy-policy-heading">第1条　取得する情報</h3>
                <p class="privacy-policy-text">当サイトでは、以下の情報を取得する場合があります。</p>
                <ol class="privacy-policy-list">
                    <li>お問い合わせ時に入力された名前、メールアドレス、本文その他送信内容</li>
                    <li>コメント投稿時に入力された名前（未入力の場合は「名無し」等の表示名）、コメント内容、IPアドレス、投稿日時等</li>
                    <li>お絵描き掲示板の会員登録およびパスワード再設定に必要なメールアドレス</li>
                    <li>お絵描き掲示板の利用に伴い入力または送信された名前、投稿本文、画像、その他投稿内容</li>
                    <li>IPアドレス、Cookie、アクセス日時、閲覧URLその他、サーバー管理・不正利用防止・セキュリティ対策のために必要なアクセスログ</li>
                    <li>広告配信、アクセス解析、不正利用防止、セキュリティ対策のために必要な情報</li>
                    <li>その他、当サイトの運営上必要な範囲で利用者から提供された情報</li>
                </ol>
                <h3 class="qa-category-title privacy-policy-heading">第2条　利用目的</h3>
                <p class="privacy-policy-text">当サイトは、取得した情報を以下の目的で利用します。</p>
                <ol class="privacy-policy-list">
                    <li>お問い合わせフォームで取得した名前、メールアドレス、本文その他送信内容を、お問い合わせへの回答および回答に必要な確認連絡のために利用するため</li>
                    <li>お絵描き掲示板の会員登録機能で取得したメールアドレスを、パスワードを忘れた場合の再設定手続き、および登録者本人による手続きであることを確認するために利用するため</li>
                    <li>コメント、掲示板投稿、問い合わせ内容の確認、管理、返信、対応を行うため</li>
                    <li>スパム、荒らし、不正アクセス、なりすまし、権利侵害、規約違反その他不正行為を防止・調査・対応するため</li>
                    <li>サイトの表示、保守、障害対応、セキュリティ対策、バックアップ等の運営管理を行うため</li>
                    <li>アクセス解析により、当サイトの利用状況を把握し、利便性やコンテンツの改善に役立てるため</li>
                    <li>広告配信、アフィリエイト、成果測定、広告の最適化を行うため</li>
                    <li>著作権侵害、肖像権侵害、プライバシー侵害等に関する削除依頼、通報、権利者対応を行うため</li>
                    <li>法令、裁判所、警察、行政機関その他公的機関からの要請に対応するため</li>
                    <li>その他、上記各号に付随する範囲で当サイトの運営上必要な目的のため</li>
                </ol>
                <h3 class="qa-category-title privacy-policy-heading">第3条　Cookieの使用について</h3>
                <p class="privacy-policy-text">当サイトでは、広告配信、アクセス解析、ログイン状態の保持、不正利用防止、利便性向上等のためにCookieを使用する場合があります。</p>
                <p class="privacy-policy-text">Cookieにより取得される情報には、氏名、住所、メールアドレスなど、利用者個人を直接特定する情報は通常含まれません。</p>
                <p class="privacy-policy-text">Cookieの使用を希望しない場合、利用者はブラウザの設定によりCookieを無効化することができます。ただし、Cookieを無効化した場合、当サイトの一部機能が正常に利用できない場合があります。</p>
                <h3 class="qa-category-title privacy-policy-heading">第4条　アクセス解析ツールについて</h3>
                <p class="privacy-policy-text">当サイトでは、Google Analytics等のアクセス解析ツールを利用する場合があります。</p>
                <p class="privacy-policy-text">これらのアクセス解析ツールは、トラフィックデータの収集のためにCookieを使用する場合があります。収集されるデータは、サイト利用状況の分析、改善、保守のために利用されます。</p>
                <p class="privacy-policy-text">アクセス解析ツールによる情報収集を希望しない場合、利用者はブラウザ設定または各サービスが提供するオプトアウト機能により、収集を拒否できる場合があります。</p>
                <h3 class="qa-category-title privacy-policy-heading">第5条　広告配信・アフィリエイトについて</h3>
                <p class="privacy-policy-text">当サイトでは、第三者配信の広告サービス、アフィリエイトプログラム、成果報酬型広告を利用する場合があります。</p>
                <p class="privacy-policy-text">広告配信事業者は、利用者の興味に応じた広告を表示するため、Cookie等を使用して情報を取得する場合があります。</p>
                <p class="privacy-policy-text">当サイトに掲載される広告、外部リンク、広告主サイト、リンク先サービスにおける個人情報の取扱いについては、各事業者のプライバシーポリシーをご確認ください。</p>
                <h3 class="qa-category-title privacy-policy-heading">第6条　コメント・掲示板投稿に関する情報</h3>
                <p class="privacy-policy-text">当サイトでは、コメントや掲示板投稿の際、スパム、荒らし、不正行為、権利侵害、規約違反への対応のため、IPアドレス、投稿日時、投稿内容等を記録する場合があります。</p>
                <p class="privacy-policy-text">これらの情報は、通常、スパム対策、荒らし対策、不正利用防止、削除依頼対応、法令上必要な対応以外の目的では利用しません。</p>
                <p class="privacy-policy-text">なお、利用者がコメント欄や掲示板に投稿した名前、本文、画像、その他公開を前提として入力した情報は、当サイト上で公開される場合があります。個人情報や第三者の権利を侵害する情報を投稿しないようご注意ください。</p>
                <h3 class="qa-category-title privacy-policy-heading">第7条　第三者提供について</h3>
                <p class="privacy-policy-text">当サイトは、取得した個人情報を、以下の場合を除き、本人の同意なく第三者に提供しません。</p>
                <ol class="privacy-policy-list">
                    <li>本人の同意がある場合</li>
                    <li>法令に基づく場合</li>
                    <li>裁判所、警察、行政機関その他公的機関から正式な照会・要請を受けた場合</li>
                    <li>人の生命、身体、財産の保護のために必要がある場合</li>
                    <li>不正行為、権利侵害、規約違反、セキュリティ上の問題への対応に必要な場合</li>
                    <li>サイト運営、サーバー管理、メール送信、アクセス解析、広告配信等に必要な範囲で外部サービスまたは委託先を利用する場合</li>
                </ol>
                <h3 class="qa-category-title privacy-policy-heading">第8条　外部委託・外部サービスの利用</h3>
                <p class="privacy-policy-text">当サイトでは、レンタルサーバー、メール送信サービス、アクセス解析サービス、広告配信サービス、セキュリティ対策サービス等の外部サービスを利用する場合があります。</p>
                <p class="privacy-policy-text">これらの外部サービスを利用する場合、当サイトの運営に必要な範囲で、アクセスログ、Cookie情報、メールアドレスその他必要な情報が各サービス提供事業者に送信または保存される場合があります。</p>
                <h3 class="qa-category-title privacy-policy-heading">第9条　安全管理措置</h3>
                <p class="privacy-policy-text">当サイトは、取得した情報について、不正アクセス、漏えい、改ざん、紛失、破壊等を防止するため、合理的な範囲で安全管理措置を講じます。</p>
                <p class="privacy-policy-text">ただし、インターネット通信、サーバー障害、サイバー攻撃、不正アクセス、システム不具合、その他不可抗力により情報の漏えい、改ざん、消失等が発生する可能性を完全に排除することはできません。</p>
                <h3 class="qa-category-title privacy-policy-heading">第10条　保存期間</h3>
                <p class="privacy-policy-text">当サイトは、取得した情報を、利用目的の達成に必要な範囲で保存します。</p>
                <p class="privacy-policy-text">お絵描き掲示板に作成された掲示板またはスレッドは、原則として作成から1か月経過後に新規書き込みの受付を停止します。</p>
                <p class="privacy-policy-text">お絵描き掲示板に投稿された投稿本文、画像その他投稿内容は、原則として投稿から半年経過後に順次削除します。</p>
                <p class="privacy-policy-text">マイページ機能に保存された投稿、画像その他投稿内容は、原則として投稿または保存から1年経過後に順次削除します。</p>
                <p class="privacy-policy-text">お問い合わせ内容、コメント情報、掲示板投稿に関する記録、アクセスログ、Cookie情報等は、スパム対策、不正利用防止、削除依頼対応、権利侵害対応、トラブル防止、法令対応のため、必要な期間保存する場合があります。</p>
                <p class="privacy-policy-text">削除対象となった投稿内容であっても、バックアップ、キャッシュ、サーバーログ、外部サービス上の記録等に、一時的または必要な範囲で残存する場合があります。</p>
                <p class="privacy-policy-text">保存の必要がなくなった情報については、合理的な範囲で削除または匿名化します。</p>
                <h3 class="qa-category-title privacy-policy-heading">第11条　開示・訂正・削除等の請求</h3>
                <p class="privacy-policy-text">利用者本人から、当サイトが保有する本人の個人情報について、開示、訂正、利用停止、削除等の請求があった場合、本人確認を行ったうえで、法令に従い合理的な範囲で対応します。</p>
                <p class="privacy-policy-text">ただし、法令上保存が必要な情報、不正利用防止や権利侵害対応のため保存が必要な情報、他者の権利利益を害するおそれがある情報については、請求に応じられない場合があります。</p>
                <h3 class="qa-category-title privacy-policy-heading">第12条　未成年者の利用について</h3>
                <p class="privacy-policy-text">未成年者が当サイトの機能を利用し、個人情報を送信する場合は、保護者の同意を得たうえで行うものとします。</p>
                <h3 class="qa-category-title privacy-policy-heading">第13条　外部リンクについて</h3>
                <p class="privacy-policy-text">当サイトには、外部サイトへのリンクが含まれる場合があります。外部サイトにおける個人情報の取扱い、Cookieの使用、広告配信等については、当該外部サイトのプライバシーポリシーをご確認ください。</p>
                <p class="privacy-policy-text">当サイトは、外部サイトにおける個人情報の取扱いについて責任を負いません。</p>
                <h3 class="qa-category-title privacy-policy-heading">第14条　本ポリシーの変更</h3>
                <p class="privacy-policy-text">当サイトは、法令の変更、利用サービスの変更、運営方針の見直し等により、本ポリシーを変更することがあります。</p>
                <p class="privacy-policy-text">変更後のプライバシーポリシーは、本ページに掲載した時点から効力を生じるものとします。</p>
                <h3 class="qa-category-title privacy-policy-heading">第15条　お問い合わせ</h3>
                <p class="privacy-policy-text">本ポリシーに関するお問い合わせ、個人情報の開示・訂正・削除等の請求、削除依頼、権利侵害に関するご連絡は、当サイトのお問い合わせフォームよりお願いいたします。</p>
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