<?php
function set_template_info()
{
    global $tn;
    global $tk;
    global $rss_table_name;
    global $current_page;
    $tn = get_template_number();
    $tk = get_template_key($tn);
    $rss_table_name = get_rss_table_name($tn);
    $current_page = get_current_page();
}

//固定ページＵＲＬの生成関数
function get_template_url($template_number, $check_search)
{
    // 【改善1】数字を想定している引数を安全のために強制的に整数化
    $template_number = (int) $template_number;

    // 現在表示しているページがカテゴリーアーカイブページか判断する
    if ($check_search && is_category()) {
        // 現在表示されているページのクエリ情報を取得
        $term = get_queried_object();

        if ($term instanceof WP_Term) {
            // 特定のタクソノミーのタームのURLを取得
            return get_term_link($term);
        }

        return home_url('/');
    } elseif ($check_search && is_archive()) {
        $y = (int) get_query_var('year');
        $m = (int) get_query_var('monthnum');

        // URL崩れ防止、変な文字混入防止、想定外URL防止
        if ($y <= 0 || $m <= 0 || $m > 12) {
            return home_url('/');
        }

        $sub = "{$y}/{$m}";
        // 【改善2】意図を明確にするためにカッコ () を追加
    } elseif ((1 === $template_number) || ($check_search && is_search())) {
        $sub = '';
    } else {
        $map = [
            2 => 'image-2',
            3 => 'image-3',
            4 => 'image-4',
        ];

        $sub = $map[$template_number] ?? '';
    }
    // 出力時に esc_url()で統一する方が保守しやすい
    return home_url($sub);
}

function get_template_number()
{
    // WordPressが管理している、現在表示中のテンプレートファイルの絶対パス（グローバル変数）を読み込む
    global $template;
    // 修正前: $template_number = $_GET['tn'];
    // tn は数値だけという前提をコード側で強制
    $template_number = isset($_GET['tn'])
        ? (int)$_GET['tn']
        : 1; // 35行目
    // 取得したテンプレート番号に応じて、処理を分岐させる
    switch ($template_number) {
        case 2:
            break;
        case 3:
            break;
        default:
            // URLでの指定が「2や3以外」（未指定や、想定外の数値・文字列だった）場合の処理
            // pathinfo() を使い、現在読み込まれているテンプレートの「ファイル名（拡張子なし）」を取り出して判定する
            switch (pathinfo($template, PATHINFO_FILENAME)) {
                case 'page-secound':
                    $template_number = 2;
                    break;
                case 'page-third':
                    $template_number = 3;
                    break;
                default:
                    $template_number = 1;
            }
    }

    // 最終的に確定したテンプレート番号（1, 2, 3 のいずれか）を呼び出し元に返す
    return $template_number;
}

function get_template_key($template_number)
{
    if (1 == $template_number) {
        return 'single_rss_feed1';
    } elseif (2 == $template_number) {
        return 'double_rss_feed2';
    } elseif (3 == $template_number) {
        return 'triple_rss_feed3';
    }

    return 'single_rss_feed1';
}

function get_rss_table_name($template_number)
{
    if (1 == $template_number) {
        return 'single_rss_feed';
    } elseif (2 == $template_number) {
        return 'double_rss_feed';
    } elseif (3 == $template_number) {
        return 'triple_rss_feed';
    } elseif (4 == $template_number) {
        return 'trisect_rss_feed';
    }

    return 'single_rss_feed';
}

function get_current_page()
{
    // 修正前: $cp = $_GET['cp'];
    $cp = isset($_GET['cp']) ? $_GET['cp'] : ''; // 84行目
    // 変数 $cp の中身が「すべて純粋な数字（0〜9）」だけで構成されているかチェック
    if (ctype_digit($cp)) {
        // 【型安全】文字列の数字（例: "3"）を、プログラムで計算可能な「整数（3）」に変換する
        // 修正前のように $_GET を直接使うのではなく、安全な $cp を指定しているのが素晴らしいポイントです
        $cp = (int) $cp; // ここを $_GET['cp'] ではなく $cp に変更

        // 1未満防止
        if ($cp < 1) {
            // 1ページ目を表す「1」に強制的に書き換える
            $cp = 1;
        }
        // URLパラメータに変な文字列（文字、記号、マイナス、空文字など）が入っていた場合の処理
    } else {
        // 安全のため、標準の「1ページ目」として「1」を代入する
        $cp = 1;
    }

    return $cp;
}

function display_other_template()
{
    global $tn;
    for ($i = 1; $i <= 3; ++$i) {
        if ($i != $tn) {
            $url = get_template_url($i, false);
            echo '<div><a href="' . esc_url($url) . '">画像' . (int)$i . 'の一覧へ</a></div>';
        }
    }
}

function display_category_ranking()
{
    // WordPressのデータベース操作用グローバル変数を読み込む
    global $wpdb;
    // 現在のテンプレート番号などを保持するグローバル変数を読み込む（URLパラメータ用）
    global $tn;
    // 現在のカテゴリ情報を保持するグローバル変数を読み込む
    global $cat;
    // データベースから「週間アクセス数が多い上位20件のカテゴリ」を取得するためのSQL文
    $sql = "
SELECT
t.*,
tt.*,
cc.meta_value AS access_count
FROM
wp_terms AS t
INNER JOIN wp_term_taxonomy AS tt
ON t.term_id = tt.term_id
INNER JOIN (
SELECT
*
FROM
wp_termmeta
WHERE
meta_key = %s
AND meta_value != 0
) AS cc
ON t.term_id = cc.term_id
ORDER BY
cc.meta_value DESC
LIMIT
%d
";
    // 【安全対策】SQL文の「%s」に文字列、「%d」に数値を安全に組み込む（SQLインジェクション防止）
    $query = $wpdb->prepare($sql, 'category_count_week', 20);

    // 安全に作成されたクエリを実行し、結果（カテゴリの配列）を取得
    $terms = $wpdb->get_results($query);
    // 該当するカテゴリデータが1件以上取得できた場合の処理
    if ($terms) {
        $out = '<ul class="category-ranking clearfix">';
        // ループの回数（順位やクラス名用）をカウントする変数を初期化
        $tag_link_count = 0;

        // 取得したカテゴリデータを1つずつ処理するループ
        foreach ($terms as $term) {
            $term = get_term((int) $term->term_id, $term->taxonomy);

            if (!$term || is_wp_error($term)) {
                continue;
            }

            // カテゴリのオブジェクトから、そのカテゴリページのURLを取得
            $term_link = get_term_link($term);

            // 【エラー防止】もしURLの取得に失敗（WP_Error）した場合は、そのカテゴリをスキップして次に進む
            // これにより、1つのカテゴリに不具合があってもランキング全体が真っ白になるのを防ぐ
            if (is_wp_error($term_link)) {
                continue;
            }

            $url = esc_url(add_query_arg('tn', (int) $tn, $term_link));
            $name = esc_html($term->name);
            $count = (int)$term->count;

            $tag_link_count++;

            $out .= '
<li>
<a href="' . $url . '" 
   class="tag-link-' . (int)$tag_link_count . '" 
   style="font-size:9pt;" 
   aria-label="' . $name . '（' . $count . '項目）">
   ' . $name . '
</a>
<div class="Information"></div>
</li>';
        }

        $out .= '</ul>';
    } else {
        $out = '<p>アクセスランキングはまだ集計されていません。</p>';
    }

    echo '<section class="category-box">';
    // 【安全対策】作成したHTML文字列（$out）を、WordPressの安全なタグだけ許可して画面に出力
    echo wp_kses_post($out);
    echo '</section>';
}

function display_archive()
{
    /*echo '<div>アーカイブ</div>';*/
    global $wpdb;
    global $tn;
    global $tk;
    $sql = "
SELECT
YEAR (post.post_date) AS y,
MONTH (post.post_date) AS m,
count(*) AS c
FROM
wp_posts AS post
INNER JOIN wp_postmeta AS meta
ON post.id = meta.post_id
WHERE

meta.meta_key = %s
AND post.post_type = 'post'
AND post.post_status = 'publish'
GROUP BY
y,
m
ORDER BY
y DESC,
m DESC
";
    $query = $wpdb->prepare($sql, $tk);
    $ym_items = $wpdb->get_results($query);
    $ym_array = [];
    foreach ($ym_items as $item) {
        $ym_array[$item->y][$item->m] = $item->c;
    }
    $out = '<ul class="archive-list">';
    foreach ($ym_array as $y => $y_items) {
        $out .= '<li class="year">' . (int)$y;
        $out .= '<ul class="month-archive-list">';
        foreach ($y_items as $m => $c) {
            // rawurlencode() → URLパラメータ安全化
            // esc_url() → href用URL安全化、esc_html() → 表示文字列のXSS対策
            // (int)$c → 数値固定
            $year  = (int)$y;
            $month = (int)$m;

            // URLを安全化
            $url = esc_url(
                home_url($year . '/' . $month . '?tn=' . rawurlencode((string)$tn))
            );

            // 表示文字列をエスケープ
            $out .= '<li><a href="' . $url . '">' .
                esc_html($year . '年' . $month . '月') .
                '</a>(' . (int)$c . ')</li>';
        }
        $out .= '</ul>';
    }
    $out .= '</li>';
    $out .= '</ul>';
    echo "
<p id=\"sampleOutput\"></p>
<div class=\"widget_archive\">
<div class=\"side-title\">月別アーカイブ(monthly archive)</div>
{$out}
</div>
";
}

function display_pagenavi()
{
    echo '<div class="page-navi">';

    global $tn;
    global $current_page;
    global $posts_per_page;
    global $post_count;

    // 数値を安全化
    $tn             = (int) $tn;
    $posts_per_page = max(1, (int) $posts_per_page); // 1ページあたりの件数は最低でも「1」以上にする（0割エラー防止）
    $post_count     = max(0, (int) $post_count);     // 総投稿数は最低でも「0」以上にする
    $pages          = max(1, (int) ceil($post_count / $posts_per_page));
    // 【バグ防止】現在のページ番号が「1未満」や「最大ページ数を超える」ことがないように、正しい範囲に収める
    $current_page   = max(1, min((int) $current_page, $pages));

    // 1度に表示するページ数
    $display_pages = 10;

    // 【ページ数計算】総投稿数を1ページの件数で割り、小数点以下を切り上げて総ページ数を算出（最低1ページ）
    $start_page = (int)(floor(($current_page - 1) / $display_pages) * $display_pages) + 1;
    // ブロックの「終了ページ番号」を計算（全体の最大ページ数を超えないように制御）
    $end_page   = min($pages, $start_page + $display_pages - 1);

    // ベースURLを安全化
    // 【安全対策】自作の get_template_url 関数を使って、現在のページのベースURLを取得
    $url = get_template_url($tn, true);

    // 検索ページなら検索語を引き継ぐ
    $search_query = is_search() ? get_search_query(false) : null;

    // 【ページURL生成用の関数】使い回しができるよう、その場限りの匿名関数（クロージャ）を定義
    $make_page_url = function ($page) use ($url, $tn, $search_query) {
        // 基本となるパラメータ（cp = ページ番号、tn = テンプレート番号）をセット
        $args = [
            'cp' => max(1, (int) $page),
            'tn' => $tn,
        ];

        // 検索キーワードが存在する場合（空文字でもない場合）は、パラメータに「s」としてキーワードを追加
        if ($search_query !== null && $search_query !== '') {
            $args['s'] = $search_query;
        }

        // ベースのURLに、作成したパラメータ（?cp=✕&tn=✕&s=✕）を安全に結合して返す
        return add_query_arg($args, $url);
    };

    // 「最初へ（＜＜）」のリンクを、URLとテキストのそれぞれをエスケープして出力（1ページ目へのリンク）
    echo '<a class="page-nav-link" href="' . esc_url($make_page_url(1)) . '">' . esc_html('＜＜') . '</a> ';

    // 1つ前の10ページブロックの開始ページを計算し、「前へ（＜）」のリンクを出力（最低でも1ページ目）
    $prev_block_page = max(1, $start_page - $display_pages);
    echo '<a class="page-nav-link" href="' . esc_url($make_page_url($prev_block_page)) . '">' . esc_html('＜') . '</a> ';

    // 計算した「開始ページ」から「終了ページ」まで、ループを回して数字のページリンクを順番に出力
    for ($i = $start_page; $i <= $end_page; ++$i) {
        echo '<a class="page-nav-link" href="' . esc_url($make_page_url($i)) . '">' . esc_html((string) $i) . '</a> ';
    }

    // 1つ次の10ページブロックの開始ページを計算し、「次へ（＞）」のリンクを出力（最大ページ数を超えない）
    $next_block_page = min($pages, $start_page + $display_pages);
    echo '<a class="page-nav-link" href="' . esc_url($make_page_url($next_block_page)) . '">' . esc_html('＞') . '</a> ';

    // 「最後へ（＞＞）」のリンクを、最大ページ数を指定して出力
    echo '<a class="page-nav-link" href="' . esc_url($make_page_url($pages)) . '">' . esc_html('＞＞') . '</a> ';

    echo '</div>';
}

// 3日間ランキングを表示
function display_3day_ranking()
{
    // ランキング取得
    // posts_per_page      : 表示件数
    // post_status         : 公開済み記事のみ取得（下書き・非公開除外）
    // ignore_sticky_posts : 固定記事をランキングに混ぜない
    // meta_key            : PV保存用カスタムフィールド
    // orderby             : 数値として並び替え
    // no_found_rows       : 総件数取得SQLを省略して軽量化
    $ranking_posts = get_posts([
        'posts_per_page'      => 12,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'meta_key'            => 'pv_count_3day',
        'orderby'             => 'meta_value_num',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    ]);
?>

    <div class="three-day-ranking">

        <div class="side-title">3days ranking</div>

        <div class="AMvertical black">

            <section class="popular-box">

                <?php if (!empty($ranking_posts)) : ?>

                    <ul>

                        <?php
                        // ランキング番号用
                        // 未定義防止のため初期化
                        // $count = 1;

                        // 【ループ処理】取得した12件の記事データを1件ずつ順番に処理する
                        foreach ($ranking_posts as $ranking_post) :

                            // 【重要】WordPress の標準関数（get_the_titleなど）が正しく動くように、現在の投稿データをグローバル変数にセット
                            setup_postdata($ranking_post);

                            // 【型安全】現在の投稿のオブジェクトから「投稿ID」を取得し、念のため(int)で整数型に固定する
                            $post_id = (int) $ranking_post->ID;
                        ?>

                            <li>

                                <!-- 記事URL -->
                                <!-- esc_url() でURLを安全に出力 -->
                                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="ranking-link">

                                    <?php
                                    // サムネイルが存在する場合のみ表示
                                    if (has_post_thumbnail($post_id)) {

                                        // サムネイルを出力
                                        // 引数1: 投稿ID
                                        // 引数2: 画像サイズ（横100px、縦100px）
                                        // 引数3: 属性のカスタマイズ（alt属性にはタイトルを esc_attr で安全に適用、loading="lazy" で遅延読み込みさせて軽量化）
                                        echo get_the_post_thumbnail(
                                            $post_id,
                                            [100, 100],
                                            [
                                                'alt'    => esc_attr(get_the_title($post_id)),
                                                'loading' => 'lazy',
                                            ]
                                        );
                                    }
                                    ?>

                                    <div class="modelName">

                                        <span class="name">

                                            <!-- タイトル出力 -->
                                            <!-- esc_html() でXSS対策 -->
                                            <?php echo esc_html(get_the_title($post_id)); ?>

                                            <!-- class化 -->
                                            <!-- idはループ内重複禁止のため使用しない -->
                                            <span class="likeCount3"></span>

                                        </span>

                                    </div>

                                </a>

                                <?php
                                // 閲覧回数表示用
                                // 出力時は esc_html() 推奨
                                /*
                                echo esc_html(
                                    getPostViews3days($post_id)
                                );
                                */
                                ?>

                            </li>

                        <?php
                        // ランキング番号を加算
                        // $count++;

                        endforeach;

                        // 【最重要】setup_postdata() によって書き換えられたグローバルな投稿データを元の状態にリセットする
                        // これを忘れると、この関数を呼び出した後の他のパーツ（サイドバーやフッターなど）が正常に表示されなくなります
                        wp_reset_postdata();
                        ?>

                    </ul>

                <?php else : ?>

                    <!-- ランキングが空の場合 -->
                    <p>
                        <?php
                        echo esc_html(
                            'アクセスランキングはまだ集計されていません。'
                        );
                        ?>
                    </p>

                <?php endif; ?>

            </section>

        </div>

    </div>

<?php
}

function display_week_ranking()
{
    $ranking_posts = get_posts([
        'posts_per_page'      => 6,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'meta_key'            => 'pv_count_week',
        'orderby'             => 'meta_value_num',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    ]);
?>

    <div class="week-ranking">
        <div class="side-title">week ranking</div>

        <?php if (!empty($ranking_posts)) : ?>
            <ul class="week-ranking-list">

                <?php foreach ($ranking_posts as $ranking_post) : ?>
                    <?php
                    setup_postdata($ranking_post);

                    $post_id = (int) $ranking_post->ID;
                    $title   = get_the_title($post_id);
                    $url     = get_permalink($post_id);
                    ?>

                    <li class="week-ranking-item">
                        <a href="<?php echo esc_url($url); ?>" class="week-ranking-link">

                            <?php
                            // 【条件分岐】その記事にアイキャッチ画像（サムネイル）が設定されている場合のみ処理
                            if (has_post_thumbnail($post_id)) {
                                // アイキャッチ画像のHTMLタグ（<img>）を生成して出力
                                // 引数1: 投稿ID
                                // 引数2: 画像サイズ（'full' = オリジナルサイズ）
                                // 引数3: 属性のカスタマイズ（class名の付与、alt属性にはタイトルを esc_attr で安全に適用、loading="lazy" で遅延読み込みを有効化）
                                echo get_the_post_thumbnail(
                                    $post_id,
                                    'full',
                                    [
                                        'class'   => 'week-ranking-img',
                                        'alt'     => esc_attr($title),
                                        'loading' => 'lazy',
                                    ]
                                );
                            }
                            ?>

                            <div class="week-ranking-title">
                                <?php echo esc_html($title); ?>
                            </div>

                        </a>
                    </li>

                <?php endforeach; ?>

                <?php wp_reset_postdata(); ?>

            </ul>
        <?php else : ?>
            <p><?php echo esc_html('アクセスランキングはまだ集計されていません。'); ?></p>
        <?php endif; ?>
    </div>

<?php
}

//検索欄
function display_search_form()
{
    global $tn;
?>
    <form
        method="get"
        id="searchform"
        class="searchform"
        action="<?php echo esc_url(home_url('/')); ?>">
        <div class="text-form">

            <!-- 検索キーワード -->
            <input
                type="text"
                placeholder="ブログ内を検索"
                name="s"
                id="s"
                value="<?php echo esc_attr(get_search_query()); ?>"
                maxlength="100" />

            <!-- テンプレート番号 -->
            <input
                type="hidden"
                name="tn"
                value="<?php echo esc_attr((string)$tn); ?>">

        </div>

        <div class="form-bottom">
            <input type="submit" id="searchsubmit" value="Q">
        </div>
    </form>
    <?php
}

//最近のコメント
function display_comment()
{
    // 【データ取得設定】コメントを取得するための条件（パラメーター）を指定
    $args = array(
        'author__not_in' => [1],       // ユーザーID「1」（通常はサイト管理者）のコメントを除外
        'number'         => 5,         // 最新のコメントを最大5件まで取得
        'status'         => 'approve', // 承認済みのコメントのみ取得（スパムや未承認を除外）
        'type'           => 'comment', // 純粋なコメントのみ取得（トラックバックやピンバックを除外）
        'no_found_rows'  => true,      // ページ送り（ページネーション）をしないため、総件数取得を省略してSQLを軽量化
    );

    // WordPressのコメントクエリ用のインスタンスを作成
    $comments_query = new WP_Comment_Query();
    // 設定した条件でデータベースからコメントデータを取得
    $comments = $comments_query->query($args);

    // 【条件分岐】コメントが1件以上存在する場合の処理
    if ($comments) {
    ?>
        <!-- 表示部分 -->
        <div class="commentlist">
            <div class="side-title">最近のコメント(comments)</div>
            <?php
            foreach ($comments as $comment) {
                // 記述が長いので $pid に入れておく
                // 投稿IDなので整数に固定した方が安全
                $pid = (int) $comment->comment_post_ID;
                // 【プライバシー保護】そのコメントがついている親記事が、現在「公開中（publish）」であるかチェック
                // 下書き、非公開、ゴミ箱にある記事のコメントなら、表示をスキップ（continue）して次のコメントへ行く
                if (get_post_status($pid) !== 'publish') {
                    continue;
                }
                // コメントがついている記事の詳細ページURLを取得
                $url = get_permalink($pid);

                // 【エラー防止】何らかの理由でURLが正常に取得できなかった場合は表示をスキップ
                if (!$url) {
                    continue;
                }
                // コメントがついている記事のタイトルを取得
                $title = get_the_title($pid);
                // 記事のサムネイル（アイキャッチ画像）を取得
                // alt属性には記事のタイトルを安全に（esc_attr）適用し、loading="lazy"で遅延読み込みを有効化
                $img = get_the_post_thumbnail(
                    $pid,
                    'thumbnail',
                    [
                        'class'   => 'myClass',
                        'alt' => esc_attr($title),
                        'loading' => 'lazy',
                    ]
                );
                // コメントが投稿された日付を指定したフォーマット「(年/月/日)」で取得
                $date = get_comment_date('(Y/n/d)', $comment->comment_ID);
                // コメントの本文（生データ）を取得
                $text = get_comment_text($comment->comment_ID);
                // $user_id = $comment->comment_author;
                // デフォルト値で初期化して
                // $user_id = '名無しさん(anonymous)';

                /* if (!empty($comment->comment_author)) {
                    $user_id = $comment->comment_author;
                } elseif (!empty($comment->user_id)) {
                    $user_id = $comment->user_id;
                }*/
            ?>
                <div class="recentcomment">
                    <ul class="mycomment">
                        <li class="imgcomment">
                            <a class="recentcomment-link" href="<?php echo esc_url($url); ?>">
                                <div class="commentheight">
                                    <?php echo wp_kses_post($img); ?>
                                </div>
                                <div class="com_title">
                                    <?php echo esc_html($title); ?>
                                </div>
                                <div class="commentnumber">
                                    <p class="comment">
                                        <?php
                                        // 【安全対策＆文字数制限】
                                        // 1. wp_strip_all_tags() でコメント本文内のHTMLタグを完全に消去
                                        // 2. mb_strimwidth() で、長文のコメントを先頭から「38バイト（日本語で約19文字）」に切り詰め、末尾に「･･･」を付与
                                        // 3. 最後に esc_html() で完全にエスケープ処理をしてから出力（非常に堅牢な設計です）
                                        echo esc_html(mb_strimwidth(wp_strip_all_tags($text), 0, 38, '･･･'));
                                        ?>
                                    </p>
                                    <p class="my_author">
                                        <?php echo esc_html($date); ?>
                                    </p><br>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php
            }
            ?>
        </div>
    <?php
    } else {
        echo esc_html('コメントなし');
    }
    ?>
<?php
}

function get_categories_array()
{
    $categories = [];
    foreach (get_categories() as $category) {
        $category->category_link = get_category_link($category->cat_ID);
        $categories[$category->cat_ID] = $category;
    }
    return $categories;
}

function display_rss_post_1()
{
    global $tn;
    global $block_per_page;
    global $limitSect1;
    global $limitSect2;
    global $limitSect3;
    global $rss_per_block;
    global $group_per_block;
    global $posts_per_group;
    global $rss_items;
    global $post_items;
    global $categories;
    // 【外側ループ】指定されたブロック数分だけ処理を繰り返す（ページの縦の長さを決めるループ）
    for ($i = 0; $i < $block_per_page; ++$i) {
        /*echo '<h3>RSS</h3>';*/
        // 各RSSセクション（A, B, C）のHTMLを蓄積するための変数を初期化
        $contentA = '';
        $contentB = '';
        $contentC = '';
        // 【内側ループ1】1ブロックあたりのRSS表示件数分だけ処理を繰り返す
        for ($j = 0; $j < $rss_per_block; ++$j) {
            // 全体のRSS配列から、今回処理するべきRSSのインデックス（順番）を計算
            $item_index = $i * $rss_per_block + $j;
            // 計算したインデックスが、実際に存在するRSSの総数を超えたら安全にループを終了
            if ($item_index >= count($rss_items)) {
                break;
            }
            // 該当するRSSの1件分のデータを取得
            $item = $rss_items[$item_index];
            // 安全にエスケープ（無害化）しつつ、リンク付きの太字タイトルHTMLを生成
            $title = '<strong><a href="' . esc_url($item->link) . '">' . esc_html($item->title) . '</a></strong>';
            // 【条件分岐】RSSの画像が空、または正しいURL形式ではない場合
            if (empty($item->img) || !filter_var($item->img, FILTER_VALIDATE_URL)) {

                // ※RSS画像が空、またはURL形式ではない場合はダミー画像を表示
                $img = home_url('/wp-content/themes/sample_theme/images/noimage.png');
            } else {

                // ※RSS画像がある場合はRSS画像を表示
                $img = $item->img;
            }

            // 画像読み込みエラー（リンク切れなど）が発生したとき用のフォールバック画像URL
            $dummy_img = esc_url(
                home_url('/wp-content/themes/sample_theme/images/noimage.png')
            );

            // RSS用の画像リンクHTMLタグを生成
            // onerror属性を使い、万が一画像が読み込めなかった場合は、JSで即座にダミー画像に差し替える仕組み
            $image = '<a href="' . esc_url($item->link) . '">
            <img
                src="' . esc_url($img) . '"
                class="rss-image"
                onerror="this.onerror=null; this.src=\'' . $dummy_img . '\';"
                alt="">
            </a>';
            // RSSのサブジェクト（カテゴリ等）を先頭10文字に切り詰めてエスケープし、リンクタグを生成
            $subject = '<a href="' . esc_url($item->link) . '">' . esc_html(mb_substr($item->subject, 0, 10)) . '</a>';
            // 【条件分岐】現在のRSSがどの表示セクション（A, B, C）に属するか判定してHTMLを組み立てる
            if ($j < $limitSect1) {
                // セクションA：タイトルのみのリスト
                $contentA .= "<li class=\"sitelink\">{$title}</li>"; // タイトルのみ
            } elseif ($j < $limitSect1 + $limitSect2) {
                // セクションB：画像・タイトル・サブジェクトボタン付きのカードレイアウト
                $contentB .= "<li class=\"sitelink2\"><figure class=\"snip\"><figcaption>{$image}<br>{$title}<p class=\"btn\">{$subject}</p></figcaption></figure></li>"; // 画像と画像の下にタイトル
            } else {
                // セクションC：画像と太字タイトルを縦並び（別スタイル）にしたリスト
                $contentC .= '
                <li class="sitelink3">
                    <a class="sitelink3-link" href="' . esc_url($item->link) . '">

                        <img
                            src="' . esc_url($img) . '"
                            class="rss-image"
                            onerror="this.onerror=null; this.src=\'' . $dummy_img . '\';"
                            alt="">

                        <strong>' . esc_html($item->title) . '</strong>

                    </a>
                </li>';
            }
        }
        // 組み立てたRSSブロック（A, B, C）をまとめて画面に出力
        echo '<div class="rssBlock">';
        echo "<ul class=\"wiget-rss\">{$contentA}</ul>";
        echo "<ul class=\"wiget-rss\">{$contentB}</ul>";
        echo "<ul class=\"wiget-rss\">{$contentC}</ul>";
        echo '</div>';

        // ここから自サイトの記事（投稿）を表示するコンテナを出力
        echo '<div id="entry-content">'; // 記事全体のid
        // 【内側ループ2】1ブロックに表示するグループ数分だけループ
        for ($k = 0; $k < $group_per_block; ++$k) {
            // 【内側ループ3】1グループあたりの記事数分だけループ
            // ここから画像とタイトルの処理
            for ($j = 0; $j < $posts_per_group; ++$j) {
                // 全体の投稿配列から、今回表示すべき投稿のインデックス（順番）を計算
                $item_index = $i * $group_per_block * $posts_per_group + $k * $posts_per_group + $j;
                // 投稿データの総数を超えたら安全にループを抜ける
                if ($item_index >= count($post_items)) {
                    break;
                }
                // 表示する自サイトの投稿データを1件取得
                $item = $post_items[$item_index];
                // 投稿に付随する追加データ（サムネイル等）をセットするカスタム関数を実行
                set_other_data($item);
                // タイトルの保存は省略
                // ここから追加
                echo '<div class="entry-post">'; // 記事1つ1つ
                // サムネイル画像のリンクと画像を安全にエスケープして出力
                echo '<figure class="entry-thumnail">
                <a href="' . esc_url($item->guid) . '">
                <img src="' . esc_url($item->thumbnail) . '" alt="">
                </a>
                </figure>'; // サムネイル画像
                echo '<div class="entry-card-content">';
                echo '<header class="entry-header">';
                echo '<h2 class="entry-title"><a href="' . esc_url($item->guid) . '">' . esc_html($item->post_title) . '</a></h2>'; // タイトル
                // メタ情報（日付、カテゴリ、コメント数）の出力開始
                echo '<p class="post-meta">'; // 日付け、カテゴリー、コメント数
                echo '<span class="fa-clock fa-fw"></span>'; // 日付けのマーク fontawesomeをbeforeで読み込む
                // echo '<span class="published">' . esc_html($item->post_date) . '</span>'; // 日付け
                // WordPressの get_the_date を用いて投稿日を安全に取得・出力
                echo '<span class="published">'
                    . esc_html(get_the_date('Y-m-d', $item->ID))
                    . '</span>';
                echo '<span class="fa-folder fa-fw"></span>'; // カテゴリーのマーク fontawesomeをbeforeで読み込む
                echo '<span class="category-link">';
                // 記事にカテゴリーが設定されている場合、ループしてすべて出力
                if ($item->categories) {
                    foreach ($item->categories as $cat_ID) {
                        // 全体カテゴリー情報から該当するオブジェクトを取得
                        $category = $categories[$cat_ID];
                        // 識別子 ?tn= の値を rawurlencode でURL用に安全にエンコードしてリンクを出力
                        echo '<a href="' . esc_url($category->category_link . '?tn=' . rawurlencode((string)$tn)) . '">' . esc_html($category->cat_name) . '</a>';
                    }
                }
                echo '</span>'; // カテゴリー
                // コメント数の出力（FontAwesomeアイコンとコメント件数）
                echo '<span class="comment-count">';
                echo '<span class="fa-comment fa-fw"></span>';
                echo '<a href="' . esc_url($item->guid) . '">' . esc_html($item->comments) . '</a>';
                echo '</span>';
                echo '</p>';
                echo '</header>';
                echo '<p class="entry-snippet">'
                    // 記事本文の抜粋（wp_trim_words関数で先頭28文字にカットし、末尾に「...」を付与して安全に出力）
                    . esc_html(wp_trim_words($item->post_excerpt, 28, '...'))
                    . '</p>'; // 抜粋
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>'; //記事全体のid
    }
}
function display_rss_post_2()
{
    global $tn;
    global $block_per_page;
    global $limitSect1;
    global $limitSect2;
    global $limitSect3;
    global $rss_per_block;
    global $group_per_block;
    global $posts_per_group;
    global $rss_items;
    global $post_items;
    global $categories;
    // 【外側ループ】ブロック数分繰り返す
    for ($i = 0; $i < $block_per_page; ++$i) {
        /*echo '<h3>RSS</h3>';*/
        // RSS用のHTML格納変数を初期化
        $contentA = '';
        $contentB = '';
        $contentC = '';
        // 【内側ループ1】RSS表示処理（※セクションCのHTML構造（DIVでのラップなど）を除き、パターン1とほぼ同様）
        for ($j = 0; $j < $rss_per_block; ++$j) {
            $item_index = $i * $rss_per_block + $j;
            if ($item_index >= count($rss_items)) {
                break;
            }
            $item = $rss_items[$item_index];
            $title = '<strong><a href="' . esc_url($item->link) . '">' . esc_html($item->title) . '</a></strong>';
            if (empty($item->img) || !filter_var($item->img, FILTER_VALIDATE_URL)) {

                // ※RSS画像が空、またはURL形式ではない場合はダミー画像を表示
                $img = home_url('/wp-content/themes/sample_theme/images/noimage.png');
            } else {

                // ※RSS画像がある場合はRSS画像を表示
                $img = $item->img;
            }

            $dummy_img = esc_url(
                home_url('/wp-content/themes/sample_theme/images/noimage.png')
            );

            $image = '<a href="' . esc_url($item->link) . '">
            <img
                src="' . esc_url($img) . '"
                class="rss-image"
                onerror="this.onerror=null; this.src=\'' . $dummy_img . '\';"
                alt="">
            </a>';
            $subject = '<a href="' . esc_url($item->link) . '">' . esc_html(mb_substr($item->subject, 0, 10)) . '</a>';
            if ($j < $limitSect1) {
                $contentA .= "<li class=\"sitelink\">{$title}</li>"; // タイトルのみ
            } elseif ($j < $limitSect1 + $limitSect2) {
                $contentB .= "<li class=\"sitelink2\"><figure class=\"snip\"><figcaption>{$image}<br>{$title}<p class=\"btn\">{$subject}</p></figcaption></figure></li>"; // 画像と画像の下にタイトル
            } else {
                // パターン2独自のセクションC：内側がレイアウト用CSS向けの独自DIV構造になっている
                $contentC .= "
                <li class=\"sitelink3\">
                    <div class=\"sitelink3-inner\">
                        <div class=\"sitelink3-image\">{$image}</div>
                        <div class=\"sitelink3-title\">{$title}</div>
                    </div>
                </li>";
            }
        }
        echo '<div class="rssBlock">';
        echo "<ul class=\"wiget-rss\">{$contentA}</ul>";
        echo "<ul class=\"wiget-rss\">{$contentB}</ul>";
        echo "<ul class=\"wiget-rss\">{$contentC}</ul>";
        echo '</div>';

        // ここから自サイトの記事（まとめ・コンボ表示）エリアを出力
        echo '<div id="itemWrapper">'; // 記事全体のid
        // 【内側ループ2】グループ数分ループ
        for ($k = 0; $k < $group_per_block; ++$k) {
            // このグループに属する全記事の画像タグを結合して蓄積するための変数を初期化
            $images = '';
            // 代表記事のデータを保持するための変数を事前に null で初期化（★安全対策が追加！）
            $keep_item = null;
            // 【内側ループ3】グループ内の設定記事数分ループを実行し、画像タグを集める
            for ($j = 0; $j < $posts_per_group; ++$j) {
                // 全体から今回のインデックスを計算
                $item_index = $i * $group_per_block * $posts_per_group + $k * $posts_per_group + $j;
                // 投稿データの総数を超えたらループを抜ける
                if ($item_index >= count($post_items)) {
                    break;
                }
                // 投稿データを取得し、付随データをセット
                $item = $post_items[$item_index];
                set_other_data($item);

                // 【特殊処理1】グループ内の「最初の記事（$j === 0）」の場合
                if ($j === 0) {
                    // もしグループ内に1件しか記事がなくてもエラーにならないよう、一旦これを代表記事（フォールバック）としてキープ
                    $keep_item = $item;
                }

                // 【特殊処理2】グループ内の「2番目の記事（$j === 1）」の場合
                if ($j === 1) {
                    // 本来の仕様通り、2番目の記事データをこのグループの代表テキスト用（タイトル等）として上書きキープ
                    $keep_item = $item;
                }
                // グループ内の全記事のサムネイル画像リンクを生成し、文字列として後ろにどんどん結合（.=）していく
                // 末尾には識別用として安全に整数キャストした (int)$item->ID も付与
                $images .= '<a href="' . esc_url($item->guid) . '"><img src="' . esc_url($item->thumbnail) . '" alt=""></a>(' . (int)$item->ID . ')';
            }
            // 【安全ガード】もし代表記事が1件も取得できていなければ（nullのままなら）、処理を行わず安全にループを離脱（★安全対策が追加！）
            if ($keep_item === null) {
                break;
            }
            // タイトルの保存は省略
            // ここから追加
            $item = $keep_item;
            // まとめて表示する1つの記事枠を出力開始
            echo '<div class="itemInner">'; // 記事1つ1つ
            // 溜め込んだグループ内の全画像（$images）を、1つの <figure> タグの中にまとめて一挙出力
            echo "<figure class=\"itemThumnail\">{$images}</figure>"; // サムネイル画像
            echo '<div class="item-outer">';
            echo '<header class="itemHead">';
            // 代表記事のタイトルを安全に出力
            echo '<h2 class="entry-title"><a href="' . esc_url($item->guid) . '">' . esc_html($item->post_title) . '</a></h2>'; // タイトル
            // メタ情報の出力
            echo '<p class="itemInfo">'; // 日付け、カテゴリー、コメント数
            echo '<span class="fa-clock fa-fw"></span>'; // 日付けのマーク fontawesomeをbeforeで読み込む
            // 代表記事の投稿日（post_date）を安全にエスケープして出力
            echo '<span class="itemDate">' . esc_html($item->post_date) . '</span>'; // 日付け
            echo '<span class="fa-folder fa-fw"></span>'; // カテゴリーのマーク fontawesomeをbeforeで読み込む
            echo '<span class="itemCategory">';
            // 代表記事のカテゴリーリンクを安全に出力（?tn= は rawurlencode で保護）
            if ($item->categories) {
                foreach ($item->categories as $cat_ID) {
                    $category = $categories[$cat_ID];
                    echo '<a href="' . esc_url($category->category_link . '?tn=' . rawurlencode((string)$tn)) . '">' . esc_html($category->cat_name) . '</a>';
                }
            }
            echo '</span>'; // カテゴリー
            echo '<span class="fa-comment fa-fw"></span>'; // コメント数のマーク fontawesomeをbeforeで読み込む
            // 代表記事のコメント数を安全に出力
            echo '<span class="clickCnt"><a href="' . esc_url($item->guid) . '">' . esc_html($item->comments) . '</a></span>'; // コメント数
            echo '</p>';
            echo '</header>';
            // 代表記事の抜粋テキストを安全に出力
            echo '<p class="itemSnippet">' . esc_html($item->post_excerpt) . '</p>'; // 抜粋
            echo '</div>'; // .item-outer の閉じタグ
            echo '</div>'; // .itemInner の閉じタグ
        }
    }
    echo '</div>'; // #itemWrapper の閉じタグ
}

function set_other_data($post)
{
    // アイキャッチIDを取得
    $post_thumbnail_id = get_post_thumbnail_id($post);
    // アイキャッチ画像の確認
    if ($post_thumbnail_id) {
        // 存在する
        $image_src = wp_get_attachment_image_src($post_thumbnail_id);
        // サムネイルの画像URLを設定
        $post->thumbnail = $image_src[0];
    } else {
        // 存在しない
        $post->thumbnail = 'noimage.jpg';
    }
    // カテゴリーIDを取得
    $post->categories = wp_get_post_categories($post->ID);
    // コメントテキスト
    if (0 == $post->comment_count) {
        // コメントなし
        //$post->comments = __('No Comments');
    } else {
        // コメントあり
        $post->comments = $post->comment_count . '件のコメント';
    }
    // コメントリンク
    $post->comments_link = get_comments_link($post->ID);
}

function display_main_banner()
{
    //配列にしてまとめる(画像リンクとタイトル)
    //配列にしてまとめる(画像リンクとタイトル)
    $banner_data = [
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_keifont.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_keifont.png', '2枚絵比較', 'http://www.irasuto.cfbx.jp/image-2/'],
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_jiyunotsubasa.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_TanukiMagic.png', '3枚絵比較', 'http://www.irasuto.cfbx.jp/image-3/'],
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_cinecaption227.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_nicomoji-plus_v09.png', '掲示板質問1', 'http://www.irasuto.cfbx.jp/informtion/'],
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_nicokaku_v1.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_chogokubosogothic5.png', '掲示板質問2', '掲示板質問1', 'http://www.irasuto.cfbx.jp/entry/'],
    ];

    $buf = [];
    foreach ($banner_data as $tmp) {
        list($img1, $img2, $title, $link) = $tmp;
        if (intval(date('H')) >= 12) $img1 = $img2;
        $buf[] = sprintf(
            '<li><a href="%s"><img src="%s" title="%s" alt="%s"></a></li>',
            esc_url($link),
            esc_url($img1),
            esc_attr($title),
            esc_attr($title)
        );
    }
?>
    <!-- 表示部分 -->
    <div id="main-banner">
        <ul class="menu banner-menu">
            <?= implode(PHP_EOL, $buf) . PHP_EOL ?>
        </ul>
    </div>
<?php
}
