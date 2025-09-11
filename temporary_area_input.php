<?php
/*
Template Name: bbs_quest_input
固定ページ: 質問する（submit→confirmを分離：一時ファイル＋トランジェント方式）
*/
header('X-FRAME-OPTIONS: SAMEORIGIN'); // クリックジャッキング対策
get_header();                           // 通常ヘッダー
get_header('menu');                     // メニュー付きヘッダー

// 画像パス（テーマ / アップロードから取得）
$upload_dir = wp_upload_dir();                                           // アップロード基底（URL/パス）
$camera_url  = esc_url($upload_dir['baseurl'] . '/camera.png');          // camera.png（任意配置）
$noimage_url = esc_url($upload_dir['baseurl'] . '/noimage.png');         // noimage.png（任意配置）
?>
<style>
/* ステップ画像のサイズ */
.step_img { height: 364px; width: 36px; }
/* 非表示制御 */
.hideItems { display: none; }
.concealItems { display: none; }
/* ボタンのローディング（.wait 付与時） */
.wait {
  height: 40px; width: 40px; border-radius: 40px;
  border: 3px solid; border-color: #bbb; border-left-color: #1ECD97;
  font-size: 0; animation: rotating 2s 0.25s linear infinite;
}
@keyframes rotating { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
/* 入力過多の視覚ヒント */
.is-over { outline: 2px solid #d9534f; }
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
      <img id="step_img" class="step_img" alt=""><!-- ステップ画像 -->
    </div>
  </div>

  <div id="input_area"><!-- 入力エリア -->
    <form id="input_form" name="input_form" enctype="multipart/form-data"><!-- ファイル送信用 -->
      <div class="image-partial"><!-- 添付ファイル群（1〜3） -->
        <h2>
          動画・画像をアップロード (Upload video / image)
          <span class="required">※1ファイル最大5MB・合計最大20MB、JPG/PNG/PDF/MP4</span>
        </h2>

        <!-- 1つ目の添付 -->
        <div class="image-selector-button">
          <label>
            <div class="image-camera-icon">
              <img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px" alt="select file">
            </div>
            <!-- サーバ許可に合わせて accept を指定 -->
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
          <textarea class="input" name="text" id="text"
            data-length="<?php echo defined('BBS_MAX_TEXT') ? BBS_MAX_TEXT : 5000; ?>"
            data-minlength="1"
            placeholder="荒らし行為や誹謗中傷や著作権の侵害はご遠慮ください"></textarea>
          <div></div><!-- 残り文字数表示用 -->
        </div>
      </div>

      <div class="title-partial-parts"><!-- タイトル -->
        <h2>質問タイトル (title)<span class="required">※必須</span></h2>
        <div class="parts">
          <input class="input" type="text" name="title" id="title"
            data-length="<?php echo defined('BBS_MAX_TITLE') ? BBS_MAX_TITLE : 200; ?>"
            data-minlength="1"
            placeholder="1文字以上で入力してください">
          <div></div><!-- 残り文字数表示用 -->
        </div>
      </div>

      <div class="stamp-partial"><!-- スタンプ -->
        <h2>スタンプを選ぶ(必須)</h2>
        <?php for ($i=1; $i<=8; $i++): ?>
          <input type="radio" name="stamp" value="<?php echo $i; ?>" id="stamp_<?php echo $i; ?>"><label for="stamp_<?php echo $i; ?>"></label>
        <?php endfor; ?>
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
            data-length="<?php echo defined('BBS_MAX_NAME') ? BBS_MAX_NAME : 50; ?>"
            data-minlength="0"
            placeholder="未入力の場合は匿名で表示されます">
          <div></div><!-- 残り文字数表示用 -->
        </div>
      </div>

      <div class="post-button">
        <button type="button" id="submit_button" name="mode" value="confirm">確認画面へ進む</button>
      </div>
    </form>
  </div><!-- /input_area -->

  <div id="confirm_area" class="hideItems"></div><!-- 確認表示エリア -->
  <div id="result_area" class="hideItems"></div><!-- 完了表示エリア -->
</div><!-- /board_form_partial -->

<script>
/* =========================
 * ステップ表示の切替（UIのみ）
 * ========================= */
const step_img    = document.getElementById("step_img");    // ステップ画像
const q_text      = document.getElementById("q_text");      // ステップ名
const input_area  = document.getElementById("input_area");  // 入力エリア
const confirm_area= document.getElementById("confirm_area");// 確認エリア
const result_area = document.getElementById("result_area"); // 完了エリア

function change1() { // 入力中
  q_text.textContent = "質問する";
  step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step01.png'); ?>";
  step_img.alt = "STEP1 入力";
}
function change2() { // 確認
  q_text.textContent = "確認する";
  step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step02.png'); ?>";
  step_img.alt = "STEP2 確認";
}
function change3() { // 完了
  q_text.textContent = "完了";
  step_img.src = "<?php echo esc_url(get_template_directory_uri() . '/images/step03.png'); ?>";
  step_img.alt = "STEP3 完了";
}

/* =========================
 * ローディングアニメーション制御
 * ========================= */
function toggleLoading(btn, isLoading) {
  if (!btn) return;
  if (isLoading) {
    btn.disabled = true;
    btn.classList.add('wait');
    btn.setAttribute('aria-busy','true');
  } else {
    btn.disabled = false;
    btn.classList.remove('wait');
    btn.removeAttribute('aria-busy');
  }
}

/* =========================
 * 文字数表示（.input + data-length）
 * ========================= */
function display_text_length(e) {
  const el = e.target;
  if (!el.classList || !el.classList.contains('input')) return;
  const max = parseInt(el.dataset.length || '0', 10);
  if (!max) return;

  const len = el.value.length;
  const counter = el.nextElementSibling; // 直後の <div> をカウンタ領域に利用
  if (!counter) return;

  if (len <= max) {
    const remain = max - len;
    counter.textContent = `残り ${remain} 文字`;
    counter.style.color = remain === 0 ? '#d9534f' : '';
  } else {
    counter.textContent = `超過 ${len - max} 文字`;
    counter.style.color = '#d9534f';
  }
  if (len > max) el.classList.add('is-over'); else el.classList.remove('is-over');
}

/* =========================
 * 送信ボタン活性/非活性制御
 * ========================= */
function validation() {
  const btn = document.getElementById('submit_button');
  if (!btn) return;

  const titleEl = document.getElementById('title');
  const textEl  = document.getElementById('text');

  const titleMax = parseInt(titleEl?.dataset.length || '0', 10);
  const titleMin = parseInt(titleEl?.dataset.minlength || '0', 10);
  const textMax  = parseInt(textEl?.dataset.length || '0', 10);
  const textMin  = parseInt(textEl?.dataset.minlength || '0', 10);

  const titleLen = (titleEl?.value || '').length;
  const textLen  = (textEl?.value || '').length;

  // スタンプ必須
  const stamps = document.querySelectorAll('input[name="stamp"]');
  let stampOk = false;
  for (const s of stamps) { if (s.checked) { stampOk = true; break; } }

  const titleOk = titleLen >= titleMin && (titleMax ? titleLen <= titleMax : true);
  const textOk  = textLen  >= textMin  && (textMax  ? textLen  <= textMax  : true);

  // 添付の軽いクライアントチェック（最終はサーバで検証）
  const inputs = document.querySelectorAll('input.attach[type="file"]');
  let filesCount = 0;
  let clientFileOk = true;
  const MAX_FILES = 4;                 // サーバ側と合わせる
  const MAX_PER   = 5 * 1024 * 1024;   // 5MB/件

  inputs.forEach(f => {
    if (f.files && f.files.length) {
      filesCount += f.files.length;
      for (const file of f.files) { if (file.size > MAX_PER) clientFileOk = false; }
    }
  });
  if (filesCount > MAX_FILES) clientFileOk = false;

  const canSubmit = titleOk && textOk && stampOk && clientFileOk;
  btn.disabled = !canSubmit;
}

/* =========================
 * ユーティリティ：エスケープ
 * ========================= */
function esc(s) {
  const div = document.createElement('div');
  div.textContent = String(s ?? '');
  return div.innerHTML;
}

/* =========================
 * submit（一次受付）→ draft_id 取得
 * ========================= */
let lastDraftId = null; // confirm で使用

async function submit_button_click() {
  const btn = document.getElementById("submit_button");
  toggleLoading(btn, true);

  try {
    const form = document.getElementById('input_form');
    const formData = new FormData(form);                        // 入力を収集
    formData.append("action", "bbs_quest_submit");              // WP AJAXアクション
    if (window.bbs_vars?.nonce) formData.append("nonce", bbs_vars.nonce); // submit 用nonce

    const res = await fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
      method: "POST", body: formData, credentials: "same-origin"
    });
    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json?.data?.errors?.[0] || "送信に失敗しました。";
      alert(msg);
      return;
    }

    lastDraftId = json.data?.draft_id || null;                  // サーバからのドラフトID
    if (!lastDraftId) { alert("ドラフトIDが取得できませんでした。"); return; }

    // 取得後、confirm:show を呼んでプレビューを描画
    await show_preview(lastDraftId);

    // 画面を確認状態へ
    change2();
    input_area.style.display   = "none";
    confirm_area.style.display = "block";

  } catch (e) {
    console.error(e);
    alert("通信に失敗しました。時間をおいて再度お試しください。");
  } finally {
    toggleLoading(btn, false);
  }
}

/* =========================
 * confirm(mode=show)：プレビュー取得
 * ========================= */
async function show_preview(draftId) {
  // confirm:show を呼ぶ
  const fd = new FormData();
  fd.append('action', 'bbs_quest_confirm');
  fd.append('mode', 'show');
  fd.append('draft_id', draftId);
  if (window.bbs_confirm_vars?.nonce) fd.append('nonce', bbs_confirm_vars.nonce);

  const res = await fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
    method: 'POST', body: fd, credentials: 'same-origin'
  });
  const json = await res.json();
  if (!json || json.success !== true) {
    const msg = json?.data?.errors?.[0] || "プレビューの取得に失敗しました。";
    alert(msg);
    return;
  }

  // プレビュー描画
  const d = json.data?.data || {}; // サーバ仕様：{ data: {...} } を返す前提
  confirm_area.innerHTML = '';     // クリア

  const h = document.createElement('div');
  h.innerHTML = `
    <h3>入力内容の確認</h3>
    <ul>
      <li><strong>タイトル:</strong> ${esc(d.title)}</li>
      <li><strong>本文:</strong><br>${esc(d.text).replace(/\\n/g,'<br>')}</li>
      <li><strong>名前:</strong> ${esc(d.name)}</li>
      <li><strong>スタンプ:</strong> ${esc(d.stamp)}</li>
    </ul>
  `;
  confirm_area.appendChild(h);

  // 添付（basename のみ表示。画像プレビューは任意で拡張可能）
  if (Array.isArray(d.files) && d.files.length) {
    const box = document.createElement('div');
    box.innerHTML = '<h4>添付ファイル</h4>';
    const ul = document.createElement('ul');
    d.files.forEach((fn, idx) => {
      const li = document.createElement('li');
      li.textContent = fn ? `#${idx+1} ${fn}` : `#${idx+1} （未選択）`;
      ul.appendChild(li);
    });
    box.appendChild(ul);
    confirm_area.appendChild(box);
  }

  // 確定ボタン生成
  let confirmBtn = document.getElementById('confirm_button');
  if (!confirmBtn) {
    confirmBtn = document.createElement('button');
    confirmBtn.type = 'button';
    confirmBtn.id   = 'confirm_button';
    confirmBtn.textContent = 'この内容で投稿を確定する';
    confirm_area.appendChild(confirmBtn);
  }
  confirmBtn.onclick = confirm_button_click; // 二重に addEventListener しない
}

/* =========================
 * confirm(mode=commit)：確定
 * ========================= */
async function confirm_button_click() {
  const btn = document.getElementById('confirm_button');
  toggleLoading(btn, true);

  try {
    if (!lastDraftId) { alert("ドラフトIDがありません。先に確認へ進んでください。"); return; }

    const fd = new FormData();
    fd.append('action', 'bbs_quest_confirm');
    fd.append('mode', 'commit');          // 最終確定
    fd.append('draft_id', lastDraftId);   // submitで受け取ったID
    if (window.bbs_confirm_vars?.nonce) fd.append('nonce', bbs_confirm_vars.nonce);

    const res = await fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
      method: 'POST', body: fd, credentials: 'same-origin'
    });
    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json?.data?.errors?.[0] || "確定に失敗しました。";
      alert(msg);
      return;
    }

    // 完了表示
    change3();
    const buttons = document.querySelectorAll('.post-button');
    buttons.forEach(x => x.style.display = "none");

    confirm_area.innerHTML = '';
    const ok = document.createElement('p');
    ok.textContent = '投稿が確定しました。ありがとうございました。';
    confirm_area.appendChild(ok);

  } catch (e) {
    console.error(e);
    alert("通信に失敗しました。時間をおいて再度お試しください。");
  } finally {
    toggleLoading(btn, false);
  }
}

/* =========================
 * 初期化
 * ========================= */
function init() {
  // 画像アップロードの既存ヘルパーがある場合（なければこの呼出は無視される想定）
  if (typeof set_attach_event === 'function') {
    set_attach_event('.image-camera-icon,.usericon-uploads', 3); // 1〜3番 + usericon
  }

  // 送信ボタンイベント
  const submitBtn = document.getElementById('submit_button');
  if (submitBtn) submitBtn.addEventListener('click', submit_button_click);

  // ステップ初期表示
  change1();

  // 入力のたびに文字数表示＆ボタン制御
  document.addEventListener('input', (e) => {
    display_text_length(e);
    validation();
  });

  // 初回のボタン状態
  validation();
}

// DOM 構築完了後に初期化
window.addEventListener('DOMContentLoaded', init);
</script>
<?php get_footer(); ?>