<?php
if (!defined('ABSPATH')) {
  exit;
}

$y = (int) get_query_var('year');
$m = (int) get_query_var('monthnum');

require_once get_template_directory() . '/display.php';

set_template_info();
?>

<?php get_header(); ?>
<?php // display_other_template(); 
?>
<!--ここから自作-->
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
      $block_per_page = 2; // 前半5件＋後半5件
      $limitSect1 = 5; // タイトルのみのRSS件数
      $limitSect2 = 4; // 画像と画像の下にタイトルのRSS件数
      $limitSect3 = 4; // 画像と画像の右にタイトルのRSS件数
      $rss_per_block = $limitSect1 + $limitSect2 + $limitSect3;

      $group_per_block = 5; // 1ブロックあたり投稿グループ件数

      // 投稿読み込み
      // $tn を整数に変換して、最低でも1、最大4にする
      $posts_per_group = min(4, max(1, (int) $tn));

      $posts_per_page = max(
        1,
        (int) $block_per_page *
          (int) $group_per_block *
          (int) $posts_per_group
      );

      // 投稿総数を先に取得
      $count_args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => $tk,
        'fields'         => 'ids',
        'year'           => (int) $y,
        'monthnum'       => (int) $m,
      ];

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
        'meta_key'       => $tk,
        'year'           => (int) $y,
        'monthnum'       => (int) $m,
        'orderby'        => 'date',
        'order'          => 'DESC',
      ];

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

        /* 検索件数指定取得 */
        $args = [
          'posts_per_page' => $posts_per_page,
          'offset'         => $posts_offset,
          'meta_key'       => $tk,
          'year'           => $y,
          'monthnum'       => $m,
        ];

        $post_items = get_posts($args);

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
INNER JOIN wp_postmeta AS meta
ON post.ID = meta.post_id
WHERE
meta.meta_key = %s
AND post.post_type = 'post'
AND post.post_status = 'publish'
AND YEAR(post.post_date) = %d
AND MONTH(post.post_date) = %d
";

        $query = $wpdb->prepare($sql, $tk, $y, $m);
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