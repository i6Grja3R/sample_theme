<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <?php
        // 固定ページのアイキャッチ（アイキャッチを使う設定が必要）
        $attachment_id = (int) get_post_thumbnail_id(get_the_ID());

        // フォールバック画像（テーマ内の画像に合わせて修正）
        $fallback = get_stylesheet_directory_uri() . '/assets/img/common/no-img.jpg';

        // 安全版ユーティリティで出力
        echo bbs_safe_attachment_img_or_fallback(
            $attachment_id,
            'rect',                               // 登録済みの画像サイズ名
            ['class' => 'page-thumbnail', 'loading' => 'lazy'],
            $fallback
        );
        ?>

        <h1><?php the_title(); ?></h1>
        <div class="page-content">
            <?php the_content(); ?>
        </div>

<?php endwhile;
endif; ?>