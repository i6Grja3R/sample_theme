<?php
/*
Template Name: bbs_que_answer
固定ページ: 回答画面
*/
header('X-FRAME-OPTIONS: SAMEORIGIN');
get_header();
// get_header('menu'); 必要なコードか分からない
$unique_id = substr($_SERVER['REQUEST_URI'], -36);
$sql = 'SELECT * FROM {$wpdb->prefix}sortable WHERE unique_id = %s';
$query = $wpdb->prepare($sql, $unique_id);
$rows = $wpdb->get_results($query);
// アップロードディレクトリ（パス名）を取得する
$upload_dir = wp_upload_dir();
echo '<div class="main_container">';
echo '<div class="quest_container">';
// var_dump($rows);
foreach ($rows as $row) {
    $files = array_filter([$row->attach1, $row->attach2, $row->attach3]);
    $views = []; //ＨＴＭＬをため込む配列の初期化する
    foreach ($files as $file) {
        $info = pathinfo($file);
        $attach_url = $upload_dir['baseurl'] . '/attach/' . $info['basename'];
        $ext = $info['extension'];
        switch ($ext) {
            case 'jpeg':
            case 'png':
                $views[] = '<img style="height:301px;width:535px" src="' . $attach_url . '">';
                break;
            case 'mp4':
                $views[] = '<video style="height:301px;width:535px" src="' . $attach_url . '" controls>';
                break;
            case 'pdf':
                $views[] = '<iframe style="height:301px;width:535px" src="' . $attach_url . '"></iframe>';
                break;
            default:
                break;
        }
    }
    $count = count($views);
    if ($count == 1 || $count == 2) {
        $qm = 'quest_markdown';
        $qi = 'quest_item';
    } else {
        $qm = 'quest_markdown_group';
        $qi = 'quest_item_group';
    }
    if (empty($row->usericon)) {
        $usericon_src = 'wp-content/themes/sample_theme/images/noimage.png';
    } else {
        $usericon_src = $upload_dir['baseurl'] . '/attach/' . $row->usericon;
    }
    // echo '<div><a href="'.$url.'">'.$row->unique_id.'</a></div>';
    echo '<div class="quest_header_title">' . $row->title . '</div>'; // タイトル30文字
    echo '<div class="quest_feeling_stamp"><input type="radio" name="stamp" value="' . $row->stamp . '" id="stamp"><label for="stamp" class="quest_stamp_label"></label></div>'; // スタンプ画像

    // 全体にのみ float: left;
    echo '<div class="' . $qm . '">';
    foreach ($views as $view) { // 個別にのみ float: left;
        echo '<div class="' . $qi . '">' . $view . '</div>'; // アップロードファイル
    }
    echo '</div>';  // quest_markdown の閉じタグ

    echo '<div class="quest_overview">' . $row->text . '</div>'; // 質問文
    // echo '</div>';
    echo '<div class="quest_usericon_img"><img src="' . $usericon_src . '">'; // アイコン画像
    echo '<div class="quest_username">' . $row->name . '</div>'; // 名前

    // ゲスト用ユーザーID（Cookieベース）
    $user_id = $_COOKIE['like_user_id'] ?? null;

    // テンプレートにデータを渡してボタン描画（AJAX + SVG込み）
    get_template_part('template-parts/like/button', null, [
        'unique_id' => $unique_id,
        'user_id'   => $user_id // ← これを追加
    ]);

    echo '</div>';  // アイコン画像
}
echo '</div>'; //<div class="quest_container"> の閉じタグ
//ここから回答機能
//追加コード
// $upload_dir = wp_upload_dir();
$camera_url = $upload_dir['baseurl'] . '/camera.png';
$noimage_url = $upload_dir['baseurl'] . '/noimage.png';
?>
<!-- <button class="comment-remark-button" id="">返信</button> -->
<div id="js_board_respond" class="board_respond">
    <div id="input_area">
        <form name="answer_Input_form">
            <div class="placeholder-area">
                <div class="comments-composer">コメント一覧</div>
                <textarea id="text" class="rich-label" name="text" placeholder="荒らし行為や誹謗中傷や著作権の侵害はご遠慮ください"></textarea>
                <div></div>
            </div>
        </form>
    </div>
</div>
<script>
    const make = function() {
        //const buttonCommentRmarkBtton = document.querySelectorAll("comment-remark-button");
        const divPlaceholderArea = document.querySelector(".placeholder-area");
        const textareaRichLabel = document.querySelector(".rich-label");

        // datasetでdata属性（data-*）を設定する
        const TEXT_MAX_LENGTH = <?php echo MAX_LENGTH::TEXT; ?>;
        const TEXT_MIN_LENGTH = <?php echo MIN_LENGTH::TEXT; ?>;

        textareaRichLabel.dataset.length = TEXT_MAX_LENGTH;
        textareaRichLabel.dataset.minlength = TEXT_MIN_LENGTH;
        //textareaRichLabel.addEventListener("click", () => {
        // クリックした時に実行する関数に変更
        const textareaRichLabelClick = function() {
            // クリックイベントを削除
            textareaRichLabel.removeEventListener("click", textareaRichLabelClick);
            // JavaScript でＨＴＭＬ生成する
            /* 回答機能全般要素作成 */
            // const divBoardRespond = document.createElement("div");
            // class属性の値を追加
            // divBoardRespond.setAttribute('class', 'board_respond');
            // id属性の値を追加
            // divBoardRespond.setAttribute('id', 'js_board_respond');

            /* 回答機能要素作成 */
            // const divInputArea = document.createElement("div");
            // id属性の値を追加
            // divInputArea.setAttribute('id', 'input_area');

            /* 回答入力フォーム要素作成 */
            // const formAnswerInputForm = document.createElement("form");

            // name属性の値を追加
            // formAnswerInputForm.setAttribute('name', 'answer_Input_form');

            /* inputタグ要素作成 */
            // const input = document.createElement("input");
            // input要素のtype属性を操作 (input type="hidden" 非表示で値を送信)
            // input.setAttribute("type", "hidden");
            // input.value="'.$unique_id['unique_id'].'"

            /* divタグ要素作成 */
            const divUserArea = document.createElement("div");
            // class属性の値を追加
            divUserArea.setAttribute('class', 'user-area');

            /* lavelタグ要素作成 */
            let label = document.createElement("label");

            /* divタグ要素作成 */
            const divuserIcon = document.createElement("div");
            // class属性の値を追加
            divuserIcon.setAttribute('class', 'user-icon');

            /* img要素を動的に作成して画像を表示する */
            const NOIMAGE_URL = '<?php echo $noimage_url; ?>';

            let img_element = document.createElement('img');
            img_element.src = NOIMAGE_URL; // 画像パス
            // class属性の値を追加
            img_element.setAttribute('class', 'changeImg');
            // 要素のスタイルを取得・設定
            img_element.style.height = '90px';
            img_element.style.width = '90px';

            /* inputタグ要素作成 */
            let inputAttach = document.createElement("input");
            // input要素のtype属性を操作
            inputAttach.setAttribute("type", "file");
            // class属性の値を追加
            inputAttach.setAttribute('class', 'attach');
            // name属性の値を追加
            inputAttach.setAttribute('name', 'attach[]');
            // datasetでdata属性（data-*）を設定する
            inputAttach.dataset.maxsize = '5';
            // HTMLInputElement: accept プロパティ
            inputAttach.accept = ".png, .jpg, .jpeg"; // accept 値を設定
            inputAttach.style.display = 'none';

            /* divタグ要素作成 */
            let divViewer = document.createElement("div");
            // class属性の値を追加
            divViewer.setAttribute('class', 'viewer');
            divViewer.style.display = 'none';

            /* buttonタグ要素作成 */
            let buttonAttachclear = document.createElement("button");
            // input要素のtype属性を操作
            buttonAttachclear.setAttribute("type", "button");
            // class属性の値を追加
            buttonAttachclear.setAttribute('class', 'attachclear');
            // タグにテキスト挿入
            buttonAttachclear.textContent = "clear";

            /* divタグ要素作成 */
            const divAnswerNameArea = document.createElement("div");
            // class属性の値を追加
            divAnswerNameArea.setAttribute('class', 'answer-name-area');

            /* divタグ要素作成 */
            let divParts = document.createElement("div");
            // class属性の値を追加
            divParts.setAttribute('class', 'parts');

            /* inputタグ要素作成 */
            const inputName = document.createElement("input");
            // input要素のtype属性を操作
            inputName.setAttribute("type", "text");
            // name属性の値を追加
            inputName.setAttribute('name', 'name');
            // class属性の値を追加
            inputName.setAttribute('class', 'input');
            // id属性の値を追加
            inputName.setAttribute('id', 'name');
            // datasetでdata属性（data-*）を設定する
            const NAME_MAX_LENGTH = <?php echo MAX_LENGTH::NAME; ?>;
            const NAME_MIN_LENGTH = <?php echo MIN_LENGTH::NAME; ?>;

            inputName.dataset.length = NAME_MAX_LENGTH;
            inputName.dataset.minlength = NAME_MIN_LENGTH;

            inputName.placeholder = "未入力の場合は、匿名で表示されます";

            /* divタグ要素作成 */
            let div = document.createElement("div");

            /* divタグ要素作成 */
            const divFilesizeRestrictionArea = document.createElement("div");
            // class属性の値を追加
            divFilesizeRestrictionArea.setAttribute('class', 'filesize-restriction-area');

            /* spanタグ要素作成 */
            const spanAnnotation = document.createElement("span");
            // class属性の値を追加
            spanAnnotation.setAttribute('class', 'annotation');
            spanAnnotation.textContent = "動画・画像をアップロード(Upload video・image)"; //spanタグにテキスト挿入
            const spanRequired = document.createElement("span");
            // class属性の値を追加
            spanRequired.setAttribute('class', 'required');
            spanRequired.textContent = "※ファイルサイズ15MB以内、JPG/GIF/PNG/MP4"; //spanタグにテキスト挿入

            /* buttonタグ要素作成 */
            const buttonCancelButton = document.createElement("button");
            // button要素のtype属性を操作
            buttonCancelButton.setAttribute("type", "button");
            // class属性の値を追加
            buttonCancelButton.setAttribute('class', 'cancel-button');
            buttonCancelButton.textContent = "キャンセル"; //divタグにテキスト挿入

            buttonCancelButton.addEventListener("click", () => {
                let divBoardRespond = document.querySelector('#divBoardRespond');
                divBoardRespond.remove(); //remove()で要素を削除する
                // setResButtonsDisabled(false); // 2度押し禁止
            });

            function setResButtonsDisabled() {
                // buttonCancelButton = document.querySelector(".cancel-button");
                // 2度押し禁止
                divBoardRespond.disabled = true;
            }

            /* divタグ要素作成 */
            const divPostButton = document.createElement("div");
            // class属性の値を追加
            divPostButton.setAttribute('class', 'post-button');

            /* buttonタグ要素作成 */
            let buttonSubmitButton = document.createElement("button");

            /* input要素のtype属性を操作 */
            buttonSubmitButton.setAttribute("type", "button");
            // name属性の値を追加
            buttonSubmitButton.setAttribute('name', 'mode');
            // id属性の値を追加
            buttonSubmitButton.setAttribute('id', 'submit_button');
            // value属性の値を追加
            buttonSubmitButton.setAttribute('value', 'confirm');
            // タグにテキスト挿入
            buttonSubmitButton.textContent = "確認画面へ進む";

            // 「確認画面へ進む」ボタンにもイベントを追加
            buttonSubmitButton.addEventListener("click", () => {
                submit_button_click();
            });

            /* divタグ要素作成 */
            const divConfirmArea = document.createElement("div");
            // id属性の値を追加
            divConfirmArea.setAttribute('id', 'confirm_area');

            /* divタグ要素作成 */
            const divResultArea = document.createElement("div");
            // id属性の値を追加
            divResultArea.setAttribute('id', 'result_area');

            /* 回答機能全般要素配置位置 */
            // divPlaceholderArea.appendChild(divBoardRespond); // div (真親要素) の末尾に div を追加

            /* 回答機能要素配置位置 */
            // divBoardRespond.appendChild(divInputArea); // div (親要素) の末尾に div を追加

            /* 回答入力フォーム要素配置位置 */
            // divInputArea.appendChild(formAnswerInputForm); // div (子要素) の末尾に form を追加

            /* 質問（親）の id 取得配置位置 */
            // 取得した時点でインデックス 0 のものを formAnswerInputForm に入れてしまう
            // const formAnswerInputForm = document.getElementsByName('answer_Input_form')[0];
            // formAnswerInputForm.appendChild(input); // form (孫要素) の末尾に input を追加

            /* 回答機能要素配置位置 */
            // 取得した時点でインデックス 0 のものを formAnswerInputForm に入れてしまう
            const formAnswerInputForm = document.getElementsByName('answer_Input_form')[0];
            formAnswerInputForm.appendChild(divUserArea); // form (孫要素) の末尾に div を追加

            /* labelタグ要素配置位置 */
            divUserArea.appendChild(label); // div (ひ孫要素) の末尾に label を追加

            /* divタグ要素配置位置 */
            label.appendChild(divuserIcon); // label (玄孫要素) の末尾に div を追加

            /* imgタグ要素配置位置 */
            divuserIcon.appendChild(img_element); // div (来孫要素) の末尾に img を追加

            /* inputタグ要素配置位置 */
            label.appendChild(inputAttach); // label (玄孫要素) の末尾に input を追加

            /* divタグ要素配置位置 */
            divUserArea.appendChild(divViewer); // div (ひ孫要素) の末尾に div を追加

            /* buttonタグ要素配置位置 */
            divUserArea.appendChild(buttonAttachclear); // div (ひ孫要素) の末尾に button を追加

            /* divタグ要素配置位置 */
            formAnswerInputForm.appendChild(divAnswerNameArea); // form (孫要素) の末尾に div を追加

            /* divタグ要素配置位置 */
            divAnswerNameArea.appendChild(divParts); // div (ひ孫要素) の末尾に div を追加

            /* inputタグ要素配置位置 */
            divParts.appendChild(inputName); // div (玄孫要素) の末尾に input を追加

            /* divタグ要素配置位置 */
            divParts.appendChild(document.createElement("div"));

            /* divタグ要素配置位置 */
            formAnswerInputForm.appendChild(divFilesizeRestrictionArea); // form (孫要素) の末尾に div を追加

            /* divタグ要素配置位置 */
            divFilesizeRestrictionArea.appendChild(spanAnnotation); // div (ひ孫要素) の末尾に span を追加

            /* divタグ要素配置位置 */
            divFilesizeRestrictionArea.appendChild(spanRequired); // div (ひ孫要素) の末尾に span を追加

            /* img要素を動的に作成して画像を表示する */
            const CAMERA_URL = '<?php echo $camera_url; ?>';

            /* divタグ要素作成 */
            const divUploadfileArea = document.createElement("div");
            // class属性の値を追加
            divUploadfileArea.setAttribute('class', 'uploadfile-area');

            for (i = 1; i <= 3; i++) {
                /* divタグ要素作成 */
                let divUploadfileSelectorButton = document.createElement("div");
                // class属性の値を追加
                divUploadfileSelectorButton.setAttribute('class', 'uploadfile-selector-button');

                /* lavelタグ要素作成 */
                label = document.createElement("label");

                /* divタグ要素作成 */
                let divUploadfileCameraIcon = document.createElement("div");
                // class属性の値を追加
                divUploadfileCameraIcon.setAttribute('class', 'uploadfile-camera-icon');

                let img_unit = document.createElement('img');
                img_unit.src = CAMERA_URL; // 画像パス
                // class属性の値を追加
                img_unit.setAttribute('class', 'changeImg');
                // 要素のスタイルを取得・設定
                img_unit.style.height = '150px';
                img_unit.style.width = '150px';

                /* inputタグ要素作成 */
                // let inputAttach = document.createElement("input");
                inputAttach = document.createElement("input");
                /* input要素のtype属性を操作 */
                inputAttach.setAttribute('type', 'file');
                // class属性の値を追加
                inputAttach.setAttribute('class', 'attach');
                // name属性の値を追加
                inputAttach.setAttribute('name', 'attach[]');
                // datasetでdata属性（data-*）を設定する
                inputAttach.dataset.maxsize = '5';
                // accent属性の値を追加
                inputAttach.accept = ".png, .jpg, .jpeg, .pdf, .mp4"; // accept 値を設定
                // 要素のスタイルを取得・設定
                inputAttach.style.display = 'none';

                /* divタグ要素作成 */
                divViewer = document.createElement("div");
                // class属性の値を追加
                divViewer.setAttribute('class', 'viewer');
                // 要素のスタイルを取得・設定
                divViewer.style.display = 'none';

                /* buttonタグ要素作成 */
                buttonAttachclear = document.createElement("button");
                /* button要素のtype属性を操作 */
                buttonAttachclear.setAttribute("type", "button");
                // class属性の値を追加
                buttonAttachclear.setAttribute('class', 'attachclear');
                // タグにテキスト挿入
                buttonAttachclear.textContent = "clear";

                /* ファイルアップロード要素配置 */
                /* divタグ要素配置位置 */
                divUploadfileArea.appendChild(divUploadfileSelectorButton); // div (ひ孫要素) の末尾に div を追加
                divUploadfileSelectorButton.appendChild(label); // div (玄孫要素) の末尾に label を追加
                label.appendChild(divUploadfileCameraIcon); // label (来孫要素) の末尾に div を追加
                divUploadfileCameraIcon.appendChild(img_unit); // div (昆孫要素) の末尾に img を追加
                label.appendChild(inputAttach); // label (来孫要素) の末尾に input を追加
                divUploadfileSelectorButton.appendChild(divViewer); // div (玄孫要素) の末尾に div を追加
                divUploadfileSelectorButton.appendChild(buttonAttachclear); // div (玄孫要素) の末尾に button を追加
            }
            /* divタグ要素配置位置 */
            formAnswerInputForm.appendChild(divUploadfileArea); // form (孫要素) の末尾に div を追加

            /* divタグ要素配置位置 */
            formAnswerInputForm.appendChild(divPostButton); // form (孫要素) の末尾に div を追加

            /* buttonタグ要素配置位置 */
            divPostButton.appendChild(buttonCancelButton); // div (ひ孫要素) の末尾に button を追加

            /* buttonタグ要素配置位置 */
            divPostButton.appendChild(buttonSubmitButton); // div (ひ孫要素) の末尾に button を追加

            /* divタグ要素配置位置 */
            const divBoardRespond = document.querySelector('#js_board_respond');

            /* divタグ要素配置位置 */
            divBoardRespond.appendChild(divConfirmArea); // div (子要素) の末尾に div を追加

            /* divタグ要素配置位置 */
            divBoardRespond.appendChild(divResultArea); // div (子要素) の末尾に div を追加

            // divInputArea.appendChild(divConfirmArea); // div (子要素) の末尾に div を追加

            /* divタグ要素配置位置 */
            // divInputArea.appendChild(divResultArea); // div (子要素) の末尾に div を追加

            /* アップロードファイルについてのイベントを設定する処理 */
            set_attach_event('.uploadfile-camera-icon,.user-icon', 0);
        };
        // クリックイベントを追加
        textareaRichLabel.addEventListener("click", textareaRichLabelClick);
    }
</script>
<?php echo '</div>'; ?>
<script>
    /*
    function NowDate() { //送信日時を取り出す関数
        with(document.form1) { //"hidden"要素に各情報を設定する
            submitdate.value = NowDate();
        }
        // 週を計算
        Date.prototype.getWeek = function() {
            var onejan = new Date(this.getFullYear(), 0, 1);
            var today = new Date(this.getFullYear(), this.getMonth(), this.getDate());
            var dayOfYear = ((today - onejan + 86400000) / 86400000);
            return Math.ceil(dayOfYear / 7)
        };
        objDate = new Date(); //現在日時を取得
        // Y = objDate.getFullYear(); //年を取得
        M = objDate.getMonth(); //月を取得
        W = objDate.getWeek(); //週を取得
        D = objDate.getDate(); //日を取得
        H = objDate.getHours(); //時間を取得
        F = objDate.getMinutes(); //分を取得
        S = objDate.getSeconds(); //秒を取得
        // we = new Array("日", "月", "火", "水", "木", "金", "土"); //配列を作成
        // W = we[objDate.getDay()];
        //曜日を0～6の数値で取得し、その数値を配列Indexに割り当てる。
        // return "送信日時：" + Y + "年" + (M + 1) + "月" + D + "日" + H + "時" + F + "分" + S + "秒";
        const sum = ((M + 1) + D + H + F + S);
        return "sum";
    }
    window.addEventListener("DOMContentLoaded", () => { //DOMツリー読み込み完了後に発火
        // 開始日と現在の時間差を計算
        // new Date() が 20250207 のように設定されるのか分からないため引き算できるのか不明
        const diff = objDate - sum;

        // 年の数値を計算
        // const year = Math.floor(diff / 1000 / 60 / 60 / 24 / 30 / 12);
        // 月の数値を計算
        const month = Math.floor(diff / 1000 / 60 / 60 / 24 / 30) % 12;
        // 週の数値を計算
        const week = Math.floor(diff / 1000 / 60 / 60 / 24 / 7);
        // 日の数値を計算
        const day = Math.floor(diff / 1000 / 60 / 60 / 24);
        // 時間の数値を計算
        const time = Math.floor(diff / 1000 / 60 / 60);

        // 文字列を格納するための変数
        let outputStr = "";

        // 年のテキストを生成(0の場合は表示しない)
        /* if (year !== 0) {
            outputStr += `${year}年`;
        } 

        // 月のテキストを生成(0の場合は表示しない)
        if (month !== 0) {
            outputStr += `${month}ヶ月`;
        }

        // 週のテキストを生成(0の場合は表示しない)
        if (week !== 0) {
            outputStr += `${week}週`;
        }

        // 日のテキストを生成(0の場合は表示しない)
        if (day !== 0) {
            outputStr += `${day}日`;
        }

        // 時間のテキストを生成(0の場合は表示しない)
        if (time !== 0) {
            outputStr += `${time}時間`;
        }

        // 完成したテキストをpタグに挿入
        // skillTextEl.innerHTML = outputStr;
        const divAnswerPubdateArea = outputStr;
        divAnswerPubdateArea = document.createElement("div"); // div (子)を生成
        divAnswerPubdateArea.classList.add("answer-pubdate-area"); // classの追加
        /* 回答要素配置 */
    // comment_area.appendChild(); // comment_area (親要素) の末尾に div を追加 
    // });
    // });
    const input_area = document.getElementById("input_area");
    // const confirm_area = document.getElementById("confirm_area");
    // const result_area = document.getElementById("result_area");
    var name_value = "";
    var text_value = "";
    // 要素が3個の空配列を作成
    const blobType = ["", "", "", ""];
    const blobUrl = ["", "", "", ""];

    const init = function() {
        make();
        // set_attach_event('.uploadfile-camera-icon,.user-icon', 0);
        // document.getElementById("submit_button").addEventListener("click", submit_button_click);
        // change1();
        /* inputイベント */
        document.addEventListener('input', e => {
            /* 文字数表示 */
            display_text_length(e);
            /* 毎回判定によるボタン制御 */
            validation();
        });
        /* 初回判定のボタン制御 */
        // validation();
    } //const init = function () の終了
    //DOM構築、スタイルシート、画像、サブフレームの読み込みが完了した後に発生する
    window.addEventListener("DOMContentLoaded", init);

    const submit_button_click = function() {
        //ローディングアニメーション画像　　　
        const submit_button = document.getElementById("submit_button");
        //クリックしたときに無効にする
        submit_button.disabled = true;
        //クラスを追加
        submit_button.classList.add('wait');
        name_value = "";
        text_value = "";
        //サーバーにデータを送信する際に使用するオブジェクトを生成
        const formData = new FormData(answer_Input_form);
        //オブジェクト内の既存のキーに新しい値を追加
        formData.append("action", "bbs_answer_submit");
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
                if (json.error != "") {
                    alert(json.error);
                    return;
                }
                name_value = json.name;
                text_value = json.text;
                // const stamps = document.getElementsByName('stamp');
                /*for (var stamp of stamps) {
                  checkedプロパティは、対象の要素がcheckedを持っていればtrueを、持っていなければfalseを返す
                  if (stamp.checked) {
                    stamp_value = stamp.value;
                    break;
                  }
                }*/
                // change2(); これは恐らくステップフロー画像なので要らない
                // 空文字を入れることで要素内を空にできる
                const confirm_area = document.getElementById("confirm_area");
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

                /* タイトルラベル要素作成 */
                // const comment_area = document.createElement("div");
                const divTitleArea = document.createElement("div"); // div (子)を生成
                divTitleArea.classList.add("title-area"); // classの追加
                divTitleArea.textContent = "回答文"; //divタグにテキスト挿入

                /* フルブロック要素作成 */
                const comment_area = document.createElement("div");
                comment_area.classList.add("fullblock-area"); // classの追加

                /* 回答要素作成 */
                const divBodyPartialParts = create_text_parts("answer-text-area", text_value);

                /* アイコン画像要素作成 */
                // const comment_area = document.createElement("div");
                const divUserArea = document.createElement("div"); // div (子)を生成
                const divUserIcon = document.createElement("div"); // div (子)を生成
                divUserArea.classList.add("user-area"); // classの追加
                divUserIcon.classList.add("user-icon"); // classの追加
                child = document.createElement("p");

                /* 名前要素作成 */
                const usericonImg = document.createElement("img");
                const divNamePartialParts = create_name_parts("answer-name-area", name_value, usericonImg);

                /* コメント要素作成 */
                // const divBodyPartialParts = create_text_parts("answer-text-area", text_value);

                /* ファイルアップロード要素作成 */
                // const usericonImg = document.createElement("img");
                const divImagePartial = create_image_parts("uploadfile-area", 0, usericonImg);
                const image_area = document.createElement("div");

                /* アップロードファイルサイズ制限事項要素作成 */
                const divFilesizeRestrictionArea = document.createElement("div"); // div (子)を生成
                divFilesizeRestrictionArea.classList.add("filesize-restriction-area"); // classの追加
                divFilesizeRestrictionArea.textContent = "動画・画像をアップロード(Upload video・image)"; // 文字表示
                const spanFilesizeRestrictionArea = document.createElement("span");
                spanFilesizeRestrictionArea.classList.add("required");
                spanFilesizeRestrictionArea.textContent = "※ファイルサイズ15MB以内、JPG/GIF/PNG/MP4";

                /* タイトルラベル要素作成 */
                confirm_area.appendChild(divTitleArea); // comment_area (親要素) の末尾に div を追加

                /* 回答要素配置 */
                comment_area.appendChild(divBodyPartialParts); // comment_area (親要素) の末尾に div を追加

                /* アイコン画像要素配置 */
                // child = document.createElement("p"); と child.appendChild(document.createTextNode(○○○_value)); で 1セット
                // divUserIcon.appendChild(child); // div (子要素) の末尾に child を追加
                divUserIcon.appendChild(child); // div (子要素) の末尾に child を追加
                divUserArea.appendChild(divUserIcon); // div (子要素) の末尾に div を追加
                comment_area.appendChild(divUserArea); // comment_area (親要素) の末尾に div を追加

                /* 名前要素配置 */
                comment_area.appendChild(divNamePartialParts); // comment_area (親要素) の末尾に div を追加

                /* アップロードファイルサイズ制限事項要素配置 */
                comment_area.appendChild(divFilesizeRestrictionArea);

                /* アップロードファイルサイズ制限事項要素配置 */
                divFilesizeRestrictionArea.appendChild(spanFilesizeRestrictionArea);

                /* ファイルアップロード要素配置 */
                comment_area.appendChild(image_area);

                /* ファイルアップロード要素配置 */
                image_area.appendChild(divImagePartial);

                /* 確認画面送信ボタン要素作成 */
                const divPostButton = create_button_parts(2);
                confirm_area.appendChild(comment_area);
                // confirm_area.appendChild(image_area);
                confirm_area.appendChild(divPostButton); // confirm_area (親要素) の末尾に div を追加
                const image_count = image_area.getElementsByClassName("changeImg").length;
                if (image_count == 1) {
                    divUserArea.style.float = "left";
                } else if (image_count == 2) {
                    divUserArea.style.float = "left";
                    divBodyPartialParts.style.height = "728px"; //コメント欄外枠
                } else if (image_count == 3) {
                    Array.from(divUserArea.children).forEach(x => x.style.float = "left");
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
        formData.append("action", "bbs_answer_confirm");
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
                // change3();
                const buttons = document.querySelectorAll('.post-button');
                buttons.forEach(x => x.style.display = "none");
                // 確認画面と結果画面の親クラスで分ける 
                const result_area = document.getElementById("result_area");
                /* 質問文要素作成 */
                const divQuestHeaderTitle = document.createElement("div");

            })
            .catch(error => {
                console.log(error);
            });
    }
</script>


<!-- ここからフッター表示 -->
<?php get_footer(); ?>