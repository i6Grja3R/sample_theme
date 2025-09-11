<?php
/*
Template Name: bbs_quest_input
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
        <form id="input_form" name="input_form" enctype="multipart/form-data"><!-- ファイル送信用にenctype指定 -->
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

    // ステップ1：入力中の見出しと画像
    function change1() {
        q_text.textContent = "質問する";
        step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step01.png'); ?>";
        step_img.alt = "STEP1 入力";
    }

    // ステップ2：確認画面の見出しと画像
    function change2() {
        q_text.textContent = "確認する";
        step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step02.png'); ?>";
        step_img.alt = "STEP2 確認";
    }

    // ステップ3：完了の見出しと画像
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
            btn.disabled = true; // クリック無効
            btn.classList.add('wait'); // スピナー用クラス
            btn.setAttribute('aria-busy', 'true');
        } else {
            btn.disabled = false;
            btn.classList.remove('wait');
            btn.removeAttribute('aria-busy');
        }
    }

    /* -------------------------------------
     * 文字数表示（タイトル/本文/名前の .input 要素）
     *  - data-length / data-minlength 属性を利用
     *  - 直後の <div> に「残り/超過」を表示
     * ------------------------------------- */
    function display_text_length(e) {
        const el = e.target;
        // .input クラス & data-length を持つ要素のみ対象
        if (!el.classList || !el.classList.contains('input')) return;
        const max = parseInt(el.dataset.length || '0', 10);
        if (!max) return;

        const len = el.value.length;
        const counter = el.nextElementSibling; // テンプレ構成: 直後の <div> をカウンタとして利用
        if (!counter) return;

        if (len <= max) {
            const remain = max - len;
            counter.textContent = `残り ${remain} 文字`;
            counter.style.color = remain === 0 ? '#d9534f' : '';
        } else {
            counter.textContent = `超過 ${len - max} 文字`;
            counter.style.color = '#d9534f';
        }

        // 入力過多の視覚ヒント（任意）
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

        // 必須フィールド
        const titleEl = document.getElementById('title');
        const textEl = document.getElementById('text');

        // data-* で設定された閾値を取得
        const titleMax = parseInt(titleEl?.dataset.length || '0', 10);
        const titleMin = parseInt(titleEl?.dataset.minlength || '0', 10);
        const textMax = parseInt(textEl?.dataset.length || '0', 10);
        const textMin = parseInt(textEl?.dataset.minlength || '0', 10);

        const titleLen = (titleEl?.value || '').length;
        const textLen = (textEl?.value || '').length;

        // スタンプ必須（1..8 のいずれかが選択済み）
        const stamps = document.querySelectorAll('input[name="stamp"]');
        let stampOk = false;
        for (const s of stamps) {
            if (s.checked) {
                stampOk = true;
                break;
            }
        }

        // 文字数の妥当性
        const titleOk = titleLen >= titleMin && (titleMax ? titleLen <= titleMax : true);
        const textOk = textLen >= textMin && (textMax ? textLen <= textMax : true);

        // 添付のクライアント側ソフトチェック
        const inputs = document.querySelectorAll('input.attach[type="file"]');
        let filesCount = 0;
        let clientFileOk = true;
        const MAX_FILES = 4; // サーバー側と合わせる
        const MAX_PER = 5 * 1024 * 1024; // 5MB/件

        inputs.forEach(f => {
            if (f.files && f.files.length) {
                filesCount += f.files.length;
                for (const file of f.files) {
                    if (file.size > MAX_PER) clientFileOk = false;
                }
            }
        });
        if (filesCount > MAX_FILES) clientFileOk = false;

        // すべてOKならボタン活性化
        const canSubmit = titleOk && textOk && stampOk && clientFileOk;
        btn.disabled = !canSubmit;
    }

    /* -------------------------------------
     * 送信（submit → サーバー bbs_quest_submit）
     * ------------------------------------- */
    let lastInsertId = null; // 確認APIで使うID（サーバーが返す）

    async function submit_button_click() {
        const btn = document.getElementById("submit_button");
        toggleLoading(btn, true); // スピナーON

        try {
            // 送信用データをフォームから収集
            const formData = new FormData(input_form);
            formData.append("action", "bbs_quest_submit"); // WP AJAXアクション
            if (window.bbs_vars?.nonce) formData.append("nonce", bbs_vars.nonce); // CSRF対策

            // 送信
            const res = await fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            });

            const json = await res.json();

            // WordPress の標準形: { success: true/false, data: {...} }
            if (!json || json.success !== true) {
                // エラー配列の最初を出す（なければメッセージ）
                const msg = json?.data?.errors?.[0] || "送信に失敗しました。";
                alert(msg);
                return;
            }

            // 成功：サーバーが返す id / files / user_uuid などを取得
            lastInsertId = json.data?.id ?? null;

            // 画面を確認状態へ
            change2();
            input_area.style.display = "none";
            confirm_area.style.display = "block";

            // 既存の確認画面生成ロジックがある場合はそれを呼び出す
            // （ここでは簡易に「確認へ進みました」だけ表示）
            confirm_area.textContent = "";
            const p = document.createElement('p');
            p.textContent = "入力内容を確認してください（このまま送信で確定します）。";
            confirm_area.appendChild(p);

            // 確定ボタンが無ければ作る（id='confirm_button'）
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
            toggleLoading(btn, false); // スピナーOFF
        }
    }

    /* -------------------------------------
     * 確定（confirm → サーバー bbs_quest_confirm）
     *  - mode=commit / id=lastInsertId を送る
     * ------------------------------------- */
    async function confirm_button_click() {
        const btn = document.getElementById('confirm_button');
        toggleLoading(btn, true);

        try {
            if (!lastInsertId) {
                alert("確認用の投稿IDが取得できていません。先に送信（確認へ）してください。");
                return;
            }

            const formData = new FormData();
            formData.append("action", "bbs_quest_confirm"); // WP AJAXアクション
            formData.append("mode", "commit"); // 確定処理
            formData.append("id", String(lastInsertId)); // submit で作られたID
            if (window.bbs_confirm_vars?.nonce) {
                formData.append("nonce", bbs_confirm_vars.nonce); // confirm 用nonce
            }

            const res = await fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            });

            const json = await res.json();

            if (!json || json.success !== true) {
                const msg = json?.data?.errors?.[0] || "確定に失敗しました。";
                alert(msg);
                return;
            }

            // 完了UI
            change3();
            const buttons = document.querySelectorAll('.post-button');
            buttons.forEach(x => x.style.display = "none");

            confirm_area.textContent = "";
            const ok = document.createElement('p');
            ok.textContent = "投稿が確定しました。ありがとうございました。";
            confirm_area.appendChild(ok);

        } catch (err) {
            console.error(err);
            alert("通信に失敗しました。時間をおいて再度お試しください。");
        } finally {
            toggleLoading(btn, false);
        }
    }

    /* -------------------------------------
     * 初期化
     *  - 画像/アイコンのセレクタにイベント付与（既存関数を利用）
     *  - 入力文字数表示とボタン制御をセット
     * ------------------------------------- */
    function init() {
        // 画像アップロードの既存ヘルパー（あなたの実装を呼び出し）
        set_attach_event('.image-camera-icon,.usericon-uploads', 3);

        // 送信ボタンイベント
        const submitBtn = document.getElementById("submit_button");
        if (submitBtn) submitBtn.addEventListener("click", submit_button_click);

        // ステップ初期表示
        change1();

        // 入力が発生するたびに文字数表示＆ボタン再評価
        document.addEventListener('input', (e) => {
            display_text_length(e);
            validation();
        });

        // 初回評価
        validation();
    }

    // DOM 構築完了後に初期化
    window.addEventListener("DOMContentLoaded", init);
</script>