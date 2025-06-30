<?php
/*
Template Name: bbs_quest_input
固定ページ: 質問する
*/
header('X-FRAME-OPTIONS: SAMEORIGIN');
get_header();
get_header('menu');
//追加コード
$upload_dir = wp_upload_dir();
$camera_url = $upload_dir['baseurl'] . '/camera.png';
$noimage_url = $upload_dir['baseurl'] . '/noimage.png';
?>
<style>
    .step_img {
        height: 364px;
        width: 36px;
    }
</style>
<div class="board_form_partial" id="js_board_form_partial">
    <div class="questionHeader-partial">
        <h2>
            <span class="fa-stack">
                <i class="fa fa-circle fa-stack-2x w-circle"></i>
                <i class="fa-stack-1x fa-inverse q">Q</i>
            </span>
            <span class="q-text" id="q_text"></span>
        </h2>
        <div class="other_step">
            <img id="step_img">
        </div>
    </div>
    <div id="input_area">
        <form name="input_form">
            <div class="image-partial">
                <h2>動画・画像をアップロード(Upload video・image)<span class="required">※ファイルサイズ15MB以内、JPG/GIF/PNG/MP4</span></h2>
                <div class="image-selector-button">
                    <label>
                        <div class="image-camera-icon">
                            <img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px">
                        </div>
                        <input type="file" class="attach" name="attach[]" accept=".png, .jpg, .jpeg, .pdf, .mp4" style="display: none;">
                    </label>
                    <div class="viewer" style="display: none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
                <div class="image-selector-button">
                    <label>
                        <div class="image-camera-icon"><img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px">
                        </div>
                        <input type="file" class="attach" name="attach[]" accept=".png, .jpg, .jpeg, .pdf, .mp4" style="display: none;">
                    </label>
                    <div class="viewer" style="display: none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
                <div class="image-selector-button">
                    <label>
                        <div class="image-camera-icon">
                            <img src="<?php echo $camera_url; ?>" class="changeImg" style="height:150px;width:150px">
                        </div>
                        <input type="file" class="attach" name="attach[]" accept=".png, .jpg, .jpeg, .pdf, .mp4" style="display: none;">
                    </label>
                    <div class="viewer" style="display: none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
            </div>
            <style>
                .hideItems {
                    display: none;
                }

                .concealItems {
                    display: none;
                }

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

            <div class="body-partial-parts"><!-- body-partial-parts -->
                <h2>質問文(question)<span class="required">※必須</span></h2>
                <div class="parts">
                    <textarea class="input" name="text" id="text" data-length="<?php echo MAX_LENGTH::TEXT; ?>" data-minlength="<?php echo MIN_LENGTH::TEXT; ?>" placeholder="荒らし行為や誹謗中傷や著作権の侵害はご遠慮ください"></textarea>
                    <div></div>
                </div>
            </div>

            <div class="title-partial-parts"> <!-- title-partial-parts -->
                <h2>質問タイトル(title)<span class="required">※必須</span></h2>
                <div class="parts">
                    <input class="input" type="text" name="title" id="title" data-length="<?php echo MAX_LENGTH::TITLE; ?>" data-minlength="<?php echo MIN_LENGTH::TITLE; ?>" placeholder="<?php echo MIN_LENGTH::TITLE; ?>文字以上で入力してください">
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

            <div class="usericon-partial">
                <h2>画像アイコン(image icon)<span class="required">※任意</span></h2>
                <div class="usericon-thumbnail-button">
                    <label>
                        <div class="usericon-uploads">
                            <img src="<?php echo $noimage_url; ?>" class="changeImg" style="height:90px;width:90px">
                        </div>
                        <input type="file" class="attach" name="attach[]" data-maxsize="5" accept=".png, .jpg, .jpeg" style="display: none;">
                    </label>
                    <div class="viewer" style="display: none;"></div>
                    <button type="button" class="attachclear">clear</button>
                </div>
            </div>

            <div class="name-partial-parts"> <!-- name-partial-parts -->
                <h2>名前(name)<span class="required">※任意</span></h2>
                <div class="parts">
                    <input class="input" type="text" name="name" id="name" data-length="<?php echo MAX_LENGTH::NAME; ?>" data-minlength="<?php echo MIN_LENGTH::NAME; ?>" placeholder="未入力の場合は、匿名で表示されます">
                    <div></div>
                </div>
            </div>

            <div class="post-button"><!-- ボタンを押せなくする -->
                <button type="button" id="submit_button" name="mode" value="confirm">確認画面へ進む</button>
            </div>
            <!-- type、name、id、valueの順番 -->
        </form>
    </div>
    <div id="confirm_area"></div>
    <div id="result_area"></div>
</div>
<script>
    function change1() {
        q_text.textContent = "質問する";
        step_img.src = "http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/step01.png";
        step_img.alt = "STEP1 入力";
    }

    function change2() {
        q_text.textContent = "確認する";
        step_img.src = "http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/step02.png";
        step_img.alt = "STEP2 確認";
    }

    function change3() {
        q_text.textContent = "完了";
        step_img.src = "http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/step03.png";
        step_img.alt = "STEP3 完了";
    }

    const step_img = document.getElementById("step_img");
    const q_text = document.getElementById("q_text");
    const input_area = document.getElementById("input_area");
    const confirm_area = document.getElementById("confirm_area");
    const result_area = document.getElementById("result_area");
    var name_value = "";
    var title_value = "";
    var text_value = "";
    var stamp_value = "";
    // 要素が3個の空配列を作成
    const blobType = ["", "", "", ""];
    const blobUrl = ["", "", "", ""];

    const init = function() {
        set_attach_event('.image-camera-icon,.usericon-uploads', 3);
        document.getElementById("submit_button").addEventListener("click", submit_button_click);
        change1();

        /* inputイベント */
        document.addEventListener('input', e => {
            /* 文字数表示 */
            display_text_length(e);
            /* 毎回判定によるボタン制御 */
            validation();
        });
        /* 初回判定のボタン制御 */
        validation();
    } //const init = function () の終了
    //DOM構築、スタイルシート、画像、サブフレームの読み込みが完了した後に発生する
    window.addEventListener("DOMContentLoaded", init);

    const submit_button_click = function() {
        //ローディングアニメーション画像
        const submit_button = document.getElementById("submit_button");
        submit_button.disabled = true;
        submit_button.classList.add('wait');
        name_value = "";
        title_value = "";
        text_value = "";
        stamp_value = "";
        //サーバーにデータを送信する際に使用するオブジェクトを生成
        const formData = new FormData(input_form);
        //オブジェクト内の既存のキーに新しい値を追加
        formData.append("action", "bbs_quest_submit");
        const opt = {
            method: "post",
            body: formData
        }
        //非同期通信
        fetch("<?php echo home_url('wp-admin/admin-ajax.php'); ?>", opt)
            .then(response => {
                //ボタンを使用可にしています「非同期通信が完了して応答があった時」の処理です。
                submit_button.disabled = false;
                //ボタンのクラスからwaitを削除しています（もとのボタンに戻ります）
                submit_button.classList.remove('wait');
                return response.json();
            })
            .then(json => {
                if (json.data.error != "") {
                    alert(json.data.error);
                    return;
                }
                name_value = json.data.name;
                text_value = json.data.text;
                title_value = json.title;
                const stamps = document.getElementsByName('stamp');
                for (var stamp of stamps) {
                    //checkedプロパティは、対象の要素がcheckedを持っていればtrueを、持っていなければfalseを返す
                    if (stamp.checked) {
                        stamp_value = stamp.value;
                        break;
                    }
                }
                change2();
                // 空文字を入れることで要素内を空にできる
                confirm_area.textContent = '';
                var div;
                var child;
                // class
                //const divBoardFormPartial = document.createElement("div");
                // classの追加
                //divBoardFormPartial.classList.add("board_form_partial");
                // idの追加
                //divBoardFormPartial.id = 'js_board_form_partial_div';

                //confirm_area.appendChild(child); // confirm_area の末尾に child を追加
                //const divQuestionHeaderPartial = document.createElement("div");
                //divQuestionHeaderPartial.classList.add("questionHeader-partial");
                //div.appendChild(child); // div の末尾に child を追加
                /* ファイルアップロード要素作成 */
                const usericonImg = document.createElement("img");
                const divImagePartial = create_image_parts("image-partial", 3, usericonImg);
                const image_area = document.createElement("div");

                /* コメント要素作成 */
                const comment_area = document.createElement("div");
                const divBodyPartialParts = create_text_parts("text-partial-parts", text_value);

                /* タイトル要素作成 */
                const divTitlePartialParts = create_title_parts("title-partial-parts", title_value);

                /* スタンプ画像要素作成 */
                const divStampPartial = document.createElement("div"); // div (子)を生成
                divStampPartial.classList.add("stamp-partial"); // classの追加

                child = document.createElement("input"); // input (孫)を生成
                child.type = "radio";
                child.name = "stamp";
                child.id = "confirm_stamp";
                child.value = stamp_value;
                child.checked = true;
                divStampPartial.appendChild(child); // div の末尾に child を追加
                child = document.createElement("label");
                child.htmlFor = "confirm_stamp";

                /* 名前要素作成 */
                const divNamePartialParts = create_name_parts("name-partial-parts", name_value, usericonImg);

                /* ファイルアップロード要素配置 */
                image_area.appendChild(divImagePartial);

                /* 回答要素配置 */
                comment_area.appendChild(divBodyPartialParts); // comment_area (親要素) の末尾に div を追加

                /* タイトル要素配置 */
                comment_area.appendChild(divTitlePartialParts); // comment_area (親要素) の末尾に div を追加

                /* スタンプ画像要素配置 */
                divStampPartial.appendChild(child); // div (子要素) の末尾に child を追加
                comment_area.appendChild(divStampPartial); // comment_area (親要素) の末尾に div を追加

                /* 名前要素配置 */
                comment_area.appendChild(divNamePartialParts); // comment_area (親要素) の末尾に div を追加

                /* 確認画面送信ボタン要素作成 */
                const divPostButton = create_button_parts(1);
                comment_area.appendChild(divPostButton); // comment_area (親要素) の末尾に div を追加
                confirm_area.appendChild(image_area);
                confirm_area.appendChild(comment_area);
                const image_count = image_area.getElementsByClassName("changeImg").length;
                // const divImagePartial = document.createElement("div"); // div (子)を生成
                // divImagePartial.classList.add(image_class); // classの追加
                if (image_count == 1) {
                    divImagePartial.style.float = "left";
                } else if (image_count == 2) {
                    divImagePartial.style.float = "left";
                    divBodyPartialParts.style.height = "728px"; //コメント欄外枠
                } else if (image_count == 3) {
                    Array.from(divImagePartial.children).forEach(x => x.style.float = "left");
                }
                input_area.style.display = "none";
                confirm_area.style.display = "block";
            })
            .catch(error => {
                console.log(error);
            });
    }

    const confirm_button_click = function() {
        const formData = new FormData();
        formData.append("action", "bbs_quest_confirm");
        const opt = {
            method: "post",
            body: formData
        }
        fetch("<?php echo home_url('wp-admin/admin-ajax.php'); ?>", opt)
            .then(response => {
                return response.json();
            })
            .then(json => {
                if (json.error != "") {
                    alert(json.error);
                    return;
                }
                change3();
                const buttons = document.querySelectorAll('.post-button');
                buttons.forEach(x => x.style.display = "none");
            })
            .catch(error => {
                console.log(error);
            });
    }
</script>