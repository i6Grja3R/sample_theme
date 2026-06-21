<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<!--ヘッダー背景画像-->

<body class="post-template" itemscope="" itemtype="https://schema.org/WebPage">
  <div class="wrapper">
    <header id="header" itemscope="" itemtype="https://schema.org/WPHeader">
      <ul class="header-box" itemscope="" itemtype="https://schema.org/SiteNavigationElement">
        <li>
          <!-- ▼　トップ画像を変更して、あなたのブログにオリジナリティーを出してみましょう ▼ --><a href="http://www.anige-sokuhouvip.com/">
            <img src="http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/main-banner.png" alt=""></a>
          <!-- ▲　画像の推奨サイズは "1100px×200px" となっております。 横幅だけでも合わせておきましょう ▲ -->
          <!--▼ メニューバー ▼-->
          <ul id="menu_bar">
            <li><a href="">サイト(ABOUT)</a></li>
            <li><a href="" target="_blank">問い合わせ(Q)</a></li>
            <li><a href="">twitter</a></li>
            <li><a href="" target="_blank">オワタあんてな</a></li>
            <li><a href="" target="_blank">レート予想</a></li>
          </ul>
        </li>
        <li class="header-rank-area">
          <div id="modernentryrank" class="infoslide warm">
            <ul>
              <!--記事をランダム表示-->
              <?php $args = array(
                'orderby' => 'rand',                 //ソートをランダムに指定
                'showposts' => 4
              );
              $query = new WP_Query($args);

              if ($query->have_posts()):
                while ($query->have_posts()) : $query->the_post();
              ?>
                  <!--繰り返し表示させる部分-->
                  <li class="custom" style="margin:1px;padding:0px;">
                    <a href="<?php echo get_permalink(); ?>" class="header-rank-link">
                      <div class="img-wrap">
                        <!--画像を追加-->
                        <?php
                        if (has_post_thumbnail()) {
                          the_post_thumbnail();
                        } else {
                          echo '<img src="noimage.jpg">';
                        } ?>
                      </div>
                      <!--リンククラス付きのコメント数を追加-->
                      <?php $num_comments = get_comments_number();
                      if ($num_comments == 0) {
                        $comments = ('No Comments'); // 댓글이 없을 경우 } elseif ( $num_comments > 1 ) { $comments = $num_comments . (' Comments'); // 댓글이 2개 이상일 경우
                      } else {
                        $comments = __('1 Comment'); // 댓글이 1개일 경우
                      }
                      $write_comments = '<span class="singlecomments"><a href="' . get_comments_link() . '"                                         class="count comment">' . $comments . '</a></span>';
                      echo $write_comments;
                      ?>
                      <div class="titlewrap"> <span class="title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></span></div>
                    </a>
                  </li>
              <?php endwhile;
              endif; ?>
            </ul>
          </div>
        </li>
      </ul>
    </header>
  </div>
  <!--ここまで-->