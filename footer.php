<footer class="footer">

    <div class="footer-inner">

        <!-- ロゴ -->
        <div class="footer-logo">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/footer-logo.png"
                    alt="<?php bloginfo('name'); ?>">
            </a>
        </div>

        <!-- SNS -->
        <div class="footer-sns">

            <a href="https://x.com/あなたのID"
                target="_blank"
                rel="nofollow noopener noreferrer"
                class="footer-x">

                <svg class="footer-icon" viewBox="0 0 1200 1227" xmlns="http://www.w3.org/2000/svg">
                    <path d="m714.163 519.284 446.727-519.284h-105.86l-387.893 450.887-309.809-450.887h-357.328l468.492 681.821-468.492 544.549h105.866l409.625-476.152 327.181 476.152h357.328l-485.863-707.086zm-144.998 168.544-47.468-67.894-377.686-540.2396h162.604l304.797 435.9906 47.468 67.894 396.2 566.721h-162.604l-323.311-462.446z" />
                </svg>

                フォローする
            </a>

            <a href="<?php bloginfo('rss2_url'); ?>"
                target="_blank"
                class="footer-rss">

                <svg class="footer-icon" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg">
                    <path d="M128.081 415.959c0 35.369-28.672 64.041-64.041 64.041S0 451.328 0 415.959s28.672-64.041 64.041-64.041 64.04 28.673 64.04 64.041zm175.66 47.25c-8.354-154.6-132.185-278.587-286.95-286.95C7.656 175.765 0 183.105 0 192.253v48.069c0 8.415 6.49 15.472 14.887 16.018 111.832 7.284 201.473 96.702 208.772 208.772.547 8.397 7.604 14.887 16.018 14.887h48.069c9.149.001 16.489-7.655 15.995-16.79zm144.249.288C439.596 229.677 251.465 40.445 16.503 32.01 7.473 31.686 0 38.981 0 48.016v48.068c0 8.625 6.835 15.645 15.453 15.999 191.179 7.839 344.627 161.316 352.465 352.465.353 8.618 7.373 15.453 15.999 15.453h48.068c9.034-.001 16.329-7.474 16.005-16.504z" />
                </svg>

                RSS
            </a>

        </div>

        <!-- フッターメニュー -->
        <nav class="footer-menu">
            <a href="<?php echo esc_url(home_url('/about/')); ?>">お絵描き民について</a>
            <!-- <a href="/about/">利用規約</a> -->
            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">プライバシーポリシー</a>
            <a href="<?php echo esc_url(home_url('/help/')); ?>">免責事項</a>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>">お問い合わせ</a>
        </nav>

        <!-- コピーライト -->
        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>
        </div>

    </div>

</footer>
<?php // JSファイルがあればここで読み込む 
?>
<!-- <script src="<?php echo get_template_directory_uri(); ?>/assets/js/your-script.js"></script> -->
</div>

<script>
    document.addEventListener('click', function(e) {

        const button = e.target.closest('.comment-reaction-button');

        if (!button) {
            return;
        }

        const formData = new FormData();

        formData.append('action', 'comment_reaction');
        formData.append('nonce', comment_reaction_vars.nonce);
        formData.append('comment_id', button.dataset.commentId);
        formData.append('reaction', button.dataset.reaction);

        fetch(comment_reaction_vars.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {

                if (!data.success) {
                    return;
                }

                const row = button.closest('.comment-action-row');

                row.querySelector('.comment-good-count').textContent = data.data.good;
                row.querySelector('.comment-bad-count').textContent = data.data.bad;
            });
    });
</script>

<?php wp_footer(); ?>
</body>

</html>