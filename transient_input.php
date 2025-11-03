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
<style>
    /* ステップ表示用の簡易スタイル */
    .step_img {
        height: 364px;
        width: 36px;
    }

    .hideItems {
        display: none;
    }

    .concealItems {
        display: none;
    }

    /* ローディング中の簡易スピナー（ボタンに付与） */
    .wait {
        height: 40px;
        width: 40px;
        border-radius: 40px;
        border: 3px solid;
        border-color: #bbbbbb;
        border-left-color: #1ECD97;
        font-size: 0;
        animation: rotating 2s 0.25s linear infinite;
    }

    @keyframes rotating {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
</style>
<div class="board_form_partial" id="js_board_form_partial"><!-- 全体ラッパ -->
    <div class="questionHeader-partial"><!-- 画面上部の見出し -->
        <h2>
            <span class="fa-stack">
                <i class="fa fa-circle fa-stack-2x w-circle"></i>
                <i class="fa-stack-1x fa-inverse q">Q</i>
            </span>
            <span class="q-text" id="q_text"></span><!-- ステップ名 -->
        </h2>
        <div class="other_step">
            <img id="step_img" alt=""><!-- ステップ画像 -->
        </div>
    </div>

    <div id="input_area"><!-- 入力エリア -->
        <form id="input_form" method="post" name="input_form" enctype="multipart/form-data"><!-- ファイル送信用にenctype指定 -->
            <div class="image-partial"><!-- 添付ファイル群 -->
                <h2>
                    動画・画像をアップロード (Upload video / image)
                    <span class="required">※1ファイル最大5MB・合計最大20MB、JPG/PNG/PDF/MP4</span><!-- サーバ設定に合わせた案内 -->
                </h2>

                <!-- 1つ目の添付 -->
                <div class="image-selector-button">
                    <label>
                        <div class="image-camera-icon">
                            <img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px" alt="select file">
                        </div>
                        <!-- サーバ許可に合わせて accept を指定（gif は除外） -->
                        <input type="file" class="attach" name="attach[]" accept=".jpg,.jpeg,.png,.pdf,.mp4" style="display:none;">
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
                        <input type="file" class="attach" name="attach[]" accept=".jpg,.jpeg,.png,.pdf,.mp4" style="display:none;">
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
                        <input type="file" class="attach" name="attach[]" accept=".jpg,.jpeg,.png,.pdf,.mp4" style="display:none;">
                    </label>
                    <div class="viewer" style="display:none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
            </div><!-- /image-partial -->

            <div class="body-partial-parts"><!-- 本文 -->
                <h2>質問文 (question)<span class="required">※必須</span></h2>
                <div class="parts">
                    <!-- サーバ上限に合わせ、data-* は任意で利用 -->
                    <textarea class="input" name="text" id="text"
                        data-length="<?php echo defined('MAX_LENGTH::TEXT') ? MAX_LENGTH::TEXT : 5000; ?>"
                        data-minlength="<?php echo defined('MIN_LENGTH::TEXT') ? MIN_LENGTH::TEXT : 1; ?>"
                        placeholder="荒らし行為や誹謗中傷や著作権の侵害はご遠慮ください"></textarea>
                    <div class="msg_partial"></div> <!-- ←★ここに class を追加 -->
                </div>
            </div>

            <div class="title-partial-parts"><!-- タイトル -->
                <h2>質問タイトル (title)<span class="required">※必須</span></h2>
                <div class="parts">
                    <input class="input" type="text" name="title" id="title"
                        data-length="<?php echo defined('MAX_LENGTH::TITLE') ? MAX_LENGTH::TITLE : 200; ?>"
                        data-minlength="<?php echo defined('MIN_LENGTH::TITLE') ? MIN_LENGTH::TITLE : 1; ?>"
                        placeholder="<?php echo defined('MIN_LENGTH::TITLE') ? MIN_LENGTH::TITLE : 1; ?>文字以上で入力してください">
                    <div class="msg_partial"></div> <!-- ←★ここも同様 -->
                </div>
            </div>

            <div class="stamp-partial">
                <h2>スタンプを選ぶ(必須)</h2>
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
                <h2>画像アイコン (image icon)<span class="required">※任意</span></h2>
                <div class="usericon-thumbnail-button">
                    <label>
                        <div class="usericon-uploads">
                            <img src="<?php echo $noimage_url; ?>" class="changeImg" style="height:90px;width:90px" alt="user icon">
                        </div>
                        <input type="file" class="attach" name="attach[]" accept=".jpg,.jpeg,.png" style="display:none;">
                    </label>
                    <div class="viewer" style="display:none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
            </div>

            <div class="name-partial-parts"><!-- 名前 -->
                <h2>名前 (name)<span class="required">※任意</span></h2>
                <div class="parts">
                    <input class="input" type="text" name="name" id="name"
                        data-length="<?php echo defined('MAX_LENGTH::NAME') ? MAX_LENGTH::NAME : 50; ?>"
                        data-minlength="<?php echo defined('MIN_LENGTH::NAME') ? MIN_LENGTH::NAME : 0; ?>"
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
    };
</script>
<script>
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
        if (!btn) return;
        if (isLoading) {
            btn.disabled = true;
            btn.classList.add('wait');
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

        if (titleEl) titleEl.value = data.title ?? '';
        if (textEl) textEl.value = data.text ?? '';
        if (nameEl) nameEl.value = data.name ?? '';

        // スタンプ（1..8想定）
        if (typeof data.stamp !== 'undefined') {
            const s = document.querySelector(`input[name="stamp"][value="${String(data.stamp)}"]`);
            if (s) s.checked = true;
        }

        // カウンタやバリデーション再評価
        if (typeof validation === 'function') validation();
    }

    // すべての添付をクリア（本当に input の値を空にするのが重要）
    function clearAllAttachments() {
        document.querySelectorAll('input.attach[type="file"]').forEach(inp => {
            inp.value = '';
        });
        // プレビューUIを消す（あれば）
        document.querySelectorAll('.upload-slot .preview, .preview-thumbs').forEach(p => {
            p.innerHTML = '';
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
        // id が text / title / name のときだけ処理
        if (!e || !e.target || !['text', 'title', 'name'].includes(e.target.id)) return;

        const el = e.target;
        const msg = el.nextElementSibling; // 各 input/textarea の直後の <div class="msg_partial">
        if (!msg) return;

        // maxlength / minlength は data-* と HTML 属性のどちらでもOKにする
        // ← ココが重要：data-length / data-minlength も確実に拾う
        const getMax = (el) => {
            const a = parseInt(el.getAttribute('maxlength') || '0', 10) || 0;
            const d = parseInt(el.dataset.length || '0', 10) || 0; // data-length
            return a || d; // どちらか入っていればOK
        };
        const getMin = (el) => {
            const a = parseInt(el.getAttribute('minlength') || '0', 10) || 0;
            const d = parseInt(el.dataset.minlength || '0', 10) || 0; // data-minlength
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
     *  - 添付の軽いクライアントチェック（最大4件・5MB/件）
     *  ※最終判定はサーバー側で必ず再検証
     * ------------------------------------- */
    function validation() {
        const btn = document.getElementById('submit_button');
        if (!btn) return;

        const titleEl = document.getElementById('title');
        const textEl = document.getElementById('text');

        // ★ dataset だけでなく HTML 属性の maxlength/minlength も見る
        const getLimit = (el, name, fallback = '0') =>
            parseInt(el?.getAttribute(name) || el?.dataset?.[name] || fallback, 10) || 0;

        const titleMax = getLimit(titleEl, 'maxlength'); // maxlength or data-length
        const titleMin = getLimit(titleEl, 'minlength'); // minlength or data-minlength
        const textMax = getLimit(textEl, 'maxlength');
        const textMin = getLimit(textEl, 'minlength');

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

        const titleOk = (titleMin ? titleLen >= titleMin : true) && (titleMax ? titleLen <= titleMax : true);
        const textOk = (textMin ? textLen >= textMin : true) && (textMax ? textLen <= textMax : true);

        // 添付ざっくりチェック（最終判定はサーバ）
        const inputs = document.querySelectorAll('input.attach[type="file"]');
        let filesCount = 0;
        let clientFileOk = true;
        const MAX_FILES = 4;
        const MAX_PER = 5 * 1024 * 1024; // 5MB/ファイル（必要なら調整）

        inputs.forEach(f => {
            if (f.files?.length) {
                filesCount += f.files.length;
                for (const file of f.files)
                    if (file.size > MAX_PER) clientFileOk = false;
            }
        });
        if (filesCount > MAX_FILES) clientFileOk = false;

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
    const AJAX_URL =
        (window.bbs_vars?.ajax_url) ||
        (window.bbs_confirm_vars?.ajax_url) ||
        "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";

    /* ------------------------------
     * グローバル: draft_id
     * ------------------------------ */
    let lastDraftId = null; // ← ここで 1 回だけ定義（以降は上書きのみ）

    /* ------------------------------
     * 添付ファイル安全版イベント関数
     * ------------------------------ */
    function set_attach_event(fileAreaSelector, usericonIndex) {
        // 許可する拡張子とMIME（最低限のクライアント側バリデーション。最終判定はサーバ）
        const ALLOWED = {
            'image': ['image/jpeg', 'image/png'],
            'video': ['video/mp4'],
            'pdf': ['application/pdf']
        };
        const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'mp4', 'pdf'];

        // スロット別の最大サイズ(MB)
        const MAX_MB_USERICON = 5;
        const MAX_MB_DEFAULT = 15;

        // 各スロットに紐づく一時URLを覚えておいて clear 時に解放する
        const urlBucket = new Map(); // key: input[type=file] element, value: Array<objectURL>

        // 要素の収集
        // 各スロットの並び順は input.attach[type="file"] / .viewer / .image-camera-icon or .usericon-uploads が同じインデックスで並んでいる前提です。ズレていると表示がおかしくなるので、HTML側の順番をそろえてください。
        const attachInputs = document.querySelectorAll('input.attach[type="file"]');
        const viewers = document.querySelectorAll('.viewer');

        // 「カメラ画像エリア」（=ファイル未選択時に見えているエリア）
        // fileAreaSelector には '.image-camera-icon,.usericon-uploads' のようにカンマ区切りで渡してください
        const fileAreas = document.querySelectorAll(fileAreaSelector);

        // clear ボタン（各スロットに1つ想定）
        const clearBtns = document.querySelectorAll('.attachclear');

        // objectURL を安全に解放
        const revokeAllFor = (inp) => {
            const list = urlBucket.get(inp);
            if (Array.isArray(list)) {
                for (const u of list) {
                    try {
                        URL.revokeObjectURL(u);
                    } catch {}
                }
            }
            urlBucket.set(inp, []);
        };

        // シンプルな拡張子取得
        const getExt = (name) => (name.split('.').pop() || '').toLowerCase();

        // クイックな MIME/拡張子チェック
        const isAllowed = (file) => {
            const ext = getExt(file.name);
            if (!ALLOWED_EXT.includes(ext)) return false;

            const type = String(file.type || '');
            if (!type) return false;

            if (type.startsWith('image/')) return ALLOWED.image.includes(type);
            if (type.startsWith('video/')) return ALLOWED.video.includes(type);
            if (type === 'application/pdf') return true; // 上で拡張子も見ているのでOK

            return false;
        };

        // スロット別の許可判定（usericonIndex は jpg/png のみ）
        const isAllowedForSlot = (slotIndex, file) => {
            const isIcon = (slotIndex === Number(usericonIndex));
            const name = String(file.name || '').toLowerCase();
            const type = String(file.type || '');

            if (isIcon) {
                // ユーザーアイコン枠は jpg/png のみ
                const okExt = /\.(jpg|jpeg|png)$/i.test(name);
                const okMime = type === 'image/jpeg' || type === 'image/png';
                return okExt && okMime;
            }
            // それ以外の枠は従来どおり（画像・動画・PDFを許可）
            return isAllowed(file);
        };

        // プレビュー描画
        const renderPreview = (slotIndex, file) => {
            const v = viewers[slotIndex];
            if (!v) return;

            // 既存プレビューはクリア（URLも解放）
            v.innerHTML = '';

            const url = URL.createObjectURL(file);

            // タイプごとに安全な要素を作成（autoplay なし、controls は video のみ）
            let el = null;
            if (file.type.startsWith('image/')) {
                el = document.createElement('img');
                el.alt = '';
            } else if (file.type === 'application/pdf') {
                el = document.createElement('iframe');
                el.setAttribute('title', 'PDF preview');
            } else if (file.type.startsWith('video/')) {
                el = document.createElement('video');
                el.setAttribute('controls', ''); // 再生はユーザー操作のみ
                el.preload = 'metadata';
            } else {
                return; // 想定外
            }

            // レイアウト（旧コード準拠）
            const isIcon = (slotIndex === Number(usericonIndex));
            el.style.height = isIcon ? '90px' : '301px';
            el.style.width = isIcon ? '90px' : '535px';
            if (el.tagName === 'VIDEO' || el.tagName === 'IMG') {
                el.style.objectFit = isIcon ? 'contain' : 'fill';
            }

            el.src = url;
            v.appendChild(el);
            v.style.display = 'block';

            // 生成URLを記憶
            const arr = urlBucket.get(attachInputs[slotIndex]) || [];
            arr.push(url);
            urlBucket.set(attachInputs[slotIndex], arr);
        };

        // スロットごとに処理を束ねる
        const setFileToSlot = (slotIndex, file) => {
            const inp = attachInputs[slotIndex];
            const fileArea = fileAreas[slotIndex];
            const viewer = viewers[slotIndex];

            if (!inp || !viewer || !fileArea) return;

            // サイズ上限
            const isIcon = (slotIndex === Number(usericonIndex));
            const maxBytes = (isIcon ? MAX_MB_USERICON : MAX_MB_DEFAULT) * 1024 * 1024;
            if (file.size > maxBytes) {
                alert(`ファイルサイズが上限(${isIcon ? MAX_MB_USERICON : MAX_MB_DEFAULT}MB)を超えています。`);
                return;
            }

            // タイプチェック（ユーザーアイコン枠だけ jpg/png 限定）
            if (!isAllowedForSlot(slotIndex, file)) {
                const isIcon = (slotIndex === Number(usericonIndex));
                if (isIcon) {
                    alert('サポートしていないファイル種別です（画像：jpg/pngのみ許可）。');
                } else {
                    alert('サポートしていないファイル種別です（画像：jpg/png、動画：mp4、PDFのみ許可）。');
                }
                return;
            }

            // カメラエリアを隠し、プレビューを描画
            fileArea.classList.add('hideItems');
            renderPreview(slotIndex, file);

            // 送信可否の再評価（あれば）
            if (typeof validation === 'function') validation();
        };

        // input[type=file] の change
        attachInputs.forEach((inp, idx) => {
            // 初期化
            urlBucket.set(inp, []);

            inp.addEventListener('change', () => {
                // 旧URLを解放
                revokeAllFor(inp);

                const file = inp.files && inp.files[0];
                const fileArea = fileAreas[idx];
                const viewer = viewers[idx];
                if (!file) {
                    // 何も選んでいない ⇒ カメラエリアを戻す
                    if (viewer) {
                        viewer.innerHTML = '';
                        viewer.style.display = 'none';
                    }
                    if (fileArea) fileArea.classList.remove('hideItems');
                    if (typeof validation === 'function') validation();
                    return;
                }
                setFileToSlot(idx, file);
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
                viewer.innerHTML = '';
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

            fa.addEventListener('drop', (e) => {
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
                setFileToSlot(idx, file);
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
        });
        wrap.appendChild(back);

        // 投稿確定ボタン
        const go = document.createElement('button');
        go.type = 'button';
        go.id = 'confirm_button';
        go.className = 'answer-following';
        go.textContent = 'この内容で投稿を確定する';
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

            // ← この位置に入れる
            console.log('files in form:',
                Array.from(document.querySelectorAll('input.attach[type="file"]'))
                .map(i => Array.from(i.files).map(f => f.name))
            );

            // ▼ ここから追加：file入力を確実に積む
            // ※ フォームに入っている場合でもブラウザ依存で漏れることがあるため保険で積み直し
            try {
                formData.delete('attach[]'); // 同名が既に入っていたら一旦クリア（無ければ無視される）

                document.querySelectorAll('input.attach[type="file"]').forEach(inp => {
                    if (inp.files && inp.files.length > 0) {
                        for (const f of inp.files)
                            formData.append('attach[]', f, f.name); // PHP側は $_FILES['attach'] を参照
                    }
                });

                // // デバッグするなら（あとで消してください）
                // for (const [k, v] of formData.entries()) {
                //   console.log('FD', k, v instanceof File ? `(file) ${v.name} ${v.size}B` : v);
                // }
            } catch (e) {
                console.warn('append files fallback failed:', e);
            }
            // ▲ ここまで追加

            const res = await fetch(AJAX_URL, {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            });

            if (!res.ok) {
                const txt = await res.text().catch(() => "");
                throw new Error(`HTTP ${res.status} ${res.statusText}\n${txt}`);
            }

            const json = await res.json().catch(() => {
                throw new Error("submit応答(JSON)の解析に失敗しました。");
            });

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
                alert(`プレビュー取得に失敗しました (HTTP ${showRes.status}).\n${txt}`);
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

            // === ここから UI 切替と描画（プレビュー成功後に行う） ===
            change2();

            // hideItems を外し、描画用に初期化
            confirm_area.classList.remove('hideItems');
            confirm_area.style.display = 'block';
            input_area.style.display = 'none';
            confirm_area.innerHTML = ''; // いったん空に

            // 見出し
            {
                const h = document.createElement('h3');
                h.textContent = 'この内容で投稿しますか？';
                confirm_area.appendChild(h);
            }

            // テキスト系（タイトル/本文/名前）
            {
                const meta = document.createElement('ul');
                meta.innerHTML = `
    <li><strong>タイトル</strong>：${esc(data.title)}</li>
    <li><strong>本文</strong>：<pre style="white-space:pre-wrap;margin:0">${esc(data.text)}</pre></li>
    <li><strong>お名前</strong>：${esc(data.name || '匿名')}</li>
  `;
                confirm_area.appendChild(meta);
            }

            // スタンプ画像のプレビュー（stamp1〜stamp8 を /images/stamp#.png として想定）
            if (data.stamp) {
                const stampBox = document.createElement('div');
                stampBox.className = 'confirm-stamp';
                stampBox.style.margin = '12px 0';

                const label = document.createElement('div');
                label.textContent = '選択したスタンプ';
                label.style.fontWeight = 'bold';
                stampBox.appendChild(label);

                const img = document.createElement('img');
                img.src = "<?php echo esc_url(get_template_directory_uri()); ?>/images/stamp" + String(data.stamp) + ".png";
                img.alt = 'stamp ' + data.stamp;
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'contain';
                stampBox.appendChild(img);

                confirm_area.appendChild(stampBox);
            }

            // 添付ファイルのプレビュー（/uploads/tmp に置いている前提）
            {
                const filesWrap = document.createElement('div');
                filesWrap.style.marginTop = '12px';

                const title = document.createElement('div');
                title.textContent = '添付ファイル';
                title.style.fontWeight = 'bold';
                filesWrap.appendChild(title);

                const grid = document.createElement('div');
                grid.style.display = 'grid';
                grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(220px, 1fr))';
                grid.style.gap = '12px';
                filesWrap.appendChild(grid);

                // PHP で tmp の公開URLを生成
                const TMP_BASE_URL = "<?php $u = wp_upload_dir();
                                        echo esc_url(trailingslashit($u['baseurl']) . 'tmp/'); ?>";
                const getExt = (name) => (String(name || '').split('.').pop() || '').toLowerCase();

                // サーバが返した配列から「空・null・'null'」を除外
                const safeFiles = Array.isArray(data.files) ?
                    data.files.filter(f => f && typeof f === 'string' && f.trim() !== '' && f !== 'null') :
                    [];

                safeFiles.forEach((fname) => {
                    // すでに http(s) で始まる場合はそれを優先（将来の仕様変更に強くするため）
                    const isUrl = /^https?:\/\//i.test(fname);
                    const url = isUrl ? fname : (TMP_BASE_URL + encodeURIComponent(fname));
                    const ext = getExt(fname);

                    const card = document.createElement('div');
                    card.style.border = '1px solid #ddd';
                    card.style.borderRadius = '8px';
                    card.style.padding = '8px';
                    card.style.background = '#fff';

                    let el = null;
                    if (['jpg', 'jpeg', 'png'].includes(ext)) {
                        el = document.createElement('img');
                        el.src = url;
                        el.alt = fname;
                        el.style.width = '100%';
                        el.style.height = '150px';
                        el.style.objectFit = 'cover';
                        el.loading = 'lazy';
                    } else if (ext === 'mp4') {
                        el = document.createElement('video');
                        el.src = url;
                        el.controls = true;
                        el.style.width = '100%';
                        el.style.height = '150px';
                    } else if (ext === 'pdf') {
                        el = document.createElement('iframe');
                        el.src = url;
                        el.width = '100%';
                        el.height = '150';
                        // PDF埋め込み可否はブラウザ依存。だめならリンクだけでも出す
                        const link = document.createElement('div');
                        link.style.marginTop = '6px';
                        link.innerHTML = `<a href="${url}" target="_blank" rel="noopener">PDFを別タブで開く</a>`;
                        card.appendChild(link);
                    } else {
                        // 想定外の拡張子はリンクのみ
                        const link = document.createElement('a');
                        link.href = url;
                        link.target = '_blank';
                        link.rel = 'noopener';
                        link.textContent = fname;
                        card.appendChild(link);
                    }

                    if (el) card.appendChild(el);
                    grid.appendChild(card);
                });

                confirm_area.appendChild(filesWrap);
            }

            // 戻る／確定ボタン（この1行でOK。二重に confirm_button を作らない）
            confirm_area.appendChild(create_button_parts(1));

        } catch (err) {
            console.error(err);
            alert("通信に失敗しました。時間をおいて再度お試しください。");

        } finally {
            toggleLoading(btn, false);
        }
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
                throw new Error(`HTTP ${res.status} ${res.statusText}\n${txt}`);
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

            // 完了UI
            change3();
            document.querySelectorAll('.post-button').forEach(el => el.style.display = "none");

            if (confirm_area) {
                confirm_area.textContent = "";
                const p = document.createElement('p');
                p.textContent = json.data?.message || "投稿が確定しました。ありがとうございました。";
                confirm_area.appendChild(p);

                if (json.data?.id) {
                    const idp = document.createElement('p');
                    idp.textContent = `受付番号: ${json.data.id}`;
                    confirm_area.appendChild(idp);
                }
            }
        } catch (err) {
            console.error(err);
            alert("通信に失敗しました。時間をおいて再度お試しください。");
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
                console.log(`[BBS] init display_text_length for ${id}`);
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