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
    if ($check_search && is_category()) {
        $term = get_queried_object();

        if ($term instanceof WP_Term) {
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
    } elseif (1 === $template_number || $check_search && is_search()) {
        $sub = '';
    } else {
        $sub = "画像{$template_number}タイトル1";
    }

    return home_url($sub);
}
function get_template_number()
{
    global $template;
    // 修正前: $template_number = $_GET['tn'];
    // tn は数値だけという前提をコード側で強制
    $template_number = isset($_GET['tn'])
        ? (int)$_GET['tn']
        : 1; // 35行目
    switch ($template_number) {
        case 2:
            break;
        case 3:
            break;
        default:
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
    if (ctype_digit($cp)) {
        // 数字なら int化
        $cp = (int) $cp; // ここを $_GET['cp'] ではなく $cp に変更

        // 1未満防止
        if ($cp < 1) {
            $cp = 1;
        }
    } else {
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
    global $wpdb;
    global $tn;
    global $cat;
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
    $query = $wpdb->prepare($sql, 'category_count_week', 20);
    $terms = $wpdb->get_results($query);
    if ($terms) {
        $out = '<ul class="category-ranking clearfix">';
        $tag_link_count = 0;

        foreach ($terms as $term) {
            // rawurlencode()でURLの構成要素を安全にエンコード
            $term_link = get_term_link($term);

            // カテゴリ1個壊れてもランキング全体は表示される
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
    $posts_per_page = max(1, (int) $posts_per_page);
    $post_count     = max(0, (int) $post_count);
    $pages          = max(1, (int) ceil($post_count / $posts_per_page));
    $current_page   = max(1, min((int) $current_page, $pages));

    // 1度に表示するページ数
    $display_pages = 10;

    // 今のページが属する10ページ単位の開始・終了
    $start_page = (int)(floor(($current_page - 1) / $display_pages) * $display_pages) + 1;
    $end_page   = min($pages, $start_page + $display_pages - 1);

    // ベースURLを安全化
    $url = get_template_url($tn, true);

    // 検索ページなら検索語を引き継ぐ
    $search_query = is_search() ? get_search_query(false) : null;

    // ページURL生成用
    $make_page_url = function ($page) use ($url, $tn, $search_query) {
        $args = [
            'cp' => max(1, (int) $page),
            'tn' => $tn,
        ];

        if ($search_query !== null && $search_query !== '') {
            $args['s'] = $search_query;
        }

        return add_query_arg($args, $url);
    };

    // 最初へ
    echo '<a class="page-nav-link" href="' . esc_url($make_page_url(1)) . '">' . esc_html('＜＜') . '</a> ';

    // 前の10ページへ
    $prev_block_page = max(1, $start_page - $display_pages);
    echo '<a class="page-nav-link" href="' . esc_url($make_page_url($prev_block_page)) . '">' . esc_html('＜') . '</a> ';

    // 10件分のページ番号
    for ($i = $start_page; $i <= $end_page; ++$i) {
        echo '<a class="page-nav-link" href="' . esc_url($make_page_url($i)) . '">' . esc_html((string) $i) . '</a> ';
    }

    // 次の10ページへ
    $next_block_page = min($pages, $start_page + $display_pages);
    echo '<a class="page-nav-link" href="' . esc_url($make_page_url($next_block_page)) . '">' . esc_html('＞') . '</a> ';

    // 最後へ
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

                        // ランキング記事ループ
                        foreach ($ranking_posts as $ranking_post) :

                            // WordPress の投稿データをセット
                            setup_postdata($ranking_post);

                            // 投稿IDを整数化
                            // 念のため型を固定
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
                                        // alt属性は esc_attr() で属性用エスケープ
                                        // loading="lazy" で画像遅延読み込み
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

                        // setup_postdata() を元に戻す
                        // 忘れると他テンプレートへ影響する
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
                            if (has_post_thumbnail($post_id)) {
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
    $args = array(
        'author__not_in' => [1],
        'number' => 5,
        'status' => 'approve',
        'type' => 'comment',
        // ページ送りしないので、軽量化
        'no_found_rows'  => true,
    );
    $comments_query = new WP_Comment_Query();
    $comments = $comments_query->query($args);
    // Comment Loop
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
                // 親記事が公開中かどうか
                if (get_post_status($pid) !== 'publish') {
                    continue;
                }
                // 必要な文字列データの取得
                $url = get_permalink($pid);

                if (!$url) {
                    continue;
                }
                $title = get_the_title($pid);
                $img = get_the_post_thumbnail(
                    $pid,
                    'thumbnail',
                    [
                        'class'   => 'myClass',
                        'alt' => esc_attr($title),
                        'loading' => 'lazy',
                    ]
                );
                $date = get_comment_date('(Y/n/d)', $comment->comment_ID);
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
                                        <?php echo esc_html(mb_strimwidth(wp_strip_all_tags($text), 0, 38, '･･･')); ?>
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
    // 表示
    for ($i = 0; $i < $block_per_page; ++$i) {
        /*echo '<h3>RSS</h3>';*/
        $contentA = '';
        $contentB = '';
        $contentC = '';
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
        echo '<div class="rssBlock">';
        echo "<ul class=\"wiget-rss\">{$contentA}</ul>";
        echo "<ul class=\"wiget-rss\">{$contentB}</ul>";
        echo "<ul class=\"wiget-rss\">{$contentC}</ul>";
        echo '</div>';

        echo '<div id="entry-content">'; // 記事全体のid
        for ($k = 0; $k < $group_per_block; ++$k) {
            // ここから画像とタイトルの処理
            for ($j = 0; $j < $posts_per_group; ++$j) {
                $item_index = $i * $group_per_block * $posts_per_group + $k * $posts_per_group + $j;
                if ($item_index >= count($post_items)) {
                    break;
                }
                $item = $post_items[$item_index];
                set_other_data($item);
                // タイトルの保存は省略
                // ここから追加
                echo '<div class="entry-post">'; // 記事1つ1つ
                echo '<figure class="entry-thumnail">
                <a href="' . esc_url($item->guid) . '">
                <img src="' . esc_url($item->thumbnail) . '" alt="">
                </a>
                </figure>'; // サムネイル画像
                echo '<div class="entry-card-content">';
                echo '<header class="entry-header">';
                echo '<h2 class="entry-title"><a href="' . esc_url($item->guid) . '">' . esc_html($item->post_title) . '</a></h2>'; // タイトル
                echo '<p class="post-meta">'; // 日付け、カテゴリー、コメント数
                echo '<span class="fa-clock fa-fw"></span>'; // 日付けのマーク fontawesomeをbeforeで読み込む
                // echo '<span class="published">' . esc_html($item->post_date) . '</span>'; // 日付け
                echo '<span class="published">'
                    . esc_html(get_the_date('Y-m-d', $item->ID))
                    . '</span>';
                echo '<span class="fa-folder fa-fw"></span>'; // カテゴリーのマーク fontawesomeをbeforeで読み込む
                echo '<span class="category-link">';
                if ($item->categories) {
                    foreach ($item->categories as $cat_ID) {
                        $category = $categories[$cat_ID];
                        echo '<a href="' . esc_url($category->category_link . '?tn=' . rawurlencode((string)$tn)) . '">' . esc_html($category->cat_name) . '</a>';
                    }
                }
                echo '</span>'; // カテゴリー
                echo '<span class="comment-count">';
                echo '<span class="fa-comment fa-fw"></span>';
                echo '<a href="' . esc_url($item->guid) . '">' . esc_html($item->comments) . '</a>';
                echo '</span>';
                echo '</p>';
                echo '</header>';
                echo '<p class="entry-snippet">'
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
    // 表示
    for ($i = 0; $i < $block_per_page; ++$i) {
        /*echo '<h3>RSS</h3>';*/
        $contentA = '';
        $contentB = '';
        $contentC = '';
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

        echo '<div id="itemWrapper">'; // 記事全体のid
        for ($k = 0; $k < $group_per_block; ++$k) {
            // ここから画像とタイトルの処理
            $images = '';
            for ($j = 0; $j < $posts_per_group; ++$j) {
                $item_index = $i * $group_per_block * $posts_per_group + $k * $posts_per_group + $j;
                if ($item_index >= count($post_items)) {
                    break;
                }
                $item = $post_items[$item_index];
                set_other_data($item);
                // ここからページナビまで変更
                if (1 == $j) {
                    // 真ん中の記事を保存して、タイトルを設定
                    $keep_item = $item;
                }
                /* 画像をため込む */
                $images .= '<a href="' . esc_url($item->guid) . '"><img src="' . esc_url($item->thumbnail) . '" alt=""></a>(' . (int)$item->ID . ')';
            }
            if ($item_index >= count($post_items)) {
                break;
            }
            // タイトルの保存は省略
            // ここから追加
            $item = $keep_item;
            // ここから追加
            echo '<div class="itemInner">'; // 記事1つ1つ
            echo "<figure class=\"itemThumnail\">{$images}</figure>"; // サムネイル画像
            echo '<div class="item-outer">';
            echo '<header class="itemHead">';
            echo '<h2 class="entry-title"><a href="' . esc_url($item->guid) . '">' . esc_html($item->post_title) . '</a></h2>'; // タイトル
            echo '<p class="itemInfo">'; // 日付け、カテゴリー、コメント数
            echo '<span class="fa-clock fa-fw"></span>'; // 日付けのマーク fontawesomeをbeforeで読み込む
            echo '<span class="itemDate">' . esc_html($item->post_date) . '</span>'; // 日付け
            echo '<span class="fa-folder fa-fw"></span>'; // カテゴリーのマーク fontawesomeをbeforeで読み込む
            echo '<span class="itemCategory">';
            if ($item->categories) {
                foreach ($item->categories as $cat_ID) {
                    $category = $categories[$cat_ID];
                    echo '<a href="' . esc_url($category->category_link . '?tn=' . rawurlencode((string)$tn)) . '">' . esc_html($category->cat_name) . '</a>';
                }
            }
            echo '</span>'; // カテゴリー
            echo '<span class="fa-comment fa-fw"></span>'; // コメント数のマーク fontawesomeをbeforeで読み込む
            echo '<span class="clickCnt"><a href="' . esc_url($item->guid) . '">' . esc_html($item->comments) . '</a></span>'; // コメント数
            echo '</p>';
            echo '</header>';
            echo '<p class="itemSnippet">' . esc_html($item->post_excerpt) . '</p>'; // 抜粋
            echo '</div>';
            echo '</div>';
        }
    }
    echo '</div>'; //記事全体のid
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
