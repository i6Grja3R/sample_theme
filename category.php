<?php
if (!defined('ABSPATH')) {
  exit;
}

require_once get_template_directory() . '/display.php';

set_template_info();

if (is_category() && !is_user_logged_in() && !isBot()) {
  category_views_week();
}

get_header();
?>
<!--ここから自作-->
<!-- php display_other_template(); -->
<div id="blog-box" class="clearfix">
  <!-- ブログ開始 -->
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
      <?php display_search_form(); ?>
      <!-- ▼　週間ランキングは category.php では非表示にする -->
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

      $group_per_block = 5; // 1ブロックあたり投稿グループ件数

      // 投稿読み込み
      // $tn を整数に変換して、最低でも1にする
      $posts_per_group = min(4, max(1, (int) $tn));

      $posts_per_page = max(
        1,
        (int) $block_per_page *
          (int) $group_per_block *
          (int) $posts_per_group
      );

      // 現在のカテゴリー情報を取得
      $current_category = get_queried_object();
      $cat_id = 0;

      // 条件式：$current_category->term_id が存在すること
      if ($current_category instanceof WP_Term) {
        $cat_id = (int) $current_category->term_id;
      }

      // 投稿総数を先に取得
      $count_args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
      ];

      if ($cat_id > 0) {
        $count_args['cat'] = $cat_id;
      }

      $count_query = new WP_Query($count_args);

      $post_count = max(0, (int) $count_query->found_posts);
      wp_reset_postdata();

      // 最大ページ数
      $pages = max(1, (int) ceil($post_count / $posts_per_page));

      // DB取得前に現在ページを補正
      $current_page = max(1, min((int) $current_page, $pages));

      // RSS読み込み
      $rss_per_page = max(
        1,
        (int) $block_per_page *
          (int) $rss_per_block
      );

      $rss_offset = max(
        0,
        ((int) $current_page - 1) * (int) $rss_per_page
      );

      $sql = "SELECT * FROM {$rss_table_name} ORDER BY date DESC LIMIT %d,%d";
      $query = $wpdb->prepare($sql, $rss_offset, $rss_per_page);
      $rss_items = $wpdb->get_results($query);

      // 投稿読み込み
      $posts_offset = max(
        0,
        ((int) $current_page - 1) * (int) $posts_per_page
      );

      $args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'offset'         => (int) $posts_offset,
        'posts_per_page' => (int) $posts_per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
      ];

      if ($cat_id > 0) {
        $args['cat'] = $cat_id;
      }

      $post_items = get_posts($args);

      // 表示
      if (1 == $tn) {
        display_rss_post_1();
      } else {
        display_rss_post_2();
      }

      // ページリンク
      display_pagenavi();


      /*
      * 旧SQL版メモ
      * 後で見返すために残す。
      * if (false) の中なので実行されない。
      */
      if (false) {

        // RSS読み込み
        $rss_per_page = $block_per_page * $rss_per_block;
        $rss_offset = ($current_page - 1) * $rss_per_page;

        $sql = "SELECT * FROM {$rss_table_name} ORDER BY date DESC LIMIT %d,%d";
        $query = $wpdb->prepare($sql, $rss_offset, $rss_per_page);
        $rss_items = $wpdb->get_results($query);

        $group_per_block = 5;

        // 投稿読み込み
        $posts_per_group = $tn;
        $posts_per_page = $block_per_page * $group_per_block * $posts_per_group;
        $posts_offset = ($current_page - 1) * $posts_per_page;

        // 検索件数指定取得
        $sql = "
SELECT
post.*
FROM
wp_posts AS post
INNER JOIN wp_term_relationships
ON post.id = wp_term_relationships.object_id
INNER JOIN wp_term_taxonomy
ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
INNER JOIN wp_postmeta AS meta
ON post.ID = meta.post_id
WHERE wp_term_taxonomy.term_id = %d
AND post.post_type = 'post'
AND post.post_status = 'publish'
AND meta.meta_key = %s
ORDER BY
post.post_date DESC
LIMIT %d,%d
";

        $query = $wpdb->prepare($sql, $cat, $tk, $posts_offset, $posts_per_page);
        $post_items = $wpdb->get_results($query);

        // 表示
        if (1 == $tn) {
          display_rss_post_1();
        } else {
          display_rss_post_2();
        }

        // ページリンク用COUNT
        $sql = "
SELECT
COUNT(*) AS count
FROM
wp_posts AS post
INNER JOIN wp_term_relationships
ON post.ID = wp_term_relationships.object_id
INNER JOIN wp_term_taxonomy
ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
INNER JOIN wp_postmeta AS meta
ON post.ID = meta.post_id
WHERE wp_term_taxonomy.term_id = %d
AND post.post_type = 'post'
AND post.post_status = 'publish'
AND meta.meta_key = %s
";

        $query = $wpdb->prepare($sql, $cat, $tk);
        $results = $wpdb->get_results($query);
        $post_count = $results[0]->count;

        display_pagenavi();
      }
      ?>
    </div><!-- /right-box -->
  </div><!-- /main-box -->
</div><!-- /blog-box -->
<!--ここまで-->
<?php get_footer(); ?>