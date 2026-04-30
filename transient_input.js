<?php
/*
Template Name: transient_input
固定ページ: 質問する
*/
header('X-FRAME-OPTIONS: SAMEORIGIN'); // クリックジャッキング対策
get_header();                           // 通常ヘッダーを読込
get_header('menu');                    // メニュー付きヘッダーを読込

// 画像パス（テーマ / アップロードから取得）
$upload_dir = wp_upload_dir();                                // アップロード基底（URL/パス）を取得
$camera_url  = esc_url($upload_dir['baseurl'] . '/camera.png');  // camera.png のURL（存在場所に合わせて配置）
$noimage_url = esc_url($upload_dir['baseurl'] . '/noimage.png'); // noimage.png のURL（存在場所に合わせて配置）
?>

<div class="board_form_partial" id="js_board_form_partial"><!-- 全体ラッパ -->
    <div class="questionHeader-partial"><!-- 画面上部の見出し -->
        <h2>
            <span class="fa-stack">
                <i class="fa fa-circle fa-stack-2x w-circle"></i>
                <i class="fa-stack-1x fa-inverse q">Q</i>
            </span>
            <span class="q-text" id="q_text">質問する</span><!-- ステップ名 -->
        </h2>
        <div class="other_step">
            <img id="step_img" src="<?php echo esc_url(get_template_directory_uri() . '/images/step01.png'); ?>" alt="STEP1 入力"><!-- ステップ画像 -->
        </div>
    </div>

    <div id="input_area"><!-- 入力エリア -->
        <form id="input_form" method="post" name="input_form" enctype="multipart/form-data"><!-- ファイル送信用にenctype指定 -->
            <div class="image-partial"><!-- 添付ファイル群 -->
                <h2>
                    画像をアップロード (JPG / PNG)
                    <span class="required">
                        ※画像は1ファイル2MBまで（合計6MBまで）アップロードできます
                    </span><!-- サーバ設定に合わせた案内 -->
                </h2>

                <!-- 1つ目の添付 -->
                <div class="image-selector-button">
                    <label>
                        <div class="image-camera-icon">
                            <img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px" alt="select file">
                        </div>
                        <!-- サーバ許可に合わせて accept を指定（gif は除外） -->
                        <input type="file" class="attach" name="attach[]" accept=".jpg,.jpeg,.png" style="display:none;">
                    </label>
                    <div class="viewer" style="display:none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>

                <!-- 2つ目の添付 -->
                <div class="image-selector-button">
                    <label>
                        <div class="image-camera-icon">
                            <img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px" alt="select file">
                        </div>
                        <input type="file" class="attach" name="attach[]" accept=".jpg,.jpeg,.png" style="display:none;">
                    </label>
                    <div class="viewer" style="display:none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>

                <!-- 3つ目の添付 -->
                <div class="image-selector-button">
                    <label>
                        <div class="image-camera-icon">
                            <img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px" alt="select file">
                        </div>
                        <input type="file" class="attach" name="attach[]" accept=".jpg,.jpeg,.png" style="display:none;">
                    </label>
                    <div class="viewer" style="display:none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
            </div><!-- /image-partial -->

            <div class="body-partial-parts"><!-- 本文 -->
                <h2>質問文 (question)<span class="required">※必須</span></h2>
                <div class="parts">
                    <textarea class="input" name="text" id="text"
                        maxlength="<?php echo MAX_LENGTH::TEXT; ?>"
                        minlength="<?php echo MIN_LENGTH::TEXT; ?>"
                        data-length="<?php echo MAX_LENGTH::TEXT; ?>"
                        data-minlength="<?php echo MIN_LENGTH::TEXT; ?>"
                        placeholder="荒らし行為や誹謗中傷や著作権の侵害はご遠慮ください"></textarea>
                    <div class="msg_partial"></div>
                </div>
            </div>

            <div class="title-partial-parts"><!-- タイトル -->
                <h2>質問タイトル (title)<span class="required">※必須</span></h2>
                <div class="parts">
                    <input class="input" type="text" name="title" id="title"
                        maxlength="<?php echo MAX_LENGTH::TITLE; ?>"
                        minlength="<?php echo MIN_LENGTH::TITLE; ?>"
                        data-length="<?php echo MAX_LENGTH::TITLE; ?>"
                        data-minlength="<?php echo MIN_LENGTH::TITLE; ?>"
                        placeholder="<?php echo MIN_LENGTH::TITLE; ?>文字以上で入力してください">
                    <div class="msg_partial"></div>
                </div>
            </div>

            <div class="stamp-partial">
                <h2>スタンプを選ぶ<span class="required">※必須</span></h2>
                <input type="radio" name="stamp" value="1" id="stamp_1"><label for="stamp_1"></label>
                <input type="radio" name="stamp" value="2" id="stamp_2"><label for="stamp_2"></label>
                <input type="radio" name="stamp" value="3" id="stamp_3"><label for="stamp_3"></label>
                <input type="radio" name="stamp" value="4" id="stamp_4"><label for="stamp_4"></label>
                <input type="radio" name="stamp" value="5" id="stamp_5"><label for="stamp_5"></label>
                <input type="radio" name="stamp" value="6" id="stamp_6"><label for="stamp_6"></label>
                <input type="radio" name="stamp" value="7" id="stamp_7"><label for="stamp_7"></label>
                <input type="radio" name="stamp" value="8" id="stamp_8"><label for="stamp_8"></label>
            </div>

            <div class="usericon-partial"><!-- 任意のアイコン（4つ目スロット扱い） -->
                <h2>
                    アイコン画像をアップロード (JPG / PNG)
                    <span class="required">
                        ※画像は1ファイル1MBまでアップロードできます
                    </span><!-- サーバ設定に合わせた案内 -->
                </h2>
                <div class="usericon-thumbnail-button">
                    <label>
                        <div class="usericon-uploads">
                            <img src="<?php echo $noimage_url; ?>" class="changeImg" style="height:90px;width:90px" alt="user icon">
                        </div>
                        <input type="file" class="attach-icon" name="usericon" accept=".jpg,.jpeg,.png" style="display:none;">
                    </label>
                    <div class="viewer" style="display:none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
            </div>

            <div class="name-partial-parts"><!-- 名前 -->
                <h2>名前 (name)<span class="required">※任意</span></h2>
                <div class="parts">
                    <input class="input" type="text" name="name" id="name"
                        data-length="<?php echo MAX_LENGTH::NAME; ?>"
                        data-minlength="<?php echo MIN_LENGTH::NAME; ?>"
                        placeholder="未入力の場合は匿名で表示されます">
                    <div class="msg_partial"></div> <!-- ←★ここも追加 -->
                </div>
            </div>

            <div class="post-button"><!-- 送信ボタン -->
                <button type="button" id="submit_button" name="mode" value="confirm">確認画面へ進む</button>
            </div>
        </form>
    </div><!-- /input_area -->

    <div id="confirm_area" class="hideItems"></div><!-- 確認表示エリア（初期は非表示） -->
    <div id="result_area" class="hideItems"></div><!-- 完了表示エリア（初期は非表示） -->
</div><!-- /board_form_partial -->

<?php
// 送信用の nonce をここで生成（submit 用 / confirm 用を分ける）
$submit_nonce  = wp_create_nonce('bbs_quest_submit');
$confirm_nonce = wp_create_nonce('bbs_quest_confirm');
$ajax_url      = admin_url('admin-ajax.php');

$theme_uri = trailingslashit(get_template_directory_uri());
// スタンプ番号 → 画像URL のマップ
$stamp_files = [
    1 => $theme_uri . 'images/stamp/1.png',
    2 => $theme_uri . 'images/stamp/2.png',
    3 => $theme_uri . 'images/stamp/3.png',
    4 => $theme_uri . 'images/stamp/4.png',
    5 => $theme_uri . 'images/stamp/5.png',
    6 => $theme_uri . 'images/stamp/6.png',
    7 => $theme_uri . 'images/stamp/7.png',
    8 => $theme_uri . 'images/stamp/8.png',
];
?>

<script>
    // 直書きJS用のグローバル設定をPHPで埋め込む
    window.bbs_vars = {
        ajax_url: "<?php echo esc_js(admin_url('admin-ajax.php')); ?>",
        nonce: "<?php echo esc_js(wp_create_nonce('bbs_quest_submit')); ?>",
    };
    window.bbs_confirm_vars = {
        ajax_url: "<?php echo esc_js(admin_url('admin-ajax.php')); ?>",
        nonce: "<?php echo esc_js(wp_create_nonce('bbs_quest_confirm')); ?>",
        // 将来的にバグる可能性ありなので英語スラッグにするのがベスト
        list_url: "<?php echo esc_js(home_url('/question-list/')); ?>"
    };
</script>
<script>
    // 安全エンドポイントURLを作る
    function tmpGetUrl(fname) {
        // URLのパラメータを追加する
        const p = new URLSearchParams({
            // どの処理を呼び出すかを指定する識別子
            action: "bbs_tmp_get",
            // 現在編集中の下書き（ドラフト）IDを文字列として送る
            draft_id: String(lastDraftId),
            // 取得したい具体的なファイル名を指定
            file: fname,
            // セキュリティ（不正操作防止）のための合言葉、ノンスという一時的なトークンを照合
            nonce: (window.bbs_confirm_vars?.nonce || "")
        });
        return (AJAX_URL + "?" + p.toString());
    }

    /* -------------------------------------
     * ステップ表示の切替（UIのみ）
     * ------------------------------------- */
    // 進捗用の見出し・画像・各エリアを取得
    const step_img = document.getElementById("step_img"); // ステップ画像 <img>
    const q_text = document.getElementById("q_text"); // ステップ見出しテキスト
    const input_area = document.getElementById("input_area"); // 入力エリア
    const confirm_area = document.getElementById("confirm_area"); // 確認エリア
    const result_area = document.getElementById("result_area"); // 完了エリア（今回は未使用）

    function change1() {
        q_text.textContent = "質問する";
        step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step01.png'); ?>";
        step_img.alt = "STEP1 入力";

        if (confirm_area) confirm_area.style.display = "none";
        if (input_area) input_area.style.display = "block";

        document.body.classList.remove('is-confirm'); // ←保険でここも

        // プレビュー時に保持した値を復元（file は復元しない）
        if (window.lastPreviewData) {
            populateFormFromData(window.lastPreviewData);
        }
        // もし「戻ったら添付も消したい」運用ならこちらを有効化
        // clearAllAttachments();
    }

    function change2() {
        q_text.textContent = "確認する";
        step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step02.png'); ?>";
        step_img.alt = "STEP2 確認";
    }

    function change3() {
        q_text.textContent = "完了";
        step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step03.png'); ?>";
        step_img.alt = "STEP3 完了";
    }

    /* -------------------------------------
     * ローディングアニメーション制御
     *  - ボタンに .wait を付け外し
     *  - 連打防止のため disabled/aria-busy を制御
     * ------------------------------------- */
    function toggleLoading(btn, isLoading) {
        // ボタン要素が存在しない場合にエラーにならないよう、処理を中断する安全装置です。
        if (!btn) return;
        // 読み込み中の時
        if (isLoading) {
            // ボタンをクリックできないようにします。連打による「二重投稿」を物理的に防ぎます。
            btn.disabled = true;
            // CSSで用意した「wait」というクラスを追加します。
            btn.classList.add('wait');
            // スクリーンリーダー（音声読み上げ）を使うユーザーに対して、「現在この要素は処理中です」と伝えるためのアクセシビリティ設定です。
            btn.setAttribute('aria-busy', 'true');
        } else {
            btn.disabled = false;
            btn.classList.remove('wait');
            btn.removeAttribute('aria-busy');
        }
    }

    /* ------------------------------
     * エスケープ（<>&"' すべて）
     * ------------------------------ */
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (m) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
    })[m]);

    /* ------------------------------
     * フォームへ値を流し込むヘルパー
     *  - previewデータ等から入力値を復元
     *  - file input は仕様上復元不可（ブラウザ制約）
     * ------------------------------ */
    function populateFormFromData(data) {
        // テキスト系
        const titleEl = document.getElementById('title');
        const textEl = document.getElementById('text');
        const nameEl = document.getElementById('name');

        // タイトルの入力欄（titleEl）が画面内に存在するかを確認
        // もし data.title が null または undefined だった場合、代わりに ''（空の文字列） を入れます。
        if (titleEl) titleEl.value = data.title ?? '';
        // タイトル欄があれば、データ内のタイトル（無ければ空っぽ）を表示する。本文欄があれば、本文を表示する。名前欄があれば、名前を表示する
        if (textEl) textEl.value = data.text ?? '';
        if (nameEl) nameEl.value = data.name ?? '';

        // スタンプ（1..8想定）
        // 「データの中に『スタンプ情報』がちゃんと存在するか」を確認
        if (typeof data.stamp !== 'undefined') {
            // String(data.stamp)：スタンプの番号が「1」という数字でも「"1"」という文字でも、確実に文字として扱って検索に失敗しないように工夫
            const s = document.querySelector(`input[name="stamp"][value="${String(data.stamp)}"]`);
            // 「もし該当するスタンプのボタンが見つかったら、それを『選択状態（チェックあり）』にする」という命令です。
            if (s) s.checked = true;
        }

        // カウンタやバリデーション再評価
        if (typeof validation === 'function') validation();
    }

    // すべての添付をクリア（本当に input の値を空にするのが重要）
    function clearAllAttachments() {
        // class="attach" がついたファイル選択ボタン、class="attach-icon" がついたファイル選択ボタン
        // リセット対象となるターゲットを2種類指定
        // 取得した複数の入力欄に対して、一つずつ順番に { } の中の処理を実行
        document.querySelectorAll('input.attach[type="file"], input.attach-icon[type="file"]').forEach(inp => {
            // 「現在選択されているファイル名」が消去され、未選択の状態に戻る
            inp.value = '';
        });
        // プレビューUIを消す（あれば）
        // 見つかったすべてのプレビュー枠に対して、一つずつ順番に { } の中の処理を実行
        document.querySelectorAll('.viewer').forEach(v => {
            // プレビュー枠の中に入っている文字（ファイル名など）を消去
            v.textContent = '';
            v.style.display = 'none';
        });

        document.querySelectorAll('.image-camera-icon, .usericon-uploads').forEach(area => {
            area.classList.remove('hideItems');
        });
        // 送信可否の再評価
        if (typeof validation === 'function') validation();
    }

    /* -------------------------------------
     * 文字数表示（タイトル/本文/名前の .input 要素）
     *  - data-length / data-minlength 属性を利用
     *  - 直後の <div> に「残り/超過」を表示
     * ------------------------------------- */
    // 文字数カウンタ（最小・最大の両方に対応）
    function display_text_length(e) {
        // id が text / title / name を入力した ➡️ 門番を通過して、保存処理へ進む。
        if (!e || !e.target || !['text', 'title', 'name'].includes(e.target.id)) return;

        // 実際にイベントが発生した要素を取得
        const el = e.target;
        const msg = el.nextElementSibling; // 各 input/textarea の直後の <div class="msg_partial">
        if (!msg) return;

        // maxlength / minlength は data-* と HTML 属性のどちらでもOKにする
        // ← ココが重要：data-length / data-minlength も確実に拾う
        const getMax = (el) => {
            // getAttributeがnullの場合は0になり、数字以外の文字列ならNaN(判定で0)になる
            const a = Number(el.getAttribute('maxlength')) || 0;
            const d = Number(el.dataset.length) || 0;
            return a || d;
        };

        const getMin = (el) => {
            const a = Number(el.getAttribute('minlength')) || 0;
            const d = Number(el.dataset.minlength) || 0;
            return a || d;
        };

        const max = getMax(el);
        const min = getMin(el);
        const len = el.value.length;

        // 表示ノード準備
        // 数字だけ色を付ける helper
        const strong = (num) => {
            const s = document.createElement('strong');
            s.textContent = String(num);
            s.style.color = '#e52d77'; // ← ★数字だけピンク
            return s;
        };

        msg.className = 'msg_partial';
        msg.style.color = ''; // ベース文字色
        // ノードからすべての子ノードを取り除く
        msg.replaceChildren();

        // 判定順：超過 → 不足 → 残り（上限設定がある時） → 何もしない
        if (max && len > max) {
            const over = len - max;
            msg.style.color = 'red';
            msg.append(
                '超過 ', strong(over), ' 文字です（最大 ', strong(max), ' 文字）。'
            );
            el.classList.add('is-over');
        } else if (min && len < min) {
            const lack = min - len;
            msg.style.color = '#d9534f'; // 注意色
            msg.append(
                'あと ', strong(lack), ' 文字必要です（最低 ', strong(min), ' 文字）。'
            );
            el.classList.remove('is-over');
        } else if (max) {
            const remain = max - len;
            msg.append('残り ', strong(remain), ' 文字入力できます。');
            el.classList.remove('is-over');
        } else {
            msg.textContent = '';
            el.classList.remove('is-over');
        }

        // カウンタ更新のたびに送信可否も再評価
        if (typeof validation === 'function') validation();
    }

    // （任意）初期表示時に現在値でカウンタを出す
    function updateAllCountersOnce() {
        ['title', 'text', 'name'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            // “入力イベント” を発火して同じ処理系に乗せる
            // 文字列の長さを取得し、それを画面に表示（表示）する
            display_text_length({
                target: el
            });
        });
    }

    // すでにあなたの init があるなら、その中のリスナーだけ確認：
    // document.addEventListener('input', (e) => {
    //   display_text_length(e);
    //   validation();
    // });

    // 初回に 1 回だけ全フィールドのカウンタを描画したい場合は、
    // DOMContentLoaded か init() の最後などで呼びます。
    // window.addEventListener('DOMContentLoaded', updateAllCountersOnce);

    /* -------------------------------------
     * 送信ボタン活性/非活性制御
     *  - タイトル/本文の最小・最大
     *  - スタンプ必須（1..8 のどれか）
     *  - 添付の軽いクライアントチェック（画像2MB / アイコン1MB）
     *  ※最終判定はサーバー側で必ず再検証
     * ------------------------------------- */
    function validation() {
        const btn = document.getElementById('submit_button');
        if (!btn) return;

        const titleEl = document.getElementById('title');
        const textEl = document.getElementById('text');

        // ★ dataset だけでなく HTML 属性の maxlength/minlength も見る
        const getLimit = (el, name, fallback = 0) => {
            // HTML属性を取る
            const attr = Number(el?.getAttribute(name)) || 0;
            // data属性のキーを決める
            // data-length="" data-minlength=""に対応
            const dataKey = name === 'maxlength' ? 'length' : 'minlength';
            // data属性を取る
            // ?? fallback → undefinedのときだけfallback
            const data = Number(el?.dataset?.[dataKey] ?? fallback) || 0;

            // 最終結果
            return attr || data;
        };

        // 制限の最大値を返す
        const titleMax = getLimit(titleEl, 'maxlength'); // maxlength or data-length
        const titleMin = getLimit(titleEl, 'minlength'); // minlength or data-minlength
        const textMax = getLimit(textEl, 'maxlength');
        const textMin = getLimit(textEl, 'minlength');

        // titleElが存在するなら value を取る、nullだとエラー
        const titleLen = (titleEl?.value || '').length;
        const textLen = (textEl?.value || '').length;

        // ★スタンプは change イベントのほうが確実。判定はここでOK
        const stamps = document.querySelectorAll('input[name="stamp"]');
        let stampOk = false;
        for (const s of stamps)
            if (s.checked) {
                stampOk = true;
                break;
            }

        // 最小文字数チェック、最大文字数チェック
        const titleOk = (titleMin ? titleLen >= titleMin : true) && (titleMax ? titleLen <= titleMax : true);
        const textOk = (textMin ? textLen >= textMin : true) && (textMax ? textLen <= textMax : true);

        // 添付ざっくりチェック（最終判定はサーバ）
        const inputs = document.querySelectorAll(
            'input.attach[type="file"], input.attach-icon[type="file"]'
        );

        let filesCount = 0;
        let clientFileOk = true;
        let clientTotalSize = 0;

        // サーバの定数に合わせる
        // const MAX_FILES = 4; // BBS_MAX_FILES
        const MAX_TOTAL = 6 * 1024 * 1024; // BBS_MAX_TOTAL
        const MAX_PER_IMAGE = 2 * 1024 * 1024; // BBS_MAX_PER_FILE_IMAGE
        const MAX_PER_ICON = 1 * 1024 * 1024; // 追加

        // inputごとにループ
        inputs.forEach((input, index) => {
            // ファイルが無ければスキップ
            if (!input.files || !input.files.length) return;

            // ファイルごとにループ
            for (const file of input.files) {
                filesCount++;

                // アイコンかどうか判定
                // const isIcon = (index === 3); // ←4つ目がアイコン
                const isIcon = input.classList.contains('attach-icon');

                // サイズ上限を決定
                // アイコン → 1MB 通常画像 → 2MB
                let maxPer = isIcon ? MAX_PER_ICON : MAX_PER_IMAGE;

                // サイズチェック
                if (file.size <= 0 || file.size > maxPer) {
                    clientFileOk = false;
                }

                // 合計サイズ加算
                clientTotalSize += file.size;
            }
        });

        // 合計サイズのざっくりチェック（40MB超ならNG）
        if (clientTotalSize > MAX_TOTAL) {
            clientFileOk = false;
        }

        // enabled 判定は今までどおり
        const enabled = (titleOk && textOk && stampOk && clientFileOk);
        btn.disabled = !enabled;

        // ★詳細ログ（コンソールに出ます）
        console.table({
            titleLen,
            titleMin,
            titleMax,
            titleOk,
            textLen,
            textMin,
            textMax,
            textOk,
            stampOk,
            filesCount,
            clientFileOk,
            '=> enabled': enabled
        });
    }

    /* ------------------------------
     * 共通: AJAX URL（ローカライズ優先）
     * ------------------------------ */
    // 使えるajax_urlがあればそれを使う、それもなければPHPで直接生成したURLを使う
    const AJAX_URL =
        (window.bbs_vars?.ajax_url) ||
        (window.bbs_confirm_vars?.ajax_url) ||
        "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";

    /* ------------------------------
     * グローバル: draft_id
     * ------------------------------ */
    let lastDraftId = null; // ← ここで 1 回だけ定義（以降は上書きのみ）

    // JavaScriptで非同期処理を扱う際、従来はPromiseを使って記述していました。
    // しかし、処理が複雑になるとコードが読みにくくなることがあります。そこで登場したのがasync/awaitです。
    async function compressImageFile(file, options = {}) {
        const maxWidth = options.maxWidth || 1200;
        const quality = options.quality || 0.72;

        // 画像じゃないならスキップ
        if (!file || !file.type.startsWith('image/')) {
            return file;
        }

        // ① PNGはそのままにする
        // 透過PNGをJPEG/WebPにすると見た目が変わる可能性があるため
        if (file.type === 'image/png') {
            return file;
        }

        // 画像をbitmapに変換
        // createImageBitmapで変換してcanvasに描画して圧縮
        const bitmap = await createImageBitmap(file);

        let width = bitmap.width;
        let height = bitmap.height;

        if (width > maxWidth) {
            // 「元の幅に対して、最大幅がどれくらいの割合か（縮小率）」を計算
            height = Math.round(height * (maxWidth / width));
            // 横幅を強制的に最大値（maxWidth）に書き換え
            width = maxWidth;
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        // canvasに「描くためのペン」を取得
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return file;
        }

        // bitmap画像を左上(0,0)に指定サイズ(width × height)で描画
        ctx.drawImage(bitmap, 0, 0, width, height);

        const blob = await new Promise(resolve => {
            // canvas → ファイルデータに変換、quality 圧縮率（0〜1）
            // 1.0	高画質（重い）、0.7	バランス、0.3 低画質
            canvas.toBlob(resolve, 'image/jpeg', quality);
        });

        // 変換失敗時は元ファイルを返す
        if (!blob) {
            return file;
        }

        // 圧縮後の方が大きいなら元ファイルを使う
        if (blob.size >= file.size) {
            return file;
        }

        // 拡張子を削除
        const baseName = file.name.replace(/\.[^.]+$/, '');
        // 新しいFileを作る [blob] 中身（圧縮後データ）
        // メタ情報
        return new File([blob], baseName + '.jpg', {
            type: 'image/jpeg',
            lastModified: Date.now()
        });
    }

    function setInputFile(input, file) {
        // ドラッグ＆ドロップ操作やクリップボード操作などで使われる「データの入れ物」のようなものです。
        const dt = new DataTransfer();
        // 入れ物の中に、引数で受け取った file（画像データなど）を追加します。
        dt.items.add(file);
        // 入れ物の中にある「ファイルリスト」を取り出し、HTMLのファイル選択欄（input）にセットします。
        input.files = dt.files;
    }

    /* ------------------------------
     * 添付ファイル安全版イベント関数
     * ------------------------------ */
    function set_attach_event(fileAreaSelector, usericonIndex) {
        // 許可する拡張子とMIME（最低限のクライアント側バリデーション。最終判定はサーバ）
        // jpg と jpeg は同じMIMEになる
        const ALLOWED = {
            'image': ['image/jpeg', 'image/png']
        };
        const ALLOWED_EXT = ['jpg', 'jpeg', 'png'];

        // スロット別の最大サイズ(MB)
        const MAX_MB_USERICON = 1; // アイコン 1MB
        const MAX_MB_IMAGE = 2; // 通常画像 2MB

        // 各スロットに紐づく一時URLを覚えておいて clear 時に解放する
        const urlBucket = new Map(); // key: input[type=file] element, value: Array<objectURL>

        // 要素の収集
        // 各スロットの並び順は input.attach[type="file"] / .viewer / .image-camera-icon or .usericon-uploads が同じインデックスで並んでいる前提です。ズレていると表示がおかしくなるので、HTML側の順番をそろえてください。
        const attachInputs = document.querySelectorAll(
            'input.attach[type="file"], input.attach-icon[type="file"]'
        );
        const viewers = document.querySelectorAll('.viewer');

        // 「カメラ画像エリア」（=ファイル未選択時に見えているエリア）
        // fileAreaSelector には '.image-camera-icon,.usericon-uploads' のようにカンマ区切りで渡してください
        const fileAreas = document.querySelectorAll(fileAreaSelector);

        // clear ボタン（各スロットに1つ想定）
        const clearBtns = document.querySelectorAll('.attachclear');

        // objectURL を安全に解放
        const revokeAllFor = (inp) => {
            // 保存してあるURL一覧を取得、urlBucket は Map
            const list = urlBucket.get(inp);
            // 配列かチェック
            if (Array.isArray(list)) {
                for (const u of list) {
                    try {
                        URL.revokeObjectURL(u); // 画像を選び直すたびにメモリが増え続けるからURLを1個ずつ削除
                    } catch {}
                }
            }
            urlBucket.set(inp, []); // 配列リセット
        };

        // シンプルな拡張子取得
        // split('.') "name.jpg" → ["name", "jpg"]
        const getExt = (name) => (name.split('.').pop() || '').toLowerCase();

        // クイックな MIME/拡張子チェック
        const isAllowed = (file) => {
            // ファイル名から拡張子（jpgやpngなど）を取り出す
            const ext = getExt(file.name);
            // 拡張子が、あらかじめ決めた「許可リスト（ALLOWED_EXT）」に入っていなければ、その時点で拒否
            if (!ALLOWED_EXT.includes(ext)) return false;

            // ブラウザが判定したファイルの「MIMEタイプ（ファイルの種類情報）」を取得
            const type = String(file.type || '');
            if (!type) return false;

            // ファイルの種類が「画像」であれば、さらに詳細な種類（jpegなのかpngなのか等）をチェック
            if (type.startsWith('image/')) return ALLOWED.image.includes(type);
            // return ALLOWED.video.includes(type);
            // if (type === 'application/pdf') return true; // 上で拡張子も見ているのでOK

            return false;
        };

        // スロット別の許可判定（usericonIndex は jpg/png のみ）
        const isAllowedForSlot = (slotIndex, file) => {
            const inp = attachInputs[slotIndex];
            // HTMLの順番変えたら即バグ index判定からclass判定に変更
            const isIcon = inp.classList.contains('attach-icon');
            // ファイル名（file.name）を安全に取得し、すべて小文字に変換する処理
            const name = String(file.name || '').toLowerCase();
            const type = String(file.type || '');

            if (isIcon) {
                // ユーザーアイコン枠は jpg/png のみ
                const okExt = /\.(jpg|jpeg|png)$/i.test(name);
                const okMime = type === 'image/jpeg' || type === 'image/png';
                return okExt && okMime;
            }
            // それ以外の枠は従来どおり（画像を許可）
            return isAllowed(file);
        };

        // プレビュー描画
        const renderPreview = (slotIndex, file) => {
            const v = viewers[slotIndex];
            if (!v) return;

            // 既存プレビューはクリア（URLも解放）
            v.textContent = '';

            const url = URL.createObjectURL(file);

            // タイプごとに安全な要素を作成（autoplay なし）
            let el = null;
            if (file.type.startsWith('image/')) {
                el = document.createElement('img');
                el.alt = '';
            } else {
                return; // 想定外
            }

            /* } else if (file.type === 'application/pdf') {
                el = document.createElement('iframe'); */

            // レイアウト（旧コード準拠）
            const inp = attachInputs[slotIndex];
            // HTMLの順番変えたら即バグ index判定からclass判定に変更
            const isIcon = inp.classList.contains('attach-icon');
            el.style.height = isIcon ? '90px' : '301px';
            el.style.width = isIcon ? '90px' : '535px';
            el.style.objectFit = isIcon ? 'contain' : 'fill';

            el.src = url;
            v.appendChild(el);
            v.style.display = 'block';

            // 生成URLを記憶
            // 既存の配列を取得（なければ新規）
            const arr = urlBucket.get(attachInputs[slotIndex]) || [];
            // URLを追加、今作った URL.createObjectURL(file) を保存
            arr.push(url);
            // 「このinputにはこのURL配列が紐づいてるよ」と登録
            urlBucket.set(attachInputs[slotIndex], arr);
        };

        const setFileToSlot = async (slotIndex, file) => {
            const inp = attachInputs[slotIndex];
            const fileArea = fileAreas[slotIndex];
            const viewer = viewers[slotIndex];

            // 要素がなければ処理中断（クラッシュ防止）
            if (!inp || !viewer || !fileArea) return;

            // HTMLの順番変えたら即バグ index判定からclass判定に変更
            const isIcon = inp.classList.contains('attach-icon');

            // 拡張子も見て動画かどうか判定
            const ext = String(file.name || '').split('.').pop().toLowerCase();
            // サイズ上限決定
            let maxMB = isIcon ? MAX_MB_USERICON : MAX_MB_IMAGE;

            // ここは分岐の外で定義する
            const maxBytes = maxMB * 1024 * 1024;

            console.log('[setFileToSlot] slot', slotIndex, {
                name: file.name,
                type: file.type,
                ext,
                maxMB,
                sizeMB: (file.size / 1024 / 1024).toFixed(2),
            });

            // 1. 先に種別チェック
            // jpg/png以外は弾く
            if (!isAllowedForSlot(slotIndex, file)) {
                alert('サポートしていないファイル種別です（画像：jpg/pngのみ許可）。');
                // inputリセット
                inp.value = '';
                // プレビュー削除
                viewer.textContent = '';
                viewer.style.display = 'none';
                // カメラアイコン戻す
                fileArea.classList.remove('hideItems');

                if (typeof validation === 'function') validation();
                return;
            }

            // 2. そのあとサイズチェック
            if (file.size <= 0 || file.size > maxBytes) {
                alert(`ファイルサイズが上限(${maxMB}MB)を超えています。`);

                inp.value = '';
                viewer.textContent = '';
                viewer.style.display = 'none';
                fileArea.classList.remove('hideItems');

                if (typeof validation === 'function') validation();
                return;
            }

            // カメラエリアを隠し、プレビューを描画
            // 画像だけブラウザ側で圧縮する
            let uploadFile = file;

            // 圧縮処理
            if (file.type.startsWith('image/')) {
                // 圧縮実行
                try {
                    uploadFile = await compressImageFile(file, {
                        maxWidth: isIcon ? 1000 : 1500,
                        quality: isIcon ? 0.7 : 0.75
                    });
                } catch (error) {
                    console.error('圧縮失敗:', error);
                    alert('画像の処理に失敗しました。別の画像で試してください。');
                    inp.value = '';
                    viewer.textContent = '';
                    viewer.style.display = 'none';
                    fileArea.classList.remove('hideItems');
                    validation?.();
                    return;
                }

                // 圧縮後でも上限を超えていたら止める
                if (uploadFile.size <= 0 || uploadFile.size > maxBytes) {
                    alert(`圧縮後のファイルサイズが上限(${maxMB}MB)を超えています。`);
                    inp.value = '';
                    viewer.textContent = '';
                    viewer.style.display = 'none';
                    fileArea.classList.remove('hideItems');

                    validation?.();
                    return;
                }

                // input.files を圧縮後ファイルに差し替える
                setInputFile(inp, uploadFile);
            }

            // カメラエリアを隠し、プレビューを描画
            fileArea.classList.add('hideItems');
            renderPreview(slotIndex, uploadFile);

            // 送信ボタンの有効/無効更新
            if (typeof validation === 'function') validation();
        };

        // input[type=file] の change
        attachInputs.forEach((inp, idx) => {
            // 初期化
            urlBucket.set(inp, []);

            inp.addEventListener('change', async () => {
                // 旧URLを解放
                revokeAllFor(inp);

                const file = inp.files && inp.files[0];
                const fileArea = fileAreas[idx];
                const viewer = viewers[idx];
                if (!file) {
                    // 何も選んでいない ⇒ カメラエリアを戻す
                    if (viewer) {
                        viewer.textContent = '';
                        viewer.style.display = 'none';
                    }
                    if (fileArea) fileArea.classList.remove('hideItems');
                    if (typeof validation === 'function') validation();
                    return;
                }
                await setFileToSlot(idx, file);
            });
        });

        // clear ボタン
        clearBtns.forEach((btn, idx) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();

                const inp = attachInputs[idx];
                const fileArea = fileAreas[idx];
                const viewer = viewers[idx];
                if (!inp || !viewer || !fileArea) return;

                // input クリア
                inp.value = '';

                // プレビューを消す＆objectURL解放
                viewer.textContent = '';
                viewer.style.display = 'none';
                revokeAllFor(inp);

                // カメラエリアを復活
                fileArea.classList.remove('hideItems');

                if (typeof validation === 'function') validation();
            });
        });

        // D&D (ファイルエリアへのドラッグ＆ドロップ)
        fileAreas.forEach((fa, idx) => {
            if (!fa) return;

            // ドラッグ見た目
            fa.addEventListener('dragover', (e) => {
                e.preventDefault();
                fa.classList.add('dragover');
            });
            fa.addEventListener('dragleave', (e) => {
                e.preventDefault();
                fa.classList.remove('dragover');
            });

            fa.addEventListener('drop', async (e) => {
                e.preventDefault();
                fa.classList.remove('dragover');

                const files = e.dataTransfer && e.dataTransfer.files;
                if (!files || !files.length) return;

                const file = files[0];

                // input.files にも反映（送信のため）
                const dt = new DataTransfer();
                dt.items.add(file);
                const inp = attachInputs[idx];
                if (inp) inp.files = dt.files;

                // 旧URLを解放の上プレビュー
                if (inp) revokeAllFor(inp);
                await setFileToSlot(idx, file);
            });
        });
    }

    /* ------------------------------
     * 確認画面ボタン生成関数（追加）
     * ------------------------------ */
    function create_button_parts(formType = 1) {
        const old = document.getElementById('confirm_button');
        if (old && old.parentNode) old.parentNode.removeChild(old);

        const wrap = document.createElement('div');
        wrap.className = 'post-button';

        // 入力画面へ戻るボタン
        const back = document.createElement('button');
        back.type = 'button';
        back.className = 'answer-previous';
        back.textContent = '入力画面へ戻る';
        back.addEventListener('click', () => {
            if (typeof change1 === 'function') change1();
            if (typeof input_area !== 'undefined' && input_area) input_area.style.display = 'block';
            if (typeof confirm_area !== 'undefined' && confirm_area) {
                confirm_area.textContent = '';
                confirm_area.style.display = 'none';
            }

            document.body.classList.remove('is-confirm'); // ←★ここ
        });
        wrap.appendChild(back);

        // 投稿確定ボタン
        const go = document.createElement('button');
        go.type = 'button';
        go.id = 'confirm_button';
        go.className = 'answer-following';
        go.textContent = '投稿する';
        go.addEventListener('click', confirm_button_click, {
            once: true
        });
        wrap.appendChild(go);

        return wrap;
    }

    /* ------------------------------
     * 送信（submit → bbs_quest_submit）
     * ------------------------------ */
    async function submit_button_click() {
        const btn = document.getElementById("submit_button");
        toggleLoading(btn, true);

        try {
            // ---- 送信: submit → bbs_quest_submit ----
            const formData = new FormData(input_form);
            formData.append("action", "bbs_quest_submit");
            if (window.bbs_vars?.nonce) formData.append("nonce", bbs_vars.nonce);

            // ---- 送信: submit → bbs_quest_submit ----
            const imageInputs = document.querySelectorAll('input.attach[type="file"]');
            const iconInput = document.querySelector('input.attach-icon[type="file"]');

            let total = 0;

            // 通常画像3枚だけ合計
            imageInputs.forEach(input => {
                if (input.files && input.files.length > 0) {
                    total += input.files[0].size;
                }
            });

            // アイコンは別チェック
            if (iconInput && iconInput.files && iconInput.files.length > 0) {
                if (iconInput.files[0].size > 1 * 1024 * 1024) {
                    alert("アイコンは1MBまでです");
                    toggleLoading(btn, false);
                    return;
                }
            }

            if (total > 6 * 1024 * 1024) {
                alert("画像は合計6MBまでです");
                toggleLoading(btn, false);
                return;
            }

            // ← この位置に入れる
            console.log('files in form:',
                Array.from(document.querySelectorAll('input.attach[type="file"]'))
                .map(i => Array.from(i.files).map(f => f.name))
            );

            formData.delete('attach[]');

            imageInputs.forEach(inp => {
                if (inp.files && inp.files.length > 0) {
                    for (const f of inp.files) {
                        formData.append('attach[]', f, f.name);
                    }
                }
            });

            formData.delete('usericon');

            if (iconInput && iconInput.files && iconInput.files.length > 0) {
                formData.append('usericon', iconInput.files[0], iconInput.files[0].name);
            }
            // ここまで追加

            const res = await fetch(AJAX_URL, {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            });

            if (!res.ok) {
                // txtをエラーメッセージに含めず、ログにだけ残す
                const txt = await res.text().catch(() => "");
                // console.error("Server Error Details:", txt); // 開発者だけが見れる
                throw new Error(`サーバーエラーが発生しました (${res.status})`);
            }

            // JSON解析の失敗も、より具体的にハンドリング
            let json;
            try {
                json = await res.json();
            } catch (e) {
                throw new Error("応答データが正しくありません。");
            }

            if (!json || json.success !== true) {
                const msg = json?.data?.errors?.[0] || json?.data?.message || "送信に失敗しました。";
                alert(msg);
                return;
            }

            // ★ドラフトIDを保持
            lastDraftId = json.data?.draft_id || json.draft_id || null;
            if (!lastDraftId) {
                alert("ドラフトIDの取得に失敗しました。");
                return;
            }

            // --- プレビュー要求（mode=show） ---
            const showFd = new FormData();
            showFd.append("action", "bbs_quest_confirm");
            showFd.append("mode", "show");
            showFd.append("draft_id", String(lastDraftId));
            if (window.bbs_confirm_vars?.nonce) showFd.append("nonce", bbs_confirm_vars.nonce);

            const showRes = await fetch(AJAX_URL, {
                method: "POST",
                body: showFd,
                credentials: "same-origin"
            });

            if (!showRes.ok) {
                const txt = await showRes.text().catch(() => "");

                // console.error("Preview Error Details:", txt);

                alert(`プレビュー取得に失敗しました。時間をおいて再度お試しください。`);
                return;
            }

            const showJson = await showRes.json().catch(() => {
                alert("プレビュー応答(JSON)の解析に失敗しました。");
                return null;
            });
            if (!showJson) return;

            if (!showJson || showJson.success !== true) {
                const msg = showJson?.data?.errors?.[0] || showJson?.data?.message || "プレビューの取得に失敗しました。";
                alert(msg);
                return;
            }

            // ここで data を作る（※ここは今のままでOK）
            const data = showJson.data?.data ?? showJson.data ?? {};
            window.lastPreviewData = {
                title: data.title ?? '',
                text: data.text ?? '',
                name: data.name ?? '',
                stamp: data.stamp
            };

            // === ここから「確認画面」描画 ===
            change2();
            confirm_area.classList.remove('hideItems');
            confirm_area.style.display = 'block';
            input_area.style.display = 'none';
            confirm_area.textContent = '';

            document.body.classList.add('is-confirm'); // ←★ここ

            // ===== スロットの実際の選択状況から「メディア」と「ユーザーアイコン」を切り分ける =====

            // 1) まず safeFiles を作る（空/null/文字列"null"を排除）
            const getExt = (name) => (String(name || '').split('.').pop() || '').toLowerCase();
            const safeFiles = Array.isArray(data.files) ?
                data.files.filter(f => f && typeof f === 'string' && f.trim() !== '' && f !== 'null') : [];

            // 2) スロットの選択状況を DOM から取得
            //    先頭3つが「動画・画像」、4つ目が「画像アイコン」の前提（あなたのHTMLに合わせています）
            const attachInputs = document.querySelectorAll(
                'input.attach[type="file"], input.attach-icon[type="file"]'
            );
            // 4スロット想定：0..2 = media, 3 = icon（3番目が存在しない場合もあるのでガード）
            const slotSelected = [false, false, false, false];
            for (let i = 0; i < 4; i++) {
                const inp = attachInputs[i];
                slotSelected[i] = !!(inp && inp.files && inp.files.length > 0);
            }

            // 3) safeFiles を “スロット順” に順当に割り当てる
            //    例：slotSelected = [true,true,false,true] かつ safeFiles = ['A','B','C']
            //    → slot0='A', slot1='B', slot2=未選択, slot3='C' という割当になる
            const slotFiles = [null, null, null, null];
            let cursor = 0;
            for (let i = 0; i < 4; i++) {
                if (slotSelected[i] && cursor < safeFiles.length) {
                    slotFiles[i] = safeFiles[cursor++];
                }
            }
            // ここで slotFiles[0..2] がメディア側、slotFiles[3] がアイコン側の「実際のファイル名（tmp名）」になる

            // 4) media と icon に分解（null を除外）
            const mediaFiles = (Array.isArray(slotFiles) ? slotFiles.slice(0, 3) : []).filter(Boolean);
            const userIconName = slotFiles[3] || null;

            // --- ここから先は “並び描画” のロジックは今までのままでOK ---
            // （2件のときは 1段目：media[0] + 質問文、2段目：media[1] + 空白、というあなたの既存処理で固定表示になります）

            // メディア要素を作るヘルパー（確認画面用）
            const makeMediaEl = (fname) => {
                const ext = getExt(fname);
                const url = tmpGetUrl(fname);

                // 外側のラッパ
                const wrap = document.createElement('div');
                wrap.className = 'confirm-media-wrap'; // ←CSSでサイズ指定

                let el = null;

                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    el = document.createElement('img');
                    el.alt = fname;
                } else {
                    return null;
                }

                /* } else if (ext === 'pdf') {
                    el = document.createElement('iframe');
                    el.title = 'PDF preview'; */

                el.src = url;
                el.className = 'confirm-media-content'; // ←CSSで 530×350 を指定
                wrap.appendChild(el);

                return wrap;
            };

            // --- confirm用カルーセルDOM生成 ---
            function buildConfirmCarousel(fileNames) {
                const total = Math.max(1, fileNames.length);

                const area = document.createElement('div');
                area.className = 'confirm-carousel-area';

                // ✅ 上帯 PREV / NEXT（実体ボタン：クリックできる）
                const topPrev = document.createElement('button');
                topPrev.type = 'button';
                topPrev.className = 'confirm-topnav confirm-topnav-prev';
                topPrev.setAttribute('aria-label', 'prev');
                topPrev.textContent = 'PREV';

                const topNext = document.createElement('button');
                topNext.type = 'button';
                topNext.className = 'confirm-topnav confirm-topnav-next';
                topNext.setAttribute('aria-label', 'next');
                topNext.textContent = 'NEXT';

                area.appendChild(topPrev);
                area.appendChild(topNext);

                // ★追加：CSSへ枚数を渡す
                area.style.setProperty('--n', total);

                const track = document.createElement('div');
                track.className = 'confirm-carousel-track';
                track.id = 'confirm_carousel_track';
                // track.style.setProperty('--w', (total * 100) + '%');

                // スライド本体
                if (fileNames.length === 0) {
                    const ph = document.createElement('div');
                    ph.className = 'confirm-carousel-slide confirm-carousel-placeholder';
                    ph.textContent = '1';
                    track.appendChild(ph);
                } else {
                    const slideW = 100 / total; // ← 追加（totalに応じて1枚の幅を決める）

                    fileNames.forEach((fname) => {
                        const slide = document.createElement('div');
                        slide.className = 'confirm-carousel-slide';

                        // ✅ 追加：1枚の幅を 100/total %
                        slide.style.flex = `0 0 ${slideW}%`;

                        const inner = document.createElement('div');
                        inner.className = 'confirm-carousel-inner';

                        const media = makeMediaEl(fname);
                        if (media) inner.appendChild(media);

                        slide.appendChild(inner);
                        track.appendChild(slide);
                    });
                }

                // prev / next（左右グレーパネルのボタン）
                const prev = document.createElement('button');
                prev.type = 'button';
                prev.id = 'confirm_prev';
                prev.className = 'confirm-carousel-prev';
                prev.setAttribute('aria-label', 'prev');

                const next = document.createElement('button');
                next.type = 'button';
                next.id = 'confirm_next';
                next.className = 'confirm-carousel-next';
                next.setAttribute('aria-label', 'next');

                // indicator
                const indicator = document.createElement('ul');
                indicator.id = 'confirm_indicator';
                indicator.className = 'confirm-carousel-indicator';

                for (let i = 0; i < total; i++) {
                    const li = document.createElement('li');
                    li.className = 'confirm-indicator-dot';
                    indicator.appendChild(li);
                }

                area.appendChild(track);
                area.appendChild(prev);
                area.appendChild(next);
                area.appendChild(indicator);

                return area;
            }

            // --- confirm用カルーセル挙動（single-que_list.php のロジックを移植） ---
            function initConfirmCarousel() {
                const track = document.getElementById('confirm_carousel_track');
                const prev = document.getElementById('confirm_prev');
                const next = document.getElementById('confirm_next');
                const indicator = document.getElementById('confirm_indicator');

                // ✅ 上帯ボタン（buildConfirmCarouselで作った実体）
                const area = document.querySelector('#confirm_area .confirm-carousel-area');
                const topPrev = area ? area.querySelector('.confirm-topnav-prev') : null;
                const topNext = area ? area.querySelector('.confirm-topnav-next') : null;

                if (!track || !prev || !next || !indicator) return;

                const dots = indicator.querySelectorAll('.confirm-indicator-dot');
                const totalSlides = Math.max(1, dots.length);
                let current = 0;

                function updateDots() {
                    dots.forEach((d, i) => d.classList.toggle('is-active', i === current));
                }

                function goTo(idx) {
                    current = (idx + totalSlides) % totalSlides;

                    const step = 100 / totalSlides; // 50, 33.333..., 25...
                    // const vSlidePercent = current * step; // ← 0, 33.33, 66.66...

                    track.style.transform = `translateX(-${current * step}%)`;
                    updateDots();
                }

                // 左右グレーパネル
                prev.addEventListener('click', () => goTo(current - 1));
                next.addEventListener('click', () => goTo(current + 1));

                // ✅ 上帯PREV/NEXTも同じ挙動
                if (topPrev) topPrev.addEventListener('click', () => goTo(current - 1));
                if (topNext) topNext.addEventListener('click', () => goTo(current + 1));

                // ドット
                dots.forEach((d, i) => d.addEventListener('click', () => goTo(i)));

                // 初期
                goTo(0);
            }

            // ===== 1. 先頭エリア：添付 + 質問文 =====
            // 2カラムのグリッドを作成
            // const firstGrid = document.createElement('div');
            // ↓ この1行を追加
            // firstGrid.classList.add('confirm-first-grid');

            // 質問文ボックス
            const makeTextBox = () => {
                const box = document.createElement('div');
                box.className = 'confirm-text-box'; // ★これを追加

                const body = document.createElement('div');
                body.style.whiteSpace = 'pre-wrap';
                body.textContent = data.text ?? '';
                // box.appendChild(ttl);
                box.appendChild(body);
                return box;
            };

            // ===== 1. 添付（カルーセル） =====
            // mediaFiles は既に作ってある想定。もし未定義なら slotFiles から作る：
            try {
                console.log('BEFORE build/append', {
                    mediaFiles,
                    confirm_area
                });

                if (mediaFiles.length > 0) {
                    const carouselEl = buildConfirmCarousel(mediaFiles);
                    confirm_area.appendChild(carouselEl);
                    initConfirmCarousel();
                }

                console.log('AFTER initConfirmCarousel() call');

            } catch (e) {
                console.error('CONFIRM RENDER ERROR', e);
                throw e;
            }

            // ===== 2. 質問タイトル（スライダーの下に表示） =====
            const titleRow = document.createElement('div');
            titleRow.classList.add('confirm-title-row'); // ← ★追加
            titleRow.style.display = 'grid';
            titleRow.style.gridTemplateColumns = '1fr auto';
            titleRow.style.alignItems = 'center';
            titleRow.style.gap = '12px';
            titleRow.style.marginTop = '14px';

            const titleBox = document.createElement('div');
            titleBox.classList.add('confirm-title-box'); // ★ 追加
            // ラベル「質問タイトル」は出さない
            // const tHdr = document.createElement('div');
            // tHdr.textContent = '質問タイトル';
            // tHdr.style.fontWeight = 'bold';
            const tBody = document.createElement('div');
            tBody.textContent = data.title ?? '';
            tBody.classList.add('confirm-title-text'); // ★ 追加
            // titleBox.appendChild(tHdr);
            titleBox.appendChild(tBody);
            titleRow.appendChild(titleBox);

            confirm_area.appendChild(titleRow);

            // ===== 3. 画像アイコン + 名前 =====
            const userRow = document.createElement('div');
            userRow.classList.add('confirm-user-row'); // ★親にクラスだけ付ける
            // userRow.style.display = 'grid';
            // userRow.style.gridTemplateColumns = 'auto 1fr';
            // userRow.style.alignItems = 'center';
            // userRow.style.gap = '12px';
            // userRow.style.marginTop = '14px';

            // アイコン
            const iconWrap = document.createElement('div');
            iconWrap.classList.add('confirm-usericon-wrap'); // ★ここで div にクラス
            const iconImg = document.createElement('img');
            iconImg.classList.add('confirm-usericon-img'); // ★imgにもクラス
            // iconImg.style.width = '90px';
            // iconImg.style.height = '90px';
            iconImg.style.objectFit = 'cover';
            <?php if (isset($noimage_url)) : ?>
                iconImg.src = <?php echo json_encode(esc_url($noimage_url)); ?>;
            <?php else : ?>
                iconImg.src = '';
            <?php endif; ?>

            if (userIconName && /\.(jpe?g|png)$/i.test(userIconName)) {
                iconImg.src = tmpGetUrl(userIconName);
            }

            iconWrap.appendChild(iconImg);
            userRow.appendChild(iconWrap);

            // 名前
            const nameBox = document.createElement('div');
            nameBox.classList.add('confirm-name-box'); // ← 外側（必要なら）
            // ラベル「名前」は出さない
            // const nHdr = document.createElement('div');
            // nHdr.textContent = '名前';
            // nHdr.style.fontWeight = 'bold';
            const nBody = document.createElement('div');
            nBody.classList.add('confirm-name-text'); // ← ★これが「<div>名前テスト</div>」に付くクラス
            nBody.textContent = data.name || '匿名';
            // nameBox.appendChild(nHdr);
            nameBox.appendChild(nBody);
            userRow.appendChild(nameBox);

            confirm_area.appendChild(userRow);

            // ★① dataの中身を確認（ここ）
            console.log('CONFIRM data:', data);
            console.log('stamp value:', data.stamp);

            // ===== 4. 質問文 =====
            const confirmTextBox = makeTextBox();
            confirm_area.appendChild(confirmTextBox);

            // ===== 5. スタンプ画像 =====
            // ★ここを修正：スタンプ番号→URL をマップから引く
            // makeTextBox() が返す要素（confirmTextBox）を作った直後にこれを入れる
            // ===== 5. スタンプ画像 =====
            if (data.stamp) {

                // ✅ confirmTextBox の「本文div（white-space: pre-wrap）」を取る
                const textDiv = confirmTextBox.querySelector('div[style*="white-space"]') ||
                    confirmTextBox.querySelector('div');

                if (textDiv) {

                    // ✅ 既にスタンプがあれば消す（重複防止）
                    const old = textDiv.querySelector('img.confirm-stamp');
                    if (old) old.remove();

                    // ✅ スタンプ生成（※ここは const でOK。二重宣言しない）
                    const stampImg = document.createElement('img');
                    stampImg.className = 'confirm-stamp';
                    stampImg.src =
                        "<?php echo esc_url(get_template_directory_uri()); ?>/images/stamp/" +
                        String(data.stamp) +
                        ".png";
                    stampImg.alt = 'stamp ' + data.stamp;

                    // ✅ 重要：本文divの「先頭」に入れる（float が効きやすい）
                    textDiv.insertBefore(stampImg, textDiv.firstChild);
                }
            }

            // 追加直後：本当に追加されたか
            // console.log('stamp appended?', confirm_area.contains(stampImg));
            // console.log('stamp element:', stampImg.outerHTML);
            // console.log('imgs AFTER stamp:', confirm_area.querySelectorAll('img').length);

            // すぐ後で消されてないか（再描画チェック）
            setTimeout(() => {
                console.log('AFTER 0ms .confirm-stamp count:', document.querySelectorAll('.confirm-stamp').length);
            }, 0);

            setTimeout(() => {
                console.log('AFTER 300ms .confirm-stamp count:', document.querySelectorAll('.confirm-stamp').length);
            }, 300);

            // ★③ append後のDOM確認
            console.log(
                '③ confirm_area imgs:',
                confirm_area.querySelectorAll('img').length
            );

            // ===== 投稿前チェック =====
            const wrap = document.createElement('div');
            wrap.className = 'confirm-checklist';

            // タイトル
            const title = document.createElement('h3');
            title.textContent = '投稿前チェック';
            wrap.appendChild(title);

            // チェック1
            const label1 = document.createElement('label');
            const check1 = document.createElement('input');
            check1.type = 'checkbox';
            check1.className = 'precheck';
            label1.appendChild(check1);
            label1.appendChild(document.createTextNode(' 他人の作品の無断転載や著作権・肖像権侵害をしていません'));
            wrap.appendChild(label1);

            // チェック2
            const label2 = document.createElement('label');
            const check2 = document.createElement('input');
            check2.type = 'checkbox';
            check2.className = 'precheck';
            label2.appendChild(check2);
            label2.appendChild(document.createTextNode(' 誹謗中傷・差別・個人情報・生成AI作品を含んでいません'));
            wrap.appendChild(label2);

            // チェック3
            const label3 = document.createElement('label');
            const check3 = document.createElement('input');
            check3.type = 'checkbox';
            check3.className = 'precheck';
            label3.appendChild(check3);
            label3.appendChild(document.createTextNode(' 利用規約に同意します'));
            wrap.appendChild(label3);

            // 注意文
            const note = document.createElement('p');
            note.textContent = '※ 詳しくは「質問・雑談掲示板 利用規約」をご確認ください';
            note.style.fontSize = '18px';
            // note.style.color = '#777';
            note.style.marginTop = '8px';
            note.style.paddingLeft = 'calc(1.4em + 8px)';
            wrap.appendChild(note);

            // 追加
            confirm_area.appendChild(wrap);

            // ===== 6. 戻る／確定ボタン =====
            confirm_area.appendChild(create_button_parts(1));

            // ▼ここ追加
            const confirmBtn = document.getElementById('confirm_button');
            // 投稿前チェック全部入るまでボタン無効
            const checks = document.querySelectorAll('.precheck');

            if (confirmBtn) confirmBtn.disabled = true;

            function updateConfirmButton() {
                const allChecked = Array.from(checks).every(c => c.checked);
                if (confirmBtn) confirmBtn.disabled = !allChecked;
            }

            checks.forEach(c => {
                c.addEventListener('change', updateConfirmButton);
            });
            // === ここまで「確認画面」描画 ===

        } catch (err) {
            console.error(err);
            const isNetwork = (err instanceof TypeError) && /fetch|network|failed/i.test(String(err && err.message));
            if (isNetwork) {
                alert("通信に失敗しました。時間をおいて再度お試しください。");
            } else {
                alert("画面処理中にエラーが発生しました。コンソール(F12)に表示されるエラー内容をご確認ください。");
            }

        } finally {
            toggleLoading(btn, false);
        }
    }

    // トースト生成関数
    function showPostToast(message) {
        const old = document.querySelector('.bbs-toast');
        if (old) old.remove();

        const toast = document.createElement('div');
        toast.className = 'bbs-toast';

        const icon = document.createElement('div');
        icon.className = 'bbs-toast_icon';

        const text = document.createElement('div');
        text.className = 'bbs-toast_text';
        text.textContent = message;

        toast.appendChild(icon);
        toast.appendChild(text);
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('is-show');
        });

        setTimeout(() => {
            toast.classList.remove('is-show');
            setTimeout(() => {
                toast.remove();
            }, 250);
        }, 800);
    }

    /* ------------------------------
     * 確定（confirm → bbs_quest_confirm）
     * ------------------------------ */
    async function confirm_button_click() {
        const btn = document.getElementById('confirm_button');
        toggleLoading(btn, true);

        try {
            if (!lastDraftId) {
                alert("確認用のドラフトIDが取得できていません。先に『確認へ進む』を押してください。");
                return;
            }

            const fd = new FormData();
            fd.append("action", "bbs_quest_confirm");
            fd.append("mode", "commit");
            fd.append("draft_id", String(lastDraftId));
            if (window.bbs_confirm_vars?.nonce) {
                fd.append("nonce", bbs_confirm_vars.nonce);
            }

            const res = await fetch(AJAX_URL, {
                method: "POST",
                body: fd,
                credentials: "same-origin",
            });

            if (!res.ok) {
                const txt = await res.text().catch(() => "");

                // 開発確認用。本番では消す or DEBUG時だけ出す
                // console.error("Server Error Details:", txt);

                throw new Error(`サーバーエラーが発生しました (${res.status})`);
            }

            let json;
            try {
                json = await res.json();
            } catch {
                alert("確定応答(JSON)の解析に失敗しました。");
                return;
            }

            if (!json || json.success !== true) {
                const msg = json?.data?.errors?.[0] || json?.data?.message || "確定に失敗しました。";
                alert(msg);
                return;
            }

            // 成功トーストを出してから一覧へ遷移
            showPostToast("投稿しました");
            setTimeout(() => {
                window.location.href = window.bbs_confirm_vars.list_url;
            }, 900);

        } catch (err) {
            console.error(err);
            const isNetwork = (err instanceof TypeError) && /fetch|network|failed/i.test(String(err && err.message));
            if (isNetwork) {
                alert("通信に失敗しました。時間をおいて再度お試しください。");
            } else {
                alert("画面処理中にエラーが発生しました。コンソール(F12)に表示されるエラー内容をご確認ください。");
            }
        } finally {
            toggleLoading(btn, false);
        }
    }

    // ❶ スクリプトタグが実際に読み込まれたか
    console.log('[BBS] script tag LOADED');

    // ❷ validation 定義の直前と直後
    console.log('[BBS] defining validation...');

    /* function validation() {
        console.log('[BBS] validation CALLED');
        // ...（あなたの元の中身）...
    } */
    console.log('[BBS] validation DEFINED =', typeof validation);
    /* ------------------------------
     * 初期化
     * ------------------------------ */
    function init() {
        console.log('[BBS] init START');

        // 🔽 ここにデバッグ出力を入れる
        console.log('attach=', document.querySelectorAll('input.attach[type="file"]').length);
        console.log('viewer=', document.querySelectorAll('.viewer').length);
        console.log('fileAreas=', document.querySelectorAll('.image-camera-icon, .usericon-uploads').length);

        // 🔽★ ここに追加：ファイルアップロードのイベント登録
        if (typeof set_attach_event === 'function') {
            // 1〜3番目: .image-camera-icon / 4番目(アイコン): .usericon-uploads
            // 第2引数 3 は「4つめスロットがユーザーアイコン」の意味
            set_attach_event('.image-camera-icon, .usericon-uploads', 3);
        }
        // 🔼★ここまで追加

        const submitBtn = document.getElementById('submit_button');
        console.log('[BBS] submit_button =', submitBtn);
        if (submitBtn) {
            submitBtn.addEventListener('click', () => console.log('[BBS] submit_button CLICKED'));
            submitBtn.addEventListener('click', validation); // 任意（押下時に再判定したいなら）
            submitBtn.addEventListener('click', submit_button_click); // ★これが必須！
        }



        document.addEventListener('input', (e) => {
            console.log('[BBS] input EVENT on', e.target?.id || e.target?.name || e.target?.tagName);
            try {
                display_text_length(e); // ←★ 追加：文字数カウンタ更新	
                validation(); // ←既存の送信可否チェック
            } catch (err) {
                console.error('[BBS] validation threw', err);
            }
        });

        // 🔽★ ここを追記：スタンプ（ラジオ）変更でもバリデーションを再実行
        const stampRadios = document.querySelectorAll('input[name="stamp"]');
        stampRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                console.log('[BBS] stamp change detected');
                validation();
            });
        });

        // ★ 3) 初期表示時にもカウンタを一度だけ更新
        ['text', 'title', 'name'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                console.log('[BBS] init display_text_length for ${id}');
                display_text_length({
                    target: el
                });
            }
        });

        // ★ 4) 初期バリデーション
        try {
            validation();
        } catch (err) {
            console.error('[BBS] validation threw on init', err);
        }

        console.log('[BBS] init END');
    }

    // ❹ DOM 準備時に init が本当に走ったか
    window.addEventListener('DOMContentLoaded', () => {
        console.log('[BBS] DOMContentLoaded');
        try {
            init();
        } catch (err) {
            console.error('[BBS] init threw', err);
        }
    });

    // ❺ 念のため、1秒後にも強制呼び
    setTimeout(() => {
        console.log('[BBS] force-call validation (timeout)');
        try {
            validation();
        } catch (err) {
            console.error('[BBS] validation threw (timeout)', err);
        }
    }, 1000);
</script>