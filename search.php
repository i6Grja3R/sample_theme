<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/display.php';

set_template_info();
?>
<?php get_header(); ?>
<!--ここから自作-->
<?php // display_other_template(); 
?>
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
        </div><!-- /left-box -->
        <!-- ▼　RSS記事右 ▼ -->
        <div id="right-box">
            <!-- ▼　RSS記事上 ▼ -->
            <!-- ▼　検索欄 ▼ -->
            <!--get_search_form()-->
            <?php display_search_form(); ?>
            <!-- ▼　週間ランキング ▼ -->
            <?php // display_week_ranking(); 
            ?>
            <!-- ▼　メインページ ▼ -->
            <?php
            /*echo __FILE__;
echo __DIR__;*/
            $categories = get_categories_array();
            $rss_table_name = get_rss_table_name($tn);
            /*var_dump($rss_table_name);*/

            //表示設定
            //ページ番号チェック
            $block_per_page = 2; //ページあたりブロック件数
            $limitSect1 = 5; // タイトルのみの件数
            $limitSect2 = 4; // 画像と画像の下にタイトルの件数
            $limitSect3 = 4; // 画像と画像の右にタイトルの件数
            $rss_per_block = $limitSect1 + $limitSect2 + $limitSect3; // ブロックあたりRSS件数
            //RSS読み込み
            $rss_per_page = $block_per_page * $rss_per_block;
            $rss_offset = ($current_page - 1) * $rss_per_page;
            //※テーブル名の変更
            $sql = "SELECT * FROM {$rss_table_name} ORDER BY date DESC LIMIT %d,%d";
            $query = $wpdb->prepare($sql, $rss_offset, $rss_per_page);
            //SQL分実行と結果取得
            $rss_items = $wpdb->get_results($query);
            $group_per_block = 5; //ブロックあたり投稿グループ件数
            //投稿読み込み
            $posts_per_group = min(4, max(1, (int) $tn)); // 投稿グループあたり投稿件数
            $posts_per_page = $block_per_page * $group_per_block * $posts_per_group; // ページあたり投稿件数
            $posts_offset = ($current_page - 1) * $posts_per_page; //投稿オフセット
            //ページリンク
            // wp_unslash(): WordPressが勝手に付けた余計なスラッシュを綺麗に取り除き、ユーザーが入力した「生の文字」に戻します。
            // sanitize_text_field(): 文字列からHTMLタグや不正な文字（無効なUTF-8など）を完全に除去し、プレーンな「ただのテキスト」に安全化してくれます。
            $search_query = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
            // 検索文字を100文字に制限（mb_substr）し、前後の不要なスペースを削る（trim）処理を入れているのは、負荷対策・セキュリティ対策としてめちゃくちゃ満点です。
            $search_query = trim(mb_substr($search_query, 0, 100));

            $search_items = [];

            if ($search_query !== '' && !preg_match("/[\.,:;]/u", $search_query)) {

                $sql = "
SELECT
distinct
post.*
FROM
{$wpdb->posts} AS post
INNER JOIN {$wpdb->postmeta} AS meta
ON post.ID = meta.post_id
WHERE
meta.meta_key = %s
AND post.post_type = 'post'
AND post.post_status = 'publish'
AND post.post_title LIKE %s
ORDER BY
post.post_date DESC
";
                $like = '%' . $wpdb->esc_like($search_query) . '%';
                $query = $wpdb->prepare($sql, $tk, $like);
                $search_items = $wpdb->get_results($query);
            }
            $stack_items = [];

            if (1 == $tn) {
                $stack_items = $search_items;
            } elseif (2 == $tn) {
                $post_ids = [];
                $stack_items = [];
                foreach ($search_items as $post) {

                    // 初期化
                    $post_white = null;
                    $post_black = null;

                    // ID比較なので、型まで見る true を付けた方が安全
                    if (in_array($post->ID, $post_ids, true)) {
                        continue;
                    }
                    $team = get_post_meta($post->ID, 'team', true);
                    if ('white' === $team) {
                        $post_white = $post; /* 白（現在） */
                        $post_black = get_adjacent_post(true, '', true); /* 黒（現在の前） */
                    } elseif ('black' === $team) {
                        $post_black = $post; /* 黒（現在） */
                        $post_white = get_adjacent_post(true, '', false); /* 白（現在の後） */
                    }

                    // $stack_items[] に入れる前にチェック
                    if (!$post_white instanceof WP_Post || !$post_black instanceof WP_Post) {
                        continue;
                    }

                    $post_ids[] = $post_white->ID;
                    $post_ids[] = $post_black->ID;
                    $stack_items[] = $post_white;
                    $stack_items[] = $post_black;
                }
            } elseif (3 == $tn) {
                $post_ids = [];
                $stack_items = [];
                foreach ($search_items as $post) {

                    // 初期化
                    $post_red = null;
                    $post_blue = null;
                    $post_green = null;

                    // ID比較なので、型まで見る true を付けた方が安全
                    if (in_array($post->ID, $post_ids, true)) {
                        continue;
                    }
                    $team = get_post_meta($post->ID, 'team', true);
                    if ('red' === $team) {
                        $post_red = $post; /* 赤（現在） */
                        $post_blue = get_adjacent_post(true, '', true); /* 青（現在の次） */
                        $post = $post_blue; /* 現在を青に置きかえる */
                        $post_green = get_adjacent_post(true, '', true); /* 緑（現在の次：青の次） */
                    } elseif ('blue' === $team) {
                        $post_blue = $post; /* 青（現在） */
                        $post_red = get_adjacent_post(true, '', false); /* 赤（現在の前） */
                        $post_green = get_adjacent_post(true, '', true); /* 緑（現在の次） */
                    } elseif ('green' === $team) {
                        $post_green = $post; /* 緑（現在） */
                        $post_blue = get_adjacent_post(true, '', false); /* 青（現在の前） */
                        $post = $post_blue; /* 現在を青に置きかえる */
                        $post_red = get_adjacent_post(true, '', false); /* 赤（現在の前：青の前） */

                        if ($post_blue instanceof WP_Post) {
                            $post = $post_blue;
                            $post_red = get_adjacent_post(true, '', false);
                        }
                    }

                    // $stack_items[] に入れる前にチェック
                    if (
                        !$post_red instanceof WP_Post ||
                        !$post_blue instanceof WP_Post ||
                        !$post_green instanceof WP_Post
                    ) {
                        continue;
                    }

                    $post_ids[] = $post_red->ID;
                    $post_ids[] = $post_blue->ID;
                    $post_ids[] = $post_green->ID;
                    $stack_items[] = $post_red;
                    $stack_items[] = $post_blue;
                    $stack_items[] = $post_green;
                }
            } else {
                $stack_items = $search_items;
            }
            // 投稿総数
            $post_count = count($stack_items);

            // 最大ページ数
            $pages = max(1, (int) ceil($post_count / $posts_per_page));

            // 現在ページを補正
            $current_page = max(1, min((int) $current_page, $pages));

            // 補正後のページ番号で offset を再計算
            $posts_offset = max(
                0,
                ((int) $current_page - 1) * (int) $posts_per_page
            );

            // 表示する投稿だけ切り出す
            $post_items = array_slice($stack_items, $posts_offset, $posts_per_page);

            // 表示
            if (1 == $tn) {
                display_rss_post_1();
            } else {
                display_rss_post_2();
            }

            // ページリンク
            display_pagenavi();
            ?>
        </div><!-- /right-box -->
    </div><!-- /main-box -->
</div><!-- /blog-box -->
<!--ここまで-->
<?php get_footer(); ?>