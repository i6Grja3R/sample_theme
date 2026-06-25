<?php
/*
Template Name: bbs_que_answer_tree_lazy
固定ページ: 回答画面（ツリー構造＋Lazy Load）
*/

if (!defined('ABSPATH')) {
    exit;
}

header('X-FRAME-OPTIONS: SAMEORIGIN');
get_header();

global $wpdb;
$table = $wpdb->prefix . 'sortable';

// URL末尾のUUIDを取得。できれば ?id=uuid 形式に変更するとさらに安全。
$unique_id = '';
if (isset($_GET['id'])) {
    $unique_id = sanitize_text_field(wp_unslash($_GET['id']));
} else {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $unique_id = substr($path, -36);
    $unique_id = sanitize_text_field($unique_id);
}

$thread = function_exists('bbs_tree_get_thread_by_unique_id')
    ? bbs_tree_get_thread_by_unique_id($unique_id)
    : $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE unique_id = %s AND (parent_id IS NULL OR parent_id = 0) LIMIT 1", $unique_id));

if (!$thread) {
    echo '<main class="main_container"><p>スレが見つかりません。</p></main>';
    get_footer();
    exit;
}

$upload_dir = wp_upload_dir();
$attach_base_url = trailingslashit($upload_dir['baseurl']) . 'attach/';
$noimage_url = trailingslashit(get_template_directory_uri()) . 'images/noimage.png';
$nonce = wp_create_nonce('bbs_tree_nonce');
$is_archived = function_exists('bbs_tree_is_archived') ? bbs_tree_is_archived($thread) : false;

function bbs_tree_safe_img_url(string $file, string $base_url): string
{
    $file = basename($file);
    if (!preg_match('/\A[a-zA-Z0-9._-]+\z/', $file)) {
        return '';
    }
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        return '';
    }
    return esc_url($base_url . $file);
}
?>

<style>
    .bbs-thread {
        max-width: 980px;
        margin: 0 auto;
    }

    .bbs-question {
        padding: 18px;
        background: #fff;
        border: 1px solid #ddd;
    }

    .bbs-question__title {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .bbs-question__text {
        white-space: pre-wrap;
        line-height: 1.8;
    }

    .bbs-question__media {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin: 16px 0;
    }

    .bbs-question__media img {
        width: 535px;
        max-width: 100%;
        height: 301px;
        object-fit: contain;
        background: #fafafa;
    }

    .bbs-question__user {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
    }

    .bbs-question__user img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 50%;
    }

    .bbs-archive-notice {
        padding: 12px;
        background: #f5f5f5;
        border: 1px solid #ddd;
        margin: 16px 0;
    }

    .bbs-answer-form {
        margin: 24px 0;
        padding: 16px;
        background: #FCF2F4;
        border: 2px solid #F7859C;
    }

    .bbs-answer-form textarea {
        width: 100%;
        min-height: 150px;
        padding: 10px;
        box-sizing: border-box;
    }

    .bbs-answer-form input[type="text"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        margin-top: 8px;
    }

    .bbs-answer-form button {
        margin-top: 10px;
    }

    .bbs-comments {
        margin-top: 24px;
    }

    .bbs-comment {
        margin-left: calc(var(--depth-indent, 0) * 22px);
        padding: 12px;
        border-left: 3px solid #ddd;
        background: #fff;
        margin-top: 12px;
    }

    .bbs-comment__meta {
        font-size: 13px;
        color: #666;
        display: flex;
        gap: 8px;
    }

    .bbs-comment__name {
        font-weight: 700;
        color: #333;
    }

    .bbs-comment__body {
        margin-top: 8px;
        line-height: 1.7;
        white-space: normal;
        word-break: break-word;
    }

    .bbs-comment__actions {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .bbs-children {
        margin-top: 8px;
    }

    .bbs-load-more-wrap {
        text-align: center;
        margin: 20px 0;
    }

    .bbs-loading {
        opacity: .65;
        pointer-events: none;
    }
</style>

<main class="bbs-thread" id="bbs_thread" data-thread-unique-id="<?php echo esc_attr($unique_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
    <section class="bbs-question">
        <h1 class="bbs-question__title"><?php echo esc_html((string) $thread->title); ?></h1>

        <div class="bbs-question__media">
            <?php foreach ([$thread->attach1 ?? '', $thread->attach2 ?? '', $thread->attach3 ?? ''] as $file) : ?>
                <?php $url = $file ? bbs_tree_safe_img_url((string) $file, $attach_base_url) : ''; ?>
                <?php if ($url) : ?><img src="<?php echo $url; ?>" alt=""><?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="bbs-question__text"><?php echo nl2br(esc_html((string) $thread->text)); ?></div>

        <div class="bbs-question__user">
            <?php $icon_url = !empty($thread->usericon) ? bbs_tree_safe_img_url((string) $thread->usericon, $attach_base_url) : ''; ?>
            <img src="<?php echo esc_url($icon_url ?: $noimage_url); ?>" alt="">
            <span><?php echo esc_html($thread->name ?: '匿名'); ?></span>
        </div>
    </section>

    <?php if ($is_archived) : ?>
        <div class="bbs-archive-notice">このスレは作成から3ヶ月以上経過したため、アーカイブ済みです。新規投稿はできません。</div>
    <?php else : ?>
        <section class="bbs-answer-form" id="bbs_answer_form">
            <h2>回答する</h2>
            <textarea id="bbs_reply_text" maxlength="<?php echo esc_attr(MAX_LENGTH::TEXT); ?>" minlength="<?php echo esc_attr(MIN_LENGTH::TEXT); ?>" placeholder="荒らし行為や誹謗中傷や著作権の侵害はご遠慮ください"></textarea>
            <input type="text" id="bbs_reply_name" maxlength="<?php echo esc_attr(MAX_LENGTH::NAME); ?>" placeholder="未入力の場合は匿名で表示されます">
            <input type="hidden" id="bbs_reply_parent_id" value="0">
            <button type="button" id="bbs_submit_reply">投稿する</button>
        </section>
    <?php endif; ?>

    <section class="bbs-comments">
        <h2>回答一覧</h2>
        <div id="bbs_answer_list"></div>
        <div class="bbs-load-more-wrap">
            <button type="button" id="bbs_load_more_answers" data-cursor="0">回答を読み込む</button>
        </div>
    </section>
</main>

<script>
    (() => {
        const root = document.getElementById('bbs_thread');
        if (!root) return;

        const ajaxUrl = root.dataset.ajaxUrl;
        const nonce = root.dataset.nonce;
        const threadUniqueId = root.dataset.threadUniqueId;
        const answerList = document.getElementById('bbs_answer_list');
        const loadMoreBtn = document.getElementById('bbs_load_more_answers');

        const postAjax = async (action, params) => {
            const fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', nonce);
            Object.entries(params || {}).forEach(([k, v]) => fd.append(k, v));

            const res = await fetch(ajaxUrl, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            });
            const json = await res.json();
            if (!json || json.success !== true) {
                throw new Error(json?.data?.message || '通信に失敗しました。');
            }
            return json.data;
        };

        const loadAnswers = async () => {
            if (!loadMoreBtn || loadMoreBtn.classList.contains('bbs-loading')) return;
            loadMoreBtn.classList.add('bbs-loading');
            try {
                const data = await postAjax('bbs_load_answers', {
                    thread_unique_id: threadUniqueId,
                    cursor: loadMoreBtn.dataset.cursor || '0',
                    limit: '20'
                });
                answerList.insertAdjacentHTML('beforeend', data.html || '');
                loadMoreBtn.dataset.cursor = String(data.next_cursor || 0);
                loadMoreBtn.style.display = data.has_more ? '' : 'none';
            } catch (e) {
                alert(e.message);
            } finally {
                loadMoreBtn.classList.remove('bbs-loading');
            }
        };

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadAnswers);
            loadAnswers();

            // 回答は20件ごとの無限スクロール
            const io = new IntersectionObserver((entries) => {
                if (entries.some(e => e.isIntersecting) && loadMoreBtn.style.display !== 'none') {
                    loadAnswers();
                }
            }, {
                rootMargin: '300px'
            });
            io.observe(loadMoreBtn);
        }

        document.addEventListener('click', async (e) => {
            const replyLoad = e.target.closest('.bbs-load-replies');
            if (replyLoad) {
                const parentId = replyLoad.dataset.parentId;
                const cursor = replyLoad.dataset.cursor || '0';
                const container = document.querySelector(`.bbs-children[data-parent-id="${CSS.escape(parentId)}"]`);
                replyLoad.classList.add('bbs-loading');
                try {
                    const data = await postAjax('bbs_load_replies', {
                        parent_id: parentId,
                        cursor,
                        limit: '10'
                    });
                    if (container) container.insertAdjacentHTML('beforeend', data.html || '');
                    replyLoad.dataset.cursor = String(data.next_cursor || 0);
                    replyLoad.textContent = data.has_more ? 'さらに返信を読む' : '返信を読み込み済み';
                    if (!data.has_more) replyLoad.disabled = true;
                } catch (err) {
                    alert(err.message);
                } finally {
                    replyLoad.classList.remove('bbs-loading');
                }
                return;
            }

            const replyOpen = e.target.closest('.bbs-reply-open');
            if (replyOpen) {
                const parentId = replyOpen.dataset.parentId;
                const slot = document.querySelector(`.bbs-reply-form-slot[data-parent-id="${CSS.escape(parentId)}"]`);
                if (!slot) return;
                document.getElementById('bbs_reply_parent_id').value = parentId;
                document.getElementById('bbs_reply_text').focus();
                slot.textContent = 'この投稿に返信します。下の入力欄から投稿してください。';
            }
        });

        const submitBtn = document.getElementById('bbs_submit_reply');
        if (submitBtn) {
            submitBtn.addEventListener('click', async () => {
                const textEl = document.getElementById('bbs_reply_text');
                const nameEl = document.getElementById('bbs_reply_name');
                const parentEl = document.getElementById('bbs_reply_parent_id');
                const text = textEl.value.trim();
                if (!text) {
                    alert('本文を入力してください。');
                    return;
                }
                submitBtn.disabled = true;
                try {
                    const data = await postAjax('bbs_tree_post_reply', {
                        thread_unique_id: threadUniqueId,
                        parent_id: parentEl.value,
                        text,
                        name: nameEl.value.trim()
                    });
                    const parentId = parentEl.value;
                    const isAnswer = parentId === '0';
                    if (isAnswer) {
                        answerList.insertAdjacentHTML('beforeend', data.html || '');
                    } else {
                        const container = document.querySelector(`.bbs-children[data-parent-id="${CSS.escape(parentId)}"]`);
                        if (container) container.insertAdjacentHTML('beforeend', data.html || '');
                    }
                    textEl.value = '';
                    parentEl.value = '0';
                } catch (err) {
                    alert(err.message);
                } finally {
                    submitBtn.disabled = false;
                }
            });
        }
    })();
</script>

<?php get_footer(); ?>