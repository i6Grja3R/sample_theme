<?php
// /home/vqnporqs/rss_test.txt 公開領域外
$rss_log_file = '/home/vqnporqs/rss_test.txt';

if (file_exists($rss_log_file) && filesize($rss_log_file) > 1024 * 1024) {
    file_put_contents($rss_log_file, '');
}

file_put_contents(
    $rss_log_file,
    date('Y-m-d H:i:s') . ' storage_rss start' . PHP_EOL,
    FILE_APPEND
);

require_once dirname(__DIR__, 3) . '/wp-load.php';

error_log('[RSS] storage_rss.php start');

/* RSSの保存　引数（テンプレート番号） */
storage_rss(1);
storage_rss(2);
storage_rss(3);
storage_rss(4);

error_log('[RSS] storage_rss.php end');

// 削除対象日付取得
function get_delete_date()
{
    // ※削除対象日付（50日前）
    return date('Y-m-d H:i:s', strtotime('-50 days'));
}

// RSSサイトURL取得
function get_rss_site_url($template_number)
{
    // テンプレート番号ごとのRSSサイトURL
    if (1 == $template_number) {

        return array(

            // 流速VIP
            'http://ryusoku.com/index.rdf',

            // あにまん掲示板
            'https://www.negisoku.com/index.rdf',

            // 二時萌エロ
            'http://blog.livedoor.jp/kinisoku/index.rdf',

        );
    } elseif (2 == $template_number) {

        return array(

            // 流速VIP
            'http://ryusoku.com/index.rdf',

            // あにまん掲示板
            'https://www.negisoku.com/index.rdf',

            // 二時萌エロ
            'http://blog.livedoor.jp/kinisoku/index.rdf',

        );
    } elseif (3 == $template_number) {

        return array(

            // 流速VIP
            'http://ryusoku.com/index.rdf',

            // あにまん掲示板
            'https://www.negisoku.com/index.rdf',

            // 二時萌エロ
            'http://blog.livedoor.jp/kinisoku/index.rdf',

        );
    } elseif (4 == $template_number) {

        return array(

            // 流速VIP
            'http://ryusoku.com/index.rdf',

            // あにまん掲示板
            'https://www.negisoku.com/index.rdf',

            // 二時萌エロ
            'http://blog.livedoor.jp/kinisoku/index.rdf',

        );
    }

    return array();
}

// RSSテーブル名取得
function get_rss_table_name($template_number)
{
    if (1 == $template_number) {

        return 'single_rss_feed';
    } elseif (2 == $template_number) {

        return 'double_rss_feed';
    } elseif (3 == $template_number) {

        return 'triple_rss_feed';
    } elseif (4 == $template_number) {

        return 'trisect_rss_feed';
    }

    return '';
}

// RSSの保存
function storage_rss($template_number)
{
    global $wpdb;
    global $rss_log_file;

    error_log('[RSS] storage_rss start : template_number=' . $template_number);

    // RSS保存テーブル名取得
    $rss_table_name = get_rss_table_name($template_number);

    // ※テーブル名が空なら終了
    if ('' === $rss_table_name) {

        error_log('[RSS] RSSテーブル名なし : template_number=' . $template_number);

        return;
    }

    error_log('[RSS] table=' . $rss_table_name);

    // 削除対象日付取得
    // 戻り値（削除対象日付）
    $delete_date = get_delete_date();

    error_log('[RSS] delete_date=' . $delete_date);

    // RSSサイトURL取得
    // 引数（テンプレート番号）
    // 戻り値（RSSサイトURL配列）
    $urls = get_rss_site_url($template_number);

    file_put_contents(
        $rss_log_file,
        'template=' . $template_number . ' urls_count=' . count($urls) . PHP_EOL,
        FILE_APPEND
    );

    error_log('[RSS] urls_count=' . count($urls));

    // 保存予定データ
    $save_items = array();

    // RSS取得成功件数
    $success_count = 0;

    // RSSサイトループ
    foreach ($urls as $url) {

        error_log('[RSS] URL=' . $url);

        // RSS取得
        $rss = simplexml_load_file($url);

        // ※RSS取得失敗
        if (false === $rss) {

            error_log('[RSS] RSS取得失敗 : ' . $url);

            continue;
        }

        error_log('[RSS] RSS取得成功 : ' . $url);

        // RSS取得成功
        $success_count++;

        // namespace取得
        $namespaces = $rss->getNamespaces(true);

        // RSS item取得
        if (isset($rss->channel->item)) {

            // RSS2.0形式
            $items = $rss->channel->item;
        } elseif (isset($rss->item)) {

            // RDF形式
            $items = $rss->item;
        } elseif (isset($rss->entry)) {

            // Atom形式
            $items = $rss->entry;
        } else {

            // 未対応
            $items = array();
        }

        // item件数確認
        $item_count = count($items);

        error_log('[RSS] item_count=' . $item_count . ' : ' . $url);

        // itemループ
        foreach ($items as $item) {

            // タイトル取得
            $title = trim((string) $item->title);

            // リンク取得
            $link = '';

            // Atom形式
            if (isset($item->link['href'])) {

                $link = trim((string) $item->link['href']);
            } else {

                // RSS形式
                $link = trim((string) $item->link);
            }

            // ※タイトルまたはリンクが空ならスキップ
            if ('' === $title || '' === $link) {

                error_log('[RSS] title/link 空のためスキップ');

                continue;
            }

            // 投稿日
            $date = '';

            // dc namespace存在確認
            if (isset($namespaces['dc'])) {

                // dc取得
                $dc = $item->children($namespaces['dc']);

                // 投稿日取得
                $date = (string) $dc->date;
            }

            // RSS2.0の投稿日取得
            if ('' === $date && !empty($item->pubDate)) {

                $date = (string) $item->pubDate;
            }

            // ※日付が取得出来ない場合
            if ('' === $date) {

                // 現在時刻
                $date = current_time('mysql');
            } else {

                // UNIX変換
                $timestamp = strtotime($date);

                // 日付変換
                if ($timestamp) {

                    $date = date('Y-m-d H:i:s', $timestamp);
                } else {

                    $date = current_time('mysql');
                }
            }

            // ※削除対象日付より古いRSSは保存しない
            if ($date < $delete_date) {

                error_log('[RSS] 古い記事のためスキップ : ' . $date . ' / ' . $title);

                continue;
            }

            // カテゴリ
            $subject = '';

            // dc namespace存在確認
            if (isset($namespaces['dc'])) {

                // dc取得
                $dc = $item->children($namespaces['dc']);

                // subject取得
                $subject = trim((string) $dc->subject);
            }

            // 画像URL
            $img = '';

            // content namespace存在確認
            if (isset($namespaces['content'])) {

                // content取得
                $content = $item->children($namespaces['content']);

                // encoded存在確認
                if (!empty($content->encoded)) {

                    // imgタグ検索
                    $result = preg_match(
                        '/<img[^>]+src=["\']([^"\']+)["\']/i',
                        (string) $content->encoded,
                        $matches
                    );

                    // ※画像あり
                    if (1 === $result) {

                        $img = $matches[1];
                    }
                }
            }

            // ※画像が取得出来なかった場合
            // descriptionから取得
            if ('' === $img && !empty($item->description)) {

                $result = preg_match(
                    '/<img[^>]+src=["\']([^"\']+)["\']/i',
                    (string) $item->description,
                    $matches
                );

                // ※画像あり
                if (1 === $result) {

                    $img = $matches[1];
                }
            }

            // ※descriptionが画像URLだけの場合
            if ('' === $img && !empty($item->description)) {

                $description = trim((string) $item->description);

                // URL形式なら画像URLとして使用
                if (filter_var($description, FILTER_VALIDATE_URL)) {

                    $img = $description;
                }
            }

            // 保存予定データに追加
            $save_items[] = array(

                'title'   => $title,
                'link'    => $link,
                'date'    => $date,
                'img'     => $img,
                'subject' => $subject,

            );
        }
    }

    error_log('[RSS] success_count=' . $success_count . ' : template_number=' . $template_number);
    error_log('[RSS] save_items=' . count($save_items) . ' : template_number=' . $template_number);

    file_put_contents(
        $rss_log_file,
        'template=' . $template_number .
            ' success_count=' . $success_count .
            ' save_items=' . count($save_items) .
            PHP_EOL,
        FILE_APPEND
    );

    // ※RSS取得に1つも成功していない場合は既存データを消さない
    if (0 === $success_count) {

        error_log('[RSS] RSS取得成功なし : template_number=' . $template_number);

        return;
    }

    // ※保存できる記事が1件もない場合も既存データを消さない
    if (empty($save_items)) {

        error_log('[RSS] RSS保存対象なし : template_number=' . $template_number);

        return;
    }

    // 既存RSSを全削除
    // ※RSS取得成功後に削除することで、
    // ※取得失敗時に一覧が空になるのを防ぐ
    // $delete_result = $wpdb->query("DELETE FROM {$rss_table_name}");
    $delete_result = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$rss_table_name} WHERE date < %s",
            $delete_date
        )
    );

    // 保存件数
    $insert_count = 0;

    // 保存予定データループ
    foreach ($save_items as $data) {

        // フォーマット
        $format = array(

            '%s',
            '%s',
            '%s',
            '%s',
            '%s',

        );

        // RSS保存
        $result = $wpdb->replace($rss_table_name, $data, $format);

        if (false !== $result) {

            $insert_count++;
        } else {

            error_log('[RSS] DB保存失敗 : ' . $data['title']);
            error_log('[RSS] DBエラー : ' . $wpdb->last_error);
        }
    }

    error_log('[RSS] RSS保存完了 : table=' . $rss_table_name . ' / insert_count=' . $insert_count);
}
