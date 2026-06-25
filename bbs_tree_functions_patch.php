<?php

/**
 * BBS tree reply patch
 * 目的:
 * - 隣接リスト方式(parent_id)で回答・返信を保存
 * - 1スレ最大1000件（質問1 + 回答/返信999）
 * - 最大32階層
 * - 3ヶ月経過したスレは書き込み不可・アーカイブ扱い
 * - 回答20件ごと、返信10件ごとのLazy Load
 *
 * functions.php の末尾、または inc/bbs-tree.php として読み込んでください。
 */

if (!defined('ABSPATH')) {
    exit;
}

const BBS_THREAD_MAX_POSTS = 1000;
const BBS_TREE_MAX_DEPTH   = 32;
const BBS_ANSWER_PAGE_SIZE = 20;
const BBS_REPLY_PAGE_SIZE  = 10;
const BBS_ARCHIVE_DAYS     = 90;

/** Cloudflare利用時も考慮したIP取得。 */
function bbs_tree_get_client_ip(): string
{
    $candidates = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    ];

    foreach ($candidates as $key) {
        if (empty($_SERVER[$key])) {
            continue;
        }
        $value = (string) $_SERVER[$key];
        if ($key === 'HTTP_X_FORWARDED_FOR') {
            $value = trim(explode(',', $value)[0]);
        }
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return $value;
        }
    }
    return '';
}

function bbs_tree_questions_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'bbs_questions';
}

function bbs_tree_posts_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'bbs_posts';
}

/** unique_id から質問スレ本体を取得。 */
function bbs_tree_get_thread_by_unique_id(string $unique_id): ?object
{
    global $wpdb;
    $table = bbs_tree_questions_table();
    $unique_id = sanitize_text_field($unique_id);

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE unique_id = %s AND is_confirmed = 1 AND status <> 'deleted' LIMIT 1",
            $unique_id
        )
    );

    return $row ?: null;
}

/** スレ作成から90日を超えているか。 */
function bbs_tree_is_archived(object $thread): bool
{
    if (!empty($thread->status) && $thread->status === 'archived') {
        return true;
    }

    $created = $thread->created_at ?? $thread->created ?? $thread->updated_at ?? null;
    if (!$created) {
        return false;
    }

    return strtotime((string) $created) < strtotime('-' . BBS_ARCHIVE_DAYS . ' days');
}

/** 親投稿のdepthを取得して、次の投稿depthを計算。 */
function bbs_tree_next_depth(int $parent_post_id): int
{
    global $wpdb;
    if ($parent_post_id <= 0) {
        return 1;
    }
    $table = bbs_tree_posts_table();
    $parent_depth = (int) $wpdb->get_var($wpdb->prepare("SELECT depth FROM {$table} WHERE id = %d", $parent_post_id));
    return $parent_depth + 1;
}

/** スレ内投稿数。質問本体も含む。 */
function bbs_tree_count_thread_posts(int $question_id): int
{
    global $wpdb;
    $table = bbs_tree_posts_table();
    $post_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE question_id = %d AND is_confirmed = 1 AND status <> 'deleted'",
            $question_id
        )
    );

    return 1 + $post_count;
}

/** 返信可能かまとめて判定。 */
function bbs_tree_can_post(int $question_id, int $parent_post_id): array
{
    global $wpdb;

    $questions_table = bbs_tree_questions_table();
    $posts_table     = bbs_tree_posts_table();

    $thread = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$questions_table} WHERE id = %d LIMIT 1",
            $question_id
        )
    );

    if (!$thread) {
        return [false, '親スレが見つかりません。'];
    }

    if (bbs_tree_is_archived($thread)) {
        return [false, 'このスレはアーカイブ済みのため投稿できません。'];
    }

    $count = bbs_tree_count_thread_posts($question_id);
    if ($count >= BBS_THREAD_MAX_POSTS) {
        return [false, 'このスレは投稿上限に達しました。'];
    }

    if ($parent_post_id > 0) {
        $parent = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT *
                 FROM {$posts_table}
                 WHERE id = %d
                   AND question_id = %d
                 LIMIT 1",
                $parent_post_id,
                $question_id
            )
        );

        if (!$parent) {
            return [false, '返信先が不正です。'];
        }
    }

    $depth = bbs_tree_next_depth($parent_post_id);
    if ($depth > BBS_TREE_MAX_DEPTH) {
        return [false, '返信階層の上限に達しました。'];
    }

    return [true, ''];
}

/** コメントHTML生成。削除済みでもID関係は残す。 */
function bbs_tree_render_comment_html(object $row): string
{
    $depth = max(0, min((int) ($row->depth ?? 0), BBS_TREE_MAX_DEPTH));
    $indent_level = min($depth, 5); // 5階層まで横にずらし、6階層以降は固定
    $is_deleted = !empty($row->status) && $row->status === 'deleted';

    $id   = (int) $row->id;
    $name = $is_deleted ? '削除済み' : esc_html($row->name ?: '匿名');
    $text = $is_deleted ? 'この投稿は削除されました。' : nl2br(esc_html((string) $row->text));
    $reply_count = (int) ($row->replies_count ?? 0);

    ob_start();
?>
    <article class="bbs-comment" data-comment-id="<?php echo esc_attr($id); ?>" data-depth="<?php echo esc_attr($depth); ?>" style="--depth-indent: <?php echo esc_attr($indent_level); ?>;">
        <div class="bbs-comment__meta">
            <span class="bbs-comment__no">#<?php echo esc_html($id); ?></span>
            <span class="bbs-comment__name"><?php echo $name; ?></span>
        </div>
        <div class="bbs-comment__body"><?php echo $text; ?></div>
        <div class="bbs-comment__actions">
            <button type="button" class="bbs-reply-open" data-parent-id="<?php echo esc_attr($id); ?>">返信する</button>
            <?php if ($reply_count > 0) : ?>
                <button type="button" class="bbs-load-replies" data-parent-id="<?php echo esc_attr($id); ?>" data-cursor="0">
                    返信を読む（<?php echo esc_html($reply_count); ?>）
                </button>
            <?php endif; ?>
        </div>
        <div class="bbs-reply-form-slot" data-parent-id="<?php echo esc_attr($id); ?>"></div>
        <div class="bbs-children" data-parent-id="<?php echo esc_attr($id); ?>"></div>
    </article>
<?php
    return (string) ob_get_clean();
}

/** 回答20件読み込み。parent_id=root_id の直接回答だけ。 */
/** 回答20件読み込み。直接回答だけ。 */
function bbs_ajax_load_answers(): void
{
    check_ajax_referer('bbs_tree_nonce', 'nonce');
    global $wpdb;

    $table = bbs_tree_posts_table();

    $thread_unique_id = sanitize_text_field($_POST['thread_unique_id'] ?? '');
    $cursor = max(0, (int) ($_POST['cursor'] ?? 0));
    $limit = min(BBS_ANSWER_PAGE_SIZE, max(1, (int) ($_POST['limit'] ?? BBS_ANSWER_PAGE_SIZE)));

    $thread = bbs_tree_get_thread_by_unique_id($thread_unique_id);
    if (!$thread) {
        wp_send_json_error(['message' => 'スレが見つかりません。']);
    }

    $question_id = (int) $thread->id;

    $where_cursor = $cursor > 0 ? $wpdb->prepare('AND id > %d', $cursor) : '';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
             FROM {$table}
             WHERE question_id = %d
               AND parent_post_id IS NULL
               AND is_confirmed = 1
               {$where_cursor}
             ORDER BY id ASC
             LIMIT %d",
            $question_id,
            $limit + 1
        )
    );

    $has_more = count($rows) > $limit;
    $rows = array_slice($rows, 0, $limit);

    $html = implode('', array_map('bbs_tree_render_comment_html', $rows));
    $next_cursor = $rows ? (int) end($rows)->id : $cursor;

    wp_send_json_success([
        'html'        => $html,
        'next_cursor' => $next_cursor,
        'has_more'   => $has_more,
    ]);
}
add_action('wp_ajax_bbs_load_answers', 'bbs_ajax_load_answers');
add_action('wp_ajax_nopriv_bbs_load_answers', 'bbs_ajax_load_answers');

/** 返信10件読み込み。直接返信だけをLazy Load。 */
/** 返信10件読み込み。直接返信だけをLazy Load。 */
function bbs_ajax_load_replies(): void
{
    check_ajax_referer('bbs_tree_nonce', 'nonce');
    global $wpdb;

    $table = bbs_tree_posts_table();

    $parent_post_id = max(0, (int) ($_POST['parent_id'] ?? 0));
    $cursor = max(0, (int) ($_POST['cursor'] ?? 0));
    $limit = min(BBS_REPLY_PAGE_SIZE, max(1, (int) ($_POST['limit'] ?? BBS_REPLY_PAGE_SIZE)));

    if ($parent_post_id <= 0) {
        wp_send_json_error(['message' => '親IDが不正です。']);
    }

    $where_cursor = $cursor > 0 ? $wpdb->prepare('AND id > %d', $cursor) : '';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
             FROM {$table}
             WHERE parent_post_id = %d
               AND is_confirmed = 1
               {$where_cursor}
             ORDER BY id ASC
             LIMIT %d",
            $parent_post_id,
            $limit + 1
        )
    );

    $has_more = count($rows) > $limit;
    $rows = array_slice($rows, 0, $limit);

    $html = implode('', array_map('bbs_tree_render_comment_html', $rows));
    $next_cursor = $rows ? (int) end($rows)->id : $cursor;

    wp_send_json_success([
        'html'        => $html,
        'next_cursor' => $next_cursor,
        'has_more'   => $has_more,
    ]);
}
add_action('wp_ajax_bbs_load_replies', 'bbs_ajax_load_replies');
add_action('wp_ajax_nopriv_bbs_load_replies', 'bbs_ajax_load_replies');

/** 回答・返信投稿。parent_id=root_idなら回答、それ以外なら返信。 */
/** 回答・返信投稿。parent_id=0なら回答、それ以外なら返信。 */
function bbs_ajax_tree_post_reply(): void
{
    check_ajax_referer('bbs_tree_nonce', 'nonce');
    global $wpdb;

    $table = bbs_tree_posts_table();

    $thread_unique_id = sanitize_text_field($_POST['thread_unique_id'] ?? '');
    $parent_post_id = max(0, (int) ($_POST['parent_id'] ?? 0));
    $text = sanitize_textarea_field($_POST['text'] ?? '');
    $name = sanitize_text_field($_POST['name'] ?? '匿名');

    if ($text === '') {
        wp_send_json_error(['message' => '本文を入力してください。']);
    }

    $thread = bbs_tree_get_thread_by_unique_id($thread_unique_id);
    if (!$thread) {
        wp_send_json_error(['message' => 'スレが見つかりません。']);
    }

    $question_id = (int) $thread->id;

    [$ok, $message] = bbs_tree_can_post($question_id, $parent_post_id);
    if (!$ok) {
        wp_send_json_error(['message' => $message]);
    }

    $depth = bbs_tree_next_depth($parent_post_id);
    $now = current_time('mysql');

    $ip = function_exists('bbs_get_client_ip') ? bbs_get_client_ip() : bbs_tree_get_client_ip();
    $ua = function_exists('bbs_get_user_agent')
        ? bbs_get_user_agent()
        : substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    $user_id = function_exists('get_guest_uuid')
        ? get_guest_uuid()
        : sanitize_text_field($_COOKIE['user_id'] ?? '');

    $inserted = $wpdb->insert(
        $table,
        [
            'question_id'     => $question_id,
            'parent_post_id'  => $parent_post_id > 0 ? $parent_post_id : null,
            'depth'           => $depth,
            'replies_count'   => 0,
            'user_id'         => $user_id,
            'text'            => $text,
            'name'            => $name ?: '匿名',
            'ip'              => $ip,
            'ua'              => $ua,
            'is_confirmed'    => 1,
            'status'          => 'active',
            'created_at'      => $now,
            'updated_at'      => $now,
        ],
        [
            '%d', // question_id
            '%d', // parent_post_id
            '%d', // depth
            '%d', // replies_count
            '%s', // user_id
            '%s', // text
            '%s', // name
            '%s', // ip
            '%s', // ua
            '%d', // is_confirmed
            '%s', // status
            '%s', // created_at
            '%s', // updated_at
        ]
    );

    if (!$inserted) {
        error_log('[BBS TREE INSERT ERROR] ' . $wpdb->last_error);
        error_log('[BBS TREE INSERT QUERY] ' . $wpdb->last_query);
        wp_send_json_error(['message' => '投稿に失敗しました。']);
    }

    $new_id = (int) $wpdb->insert_id;

    if ($parent_post_id > 0) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table}
                 SET replies_count = replies_count + 1
                 WHERE id = %d",
                $parent_post_id
            )
        );
    }

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $new_id
        )
    );

    wp_send_json_success([
        'html' => bbs_tree_render_comment_html($row),
        'id'   => $new_id,
    ]);
}
add_action('wp_ajax_bbs_tree_post_reply', 'bbs_ajax_tree_post_reply');
add_action('wp_ajax_nopriv_bbs_tree_post_reply', 'bbs_ajax_tree_post_reply');

/** 3ヶ月経過スレをアーカイブ扱いにする。WP-Cron用。 */
/** 3ヶ月経過スレをアーカイブ扱いにする。WP-Cron用。 */
function bbs_tree_archive_old_threads(): void
{
    global $wpdb;

    $table = bbs_tree_questions_table();
    $border = gmdate('Y-m-d H:i:s', strtotime('-' . BBS_ARCHIVE_DAYS . ' days'));

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$table}
             SET status = 'archived',
                 archived_at = %s
             WHERE status = 'active'
               AND created_at < %s",
            current_time('mysql'),
            $border
        )
    );
}
add_action('bbs_tree_archive_old_threads_event', 'bbs_tree_archive_old_threads');

if (!wp_next_scheduled('bbs_tree_archive_old_threads_event')) {
    wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'bbs_tree_archive_old_threads_event');
}
