<?php
/*
Template Name: 画像1タイトル1
Template Post Type: page
*/

if (!defined('ABSPATH')) {
  exit;
}

require_once get_template_directory() . '/display.php';

set_template_info();
?>
<?php get_header(); ?>
<!--ここから自作-->
<?php display_other_template(); ?>
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
    </div>
    <!-- ▼　RSS記事右 ▼ -->
    <div id="right-box">
      <!-- ▼　RSS記事上 ▼ -->
      <!-- ▼　検索欄 ▼ -->
      <!--get_search_form()-->
      <?php display_search_form(); ?>
      <!-- ▼　サブページ1 ▼ -->

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

      $group_per_block = 5;

      //投稿読み込み
      $posts_per_group = $tn;

      $posts_per_page = max(
        1,
        (int) $block_per_page *
          (int) $group_per_block *
          (int) $posts_per_group
      );

      // 投稿総数を先に取得
      $count_query = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => $tk,
        'fields'         => 'ids',
      ]);

      $post_count = max(0, (int) $count_query->found_posts);

      // 最大ページ数
      $pages = max(1, (int) ceil($post_count / $posts_per_page));

      // DB取得前に補正
      $current_page = max(1, min((int) $current_page, $pages));

      //RSS読み込み
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

      //投稿読み込み
      $posts_offset = max(
        0,
        ((int) $current_page - 1) * (int) $posts_per_page
      );

      $args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'offset'         => (int) $posts_offset,
        'posts_per_page' => (int) $posts_per_page,
        'meta_key'       => $tk,
      ];

      $post_items = get_posts($args);

      //表示
      display_rss_post_1();

      //ページリンク
      display_pagenavi(); ?>
    </div>
  </div>
</div>
<!--ここまで-->