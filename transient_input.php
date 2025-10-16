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
                    <div></div>
                </div>
            </div>

            <div class="title-partial-parts"><!-- タイトル -->
                <h2>質問タイトル (title)<span class="required">※必須</span></h2>
                <div class="parts">
                    <input class="input" type="text" name="title" id="title"
                        data-length="<?php echo defined('MAX_LENGTH::TITLE') ? MAX_LENGTH::TITLE : 200; ?>"
                        data-minlength="<?php echo defined('MIN_LENGTH::TITLE') ? MIN_LENGTH::TITLE : 1; ?>"
                        placeholder="<?php echo defined('MIN_LENGTH::TITLE') ? MIN_LENGTH::TITLE : 1; ?>文字以上で入力してください">
                    <div></div>
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
                    <div></div>
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
    function display_text_length(e) {
        const el = e.target;
        if (!el.classList || !el.classList.contains('input')) return;
        const max = parseInt(el.dataset.length || '0', 10);
        if (!max) return;

        const len = el.value.length;
        const counter = el.nextElementSibling;
        if (!counter) return;

        if (len <= max) {
            const remain = max - len;
            counter.textContent = `残り ${remain} 文字`;
            counter.style.color = remain === 0 ? '#d9534f' : '';
        } else {
            counter.textContent = `超過 ${len - max} 文字`;
            counter.style.color = '#d9534f';
        }
        if (len > max) el.classList.add('is-over');
        else el.classList.remove('is-over');
    }

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

        const titleMax = parseInt(titleEl?.dataset.length || '0', 10);
        const titleMin = parseInt(titleEl?.dataset.minlength || '0', 10);
        const textMax = parseInt(textEl?.dataset.length || '0', 10);
        const textMin = parseInt(textEl?.dataset.minlength || '0', 10);

        const titleLen = (titleEl?.value || '').length;
        const textLen = (textEl?.value || '').length;

        const stamps = document.querySelectorAll('input[name="stamp"]');
        let stampOk = false;
        for (const s of stamps) {
            if (s.checked) {
                stampOk = true;
                break;
            }
        }

        const titleOk = titleLen >= titleMin && (titleMax ? titleLen <= titleMax : true);
        const textOk = textLen >= textMin && (textMax ? textLen <= textMax : true);

        const inputs = document.querySelectorAll('input.attach[type="file"]');
        let filesCount = 0;
        let clientFileOk = true;
        const MAX_FILES = 4;
        const MAX_PER = 5 * 1024 * 1024;

        inputs.forEach(f => {
            if (f.files?.length) {
                filesCount += f.files.length;
                for (const file of f.files) {
                    if (file.size > MAX_PER) clientFileOk = false;
                }
            }
        });
        if (filesCount > MAX_FILES) clientFileOk = false;

        btn.disabled = !(titleOk && textOk && stampOk && clientFileOk);
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
     * 送信（submit → bbs_quest_submit）
     * ------------------------------ */
    async function submit_button_click() {
        const btn = document.getElementById("submit_button");
        toggleLoading(btn, true);
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
                    for (const f of inp.files) {
                        formData.append('attach[]', f, f.name); // PHP側は $_FILES['attach'] を参照
                    }
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

        let json;
        try {
            json = await res.json();
        } catch {
            throw new Error("submit応答(JSON)の解析に失敗しました。");
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

        // 画面を確認状態へ
        change2();
        confirm_area.appendChild(create_button_parts(1)); // ← 戻る/進むボタンが生える
        input_area.style.display = "none";
        confirm_area.style.display = "block";
        confirm_area.textContent = "";

        // --- プレビュー要求（mode=show） ---
        const showFd = new FormData();
        showFd.append("action", "bbs_quest_confirm");
        showFd.append("mode", "show");
        showFd.append("draft_id", String(lastDraftId));
        if (window.bbs_confirm_vars?.nonce) {
            showFd.append("nonce", bbs_confirm_vars.nonce);
        }

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

        let showJson;
        try {
            showJson = await showRes.json();
        } catch {
            alert("プレビュー応答(JSON)の解析に失敗しました。");
            return;
        }

        if (!showJson || showJson.success !== true) {
            const msg = showJson?.data?.errors?.[0] || showJson?.data?.message || "プレビューの取得に失敗しました。";
            alert(msg);
            return;
        }

        // ここで data を作る
        // すでにあるはず：const data = showJson.data?.data ?? showJson.data ?? {};
        window.lastPreviewData = {
            title: data.title ?? '',
            text: data.text ?? '',
            name: data.name ?? '',
            stamp: data.stamp
        };

        // 以下、確認画面の描画（ul.innerHTML = `...${esc(data.title)}...` など）

        const h = document.createElement('h3');
        h.textContent = "この内容で投稿しますか？";
        confirm_area.appendChild(h);

        const ul = document.createElement('ul');
        ul.innerHTML = `
        <li><strong>タイトル</strong>：${esc(data.title)}</li>
        <li><strong>本文</strong>：<pre style="white-space:pre-wrap;margin:0">${esc(data.text)}</pre></li>
        <li><strong>お名前</strong>：${esc(data.name || '匿名')}</li>
        <li><strong>スタンプ</strong>：${esc(data.stamp)}</li>
        <li><strong>添付</strong>：${Array.isArray(data.files) ? data.files.filter(Boolean).length : 0} 件</li>
      `;
        confirm_area.appendChild(ul);

        // 確定ボタン（なければ作る）
        let confirmBtn = document.getElementById('confirm_button');
        if (!confirmBtn) {
            confirmBtn = document.createElement('button');
            confirmBtn.type = 'button';
            confirmBtn.id = 'confirm_button';
            confirmBtn.textContent = 'この内容で投稿を確定する';
            confirm_area.appendChild(confirmBtn);
        }
        confirmBtn.addEventListener('click', confirm_button_click, {
            once: true
        });

    } catch (err) {
        console.error(err);
        alert("通信に失敗しました。時間をおいて再度お試しください。");
    } finally {
        toggleLoading(btn, false);
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

    /* ------------------------------
     * 初期化
     * ------------------------------ */
    function init() {
        // ▼ ここに追加（.attachclear ボタンへハンドラを付ける）
        document.querySelectorAll('.attachclear').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault(); // 多重送信などの事故防止
                clearAllAttachments(); // 一括クリア
                if (typeof validation === 'function') validation(); // ついでに再判定
            });
        });
        // ▲ 追加ここまで

        // 他の初期化
        if (typeof set_attach_event === 'function') {
            set_attach_event('.image-camera-icon,.usericon-uploads', 3);
        }
        const submitBtn = document.getElementById('submit_button');
        if (submitBtn) submitBtn.addEventListener('click', submit_button_click);

        change1();

        document.addEventListener('input', (e) => {
            display_text_length(e);
            validation();
        });

        validation();
    }

    window.addEventListener("DOMContentLoaded", init);
</script>