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
        $category = single_cat_title('', false);
        $sub = "category/{$category}";
    } elseif ($check_search && is_archive()) {
        $y = get_query_var('year');
        $m = get_query_var('monthnum');
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
    $template_number = isset($_GET['tn']) ? $_GET['tn'] : ''; // 35行目
    switch ($template_number) {
        case '2':
            break;
        case '3':
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
        $template_key = 'single_rss_feed1';
    } elseif (2 == $template_number) {
        $template_key = 'double_rss_feed2';
    } elseif (3 == $template_number) {
        $template_key = 'triple_rss_feed3';
    }

    return $template_key;
}
function get_rss_table_name($template_number)
{
    if (1 == $template_number) {
        $rss_table_name = 'single_rss_feed';
    } elseif (2 == $template_number) {
        $rss_table_name = 'double_rss_feed';
    } elseif (3 == $template_number) {
        $rss_table_name = 'triple_rss_feed';
    } elseif (4 == $template_number) {
        $rss_table_name = 'trisect_rss_feed';
    }

    return $rss_table_name;
}
function get_current_page()
{
    // 修正前: $cp = $_GET['cp'];
    $cp = isset($_GET['cp']) ? $_GET['cp'] : ''; // 84行目
    if (ctype_digit($cp)) {
        $cp = (int) $cp; // ここを $_GET['cp'] ではなく $cp に変更
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
            echo "<div><a href=\"{$url}\">画像{$i}の一覧へ</a></div>";
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
meta_key = 'category_count_week'
AND meta_value != 0
) AS cc
ON t.term_id = cc.term_id
ORDER BY
cc.meta_value DESC
LIMIT
20
";
    $query = $wpdb->prepare($sql);
    $terms = $wpdb->get_results($query);
    if ($terms) {
        $out = '<ul class="category-ranking clearfix">';
        $tag_link_count = 0;
        foreach ($terms as $term) {
            $url = get_term_link($term) . "?tn={$tn}";
            $tag_link_count++;
            $out .= "
<li>
<!--<span class=\"material-icons\">sell
</span>-->
<a href=\"{$url}\" class=\"tag-link-{$tag_link_count}\" style=\"font-size:9pt;\" aria-lavel=\"{$term->name}（{$term->count}項目）\" >
{$term->name}
</a>
<div class=\"Information\">
</div>
</li>
";
        }
        $out .= '</ul>';
    } else {
        $out = '<p>アクセスランキングはまだ集計されていません。</p>';
    }
    echo "
<section class=\"category-box\">
{$out}
</section>
";
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
        $out .= '<li class="year">' . $y;
        $out .= '<ul class="month-archive-list">';
        foreach ($y_items as $m => $c) {
            $url = home_url("{$y}/{$m}?tn={$tn}");
            $out .= "<li><a href=\"{$url}\">{$y}年{$m}月</a>({$c})</li>";
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
</div>
";
}

function display_pagenavi()
{
    echo '<div>ページナビ</div>';
    global $tn;
    global $current_page;
    global $posts_per_page;
    global $post_count;
    $pages = ceil($post_count / $posts_per_page);
    $display_pages = 5;
    $display_page_count = 0;
    $url = get_template_url($tn, true);
    $a = filter_input(INPUT_GET, 's');
    var_dump($a);
    $s = '';
    if (is_search()) {
        $s = filter_input(INPUT_GET, 's');
        if (!empty($s)) {
            $s = "&s={$s}";
        }
    }
    for ($i = 1; $i <= $pages; ++$i) {
        if (1 == $i) {
            $page_text = '＜＜';
            echo "<a href=\"{$url}?cp={$i}&tn={$tn}{$s}\">{$page_text}</a> ";
            if ($current_page > 1) {
                $j = $current_page - 1;
            } else {
                $j = 1;
            }
            $page_text = '＜';
            echo "<a href=\"{$url}?cp={$j}&tn={$tn}{$s}\">{$page_text}</a> ";
        }
        if ($i >= $current_page && ++$display_page_count <= $display_pages) {
            $page_text = $i;
            echo "<a href=\"{$url}?cp={$i}&tn={$tn}{$s}\">{$page_text}</a> ";
        }
        if ($i == $pages) {
            if ($current_page < $pages) {
                $j = $current_page + 1;
            } else {
                $j = $pages;
            }
            $page_text = '＞';
            echo "<a href=\"{$url}?cp={$j}&tn={$tn}{$s}\">{$page_text}</a> ";
            $page_text = '＞＞';
            echo "<a href=\"{$url}?cp={$i}&tn={$tn}{$s}\">{$page_text}</a> ";
        }
    }
}

// 3日間ランキング
function display_3day_ranking()
{
    global $post;
?>
    <div class="3day-ranking">
        <div class="side-title">3days ranking</div>
        <div class="AMvertical black" style="width: 300px;">
            <section class="popular-box">
                <?php
                $args = array(
                    'numberposts'   => 12,       //表示数
                    'meta_key'      => 'pv_count_3day',
                    'orderby'       => 'meta_value_num',
                    'order'         => 'DESC',
                );
                $posts = get_posts($args);
                if ($posts) : ?>
                    <ul>
                        <?php foreach ($posts as $post) : setup_postdata($post); ?>
                            <li>
                                <a href="<?php echo get_permalink(); ?>" style="width: 97px;height: 130px">
                                    <?php if (has_post_thumbnail()) {
                                        the_post_thumbnail(array(100, 100));
                                    } ?>
                                    <div class="modelName"> <span class="name"><?php the_title(); ?><span id="likeCount3"></span></span>
                                    </div>
                                </a>
                                <div class="info topinfo">
                                    <p>
                                        <?php //　連番表示 $count = sprintf("%02d",$count); // 一桁を二桁に echo $count + 1; // 01を出力 $count++; 
                                        ?> </p>
                                </div>
                                <?/*php echo getPostViews3days(get_the_ID()); // 記事閲覧回数表示 */ ?>
                            <?php endforeach;
                        wp_reset_postdata(); ?>
                            </li>
                    </ul>
                <?php else : ?>
                    <p>アクセスランキングはまだ集計されていません。</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
<?php
}

function display_week_ranking()
{
    global $post;
?>
    <!-- ▼　週間ランキング ▼ -->
    <div class="week-ranking">
        <div class="main-wrap">
            <section class="column-inner">
                <?php
                $args = array(
                    'numberposts' => 6, //表示数
                    'meta_key' => 'pv_count_week',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                );

                $posts = get_posts($args);
                if ($posts) : ?>
                    <ul class="parent_box">
                        <?php foreach ($posts as $post) : setup_postdata($post); ?>
                            <li class="child_box">
                                <header class="week-ranking-header">
                                    <div class="week-ranking date-outer"> <time datetime="<?php the_time('Y-m-d'); ?>T<?php the_time('H:i:sP'); ?>">
                                            <span class="week-ranking-date"><?php the_time('Y/m/d'); ?></span>
                                            <span class="week-ranking-time"><?php the_time('H:i'); ?></span>
                                        </time> </div>
                                    <a href="<?php echo get_permalink(); ?>" width: 100px;height: 130px;>
                                        <!--div class="week-ranking masking"-->
                                        <!--<h3 class="week-ranking masktext">-->
                                        <?php  //the_title(); 
                                        ?><!--<span id="likeCount3"></span></h3>
                    </div>-->
                                </header>
                                <div class="week-ranking mosaic-backdrop">
                                    <div class="index_commentbox">
                                        <?php if (function_exists("the_ratings")) the_ratings(); ?></div>
                                    <?php if (has_post_thumbnail()) {
                                        echo '<div class="week-ranking list-thumbnail">';
                                        the_post_thumbnail(array(200, 200), array('class' => 'week-ranking mosaic-backdrop'));
                                    }
                                    echo '</div>';
                                    ?>
                                </div>
                                </a>
                                <div class="week-ranking info topinfo">
                                    <p>
                                        <?php $count = sprintf("%02d", $count); // 一桁を二桁に echo $count + 1; // 01を出力 $count++; 
                                        ?> </p>
                                    <?php echo getPostViewsWeek(get_the_ID()); // 記事閲覧回数表示 */
                                    ?>
                                </div>
                            <?php endforeach;
                        wp_reset_postdata(); ?>
                            </li>
                    </ul>
                <?php else : ?>
                    <p>アクセスランキングはまだ集計されていません。</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
<?php
}

//検索欄
function display_search_form()
{
    global $tn;
?>
    <form method="get" id="searchform" class="searchform" action="<?php echo home_url('/'); ?>">
        <div class="text-form"> <input type="text" placeholder="ブログ内を検索" name="s" id="s" value="" /> <input type="hidden" name="tn" value="<?php echo $tn; ?>"> </div>
        <div class="form-bottom"> <input type="submit" id="searchsubmit" value="Q" /> </div>
    </form>
    <?php
}

//最近のコメント
function display_comment()
{
    $args = array(
        'author__not_in' => '1',
        'number' => '5',
        'status' => 'approve',
        'type' => 'comment'
    );
    $comments_query = new WP_Comment_Query;
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
                $pid = $comment->comment_post_ID;
                // 必要な文字列データの取得
                $url = get_permalink($pid);
                $img = get_the_post_thumbnail($pid, array('class' => 'myClass'));
                $date = get_comment_date('(Y/n/d)', $comment->comment_ID);
                $title = get_the_title($pid);
                $text = get_comment_text($comment->comment_ID);
                $user_id = $comment->comment_author;
                // デフォルト値で初期化して
                $user_id = '名無しさん(anonymous)';

                if (!empty($comment->comment_author)) {
                    $user_id = $comment->comment_author;
                } elseif (!empty($comment->user_id)) {
                    $user_id = $comment->user_id;
                }
            ?>
                <div class="recentcomment">
                    <ul class="mycomment">
                        <li class="imgcomment">
                            <a class="commentheight" href="<?= $url ?>">
                                <?= $img ?>
                            </a>
                            <a class="com_title" href="<?= $url ?>">
                                <?= $title ?>
                            </a>
                            <div class="commentnumber">
                                <p class="comment">
                                    <?= mb_strimwidth($text, 0, 38, "･･･") ?>
                                </p>
                                <p class="my_author">
                                    <?= $date ?>
                                </p><br>
                            </div>
                        </li>
                    </ul>
                </div>
            <?php
            }
            ?>
        </div>
    <?php
    } else {
        echo 'コメントなし';
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
            $title = "<strong><a href=\"{$item->link}\">{$item->title}</a></strong>";
            if (empty($item->img)) {
                $img = 'http://www.last.cfbx.jp/wp-content/uploads/2022/08/9404141699102.jpg';
            } else {
                $img = $item->img;
            }
            $image = "<a href=\"{$item->link}\"><img src=\"{$img}\" width=\"100\"></a>";
            $subject = '<a href="' . $item->link . '">' . mb_substr($item->subject, 0, 10) . '</a>';
            if ($j < $limitSect1) {
                $contentA .= "<li class=\"sitelink\">{$title}</li>"; // タイトルのみ
            } elseif ($j < $limitSect1 + $limitSect2) {
                $contentB .= "<li class=\"sitelink2\"><figure class=\"snip\"><figcaption>{$image}<br>{$title}<p class=\"btn\">{$subject}</p></figcaption></figure></li>"; // 画像と画像の下にタイトル
            } else {
                $contentC .= "<li class=\"sitelink3\">{$image}{$title}</li>"; // 画像と画像の右にタイトル
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
                echo "<figure class=\"entry-thumnail\"><a href=\"{$item->guid}\"><img src=\"{$item->thumbnail}\"></a>({$item->ID})</figure>"; // サムネイル画像
                echo '<div class="entry-card-content">';
                echo '<header class="entry-header">';
                echo "<h2 class=\"entry-title\"><a href=\"{$item->guid}\">{$item->post_title}</a></h2>"; // タイトル
                echo '<p class="post-meta">'; // 日付け、カテゴリー、コメント数
                echo '<span class="fa-clock fa-fw"></span>'; // 日付けのマーク fontawesomeをbeforeで読み込む
                echo "<span class=\"published\">{$item->post_date}</span>"; // 日付け
                echo '<span class="fa-folder fa-fw"></span>'; // カテゴリーのマーク fontawesomeをbeforeで読み込む
                echo '<span class="category-link">';
                if ($item->categories) {
                    foreach ($item->categories as $cat_ID) {
                        $category = $categories[$cat_ID];
                        echo "<a href=\"{$category->category_link}?tn={$tn}\">{$category->cat_name}</a>";
                    }
                }
                echo '</span>'; // カテゴリー
                echo '<span class="fa-comment fa-fw"></span>'; // コメント数のマーク fontawesomeをbeforeで読み込む
                echo "<span class=\"comment-count\"><a href=\"{$item->guid}\">{$item->comments}</a></span>"; // コメント数
                echo '</p>';
                echo '</header>';
                echo "<p class=\"entry-snippet\">{$item->post_excerpt}</p>"; // 抜粋
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
            $title = "<strong><a href=\"{$item->link}\">{$item->title}</a></strong>";
            if (empty($item->img)) {
                $img = 'http://www.last.cfbx.jp/wp-content/uploads/2022/08/9404141699102.jpg';
            } else {
                $img = $item->img;
            }
            $image = "<a href=\"{$item->link}\"><img src=\"{$img}\" width=\"100\"></a>";
            $subject = '<a href="' . $item->link . '">' . mb_substr($item->subject, 0, 10) . '</a>';
            if ($j < $limitSect1) {
                $contentA .= "<li class=\"sitelink\">{$title}</li>"; // タイトルのみ
            } elseif ($j < $limitSect1 + $limitSect2) {
                $contentB .= "<li class=\"sitelink2\"><figure class=\"snip\"><figcaption>{$image}<br>{$title}<p class=\"btn\">{$subject}</p></figcaption></figure></li>"; // 画像と画像の下にタイトル
            } else {
                $contentC .= "<li class=\"sitelink3\">{$image}{$title}</li>"; // 画像と画像の右にタイトル
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
                $images .= "<a href=\"{$item->guid}\"><img src=\"{$item->thumbnail}\"></a>({$item->ID})";
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
            echo "<h2 class=\"entry-Title\"><a href=\"{$item->guid}\">{$item->post_title}</a></h2>"; // タイトル
            echo '<p class="itemInfo">'; // 日付け、カテゴリー、コメント数
            echo '<span class="fa-clock fa-fw"></span>'; // 日付けのマーク fontawesomeをbeforeで読み込む
            echo "<span class=\"itemDate\">{$item->post_date}</span>"; // 日付け
            echo '<span class="fa-folder fa-fw"></span>'; // カテゴリーのマーク fontawesomeをbeforeで読み込む
            echo '<span class="itemCategory">';
            if ($item->categories) {
                foreach ($item->categories as $cat_ID) {
                    $category = $categories[$cat_ID];
                    echo "<a href=\"{$category->category_link}?tn={$tn}\">{$category->cat_name}</a>";
                }
            }
            echo '</span>'; // カテゴリー
            echo '<span class="fa-comment fa-fw"></span>'; // コメント数のマーク fontawesomeをbeforeで読み込む
            echo "<span class=\"clickCnt\"><a href=\"{$item->guid}\">{$item->comments}</a></span>"; // コメント数
            echo '</p>';
            echo '</header>';
            echo "<p class=\"itemSnippet\">{$item->post_excerpt}</p>"; // 抜粋
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
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_keifont.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_keifont.png', '2枚絵比較', 'http://www.irasuto.cfbx.jp/画像2タイトル1/'],
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_jiyunotsubasa.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_TanukiMagic.png', '3枚絵比較', 'http://www.irasuto.cfbx.jp/画像3タイトル1/'],
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_cinecaption227.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_nicomoji-plus_v09.png', '掲示板質問1', 'http://www.irasuto.cfbx.jp/informtion/'],
        ['wp-content/themes/sample_theme//images/banner/freefont_logo_nicokaku_v1.png', 'wp-content/themes/sample_theme//images/banner/freefont_logo_chogokubosogothic5.png', '掲示板質問2', '掲示板質問1', 'http://www.irasuto.cfbx.jp/entry/'],
    ];

    $buf = [];
    foreach ($banner_data as $tmp) {
        list($img1, $img2, $title, $link) = $tmp;
        if (intval(date('H')) >= 12) $img1 = $img2;
        $buf[] = sprintf('<li><a href="%s"><img src="%s" title="%s"></a></li>', $link, $img1, $title);
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
