<?php
/*
Template Name: que_list
固定ページ: 質問一覧画面
*/
get_header();
$sql = "SELECT * FROM {$wpdb->prefix}sortable WHERE parent_id IS NULL AND attach1 LIKE '%.mp4' ORDER BY RAND() LIMIT 5";
$query = $wpdb->prepare($sql);
$rows = $wpdb->get_results($query);
$upload_dir = wp_upload_dir();
$carousel_list = [];
$indicator_list = [];
if (empty($rows)) {
    $carousel_area_style = 'height:0;';
} else {
    $carousel_area_style = '';
}
foreach ($rows as $row) {
    $carousel_list[] = '
<div class="carousel-container">
    <div class="carousel-video-area" style="">
        <a href="' . home_url('質問回答画面?' . $row->unique_id) . '">
            <div class="carousel-video-wrap">
                <video class="carousel-video-streaming" src="' . $upload_dir['baseurl'] . '/attach/' . $row->attach1 . '#t=0.1" type="video/mp4" autoplay muted loop></video>
            </div>
            <div class="carousel-video-title">
            <script>
            document.currentScript.insertAdjacentHTML("beforebegin", truncate("' . $row->title . '",39));
            </script>
            </div>
        </a>
    </div>
</div>';
    $indicator_list[] = '<li class="list"></li>';
}
?>
<script>
    function truncate(str, len) {
        return str.length <= len ? str : (str.substr(0, len) + "...");
    };
</script>
<div id="feed4-layout">
    <div class="inline-player">
        <!-- スライドの外枠 -->
        <div class="carousel-area" style="<?php echo $carousel_area_style; ?>">
            <!-- スライド（コンテンツ） -->
            <div id="carousel" class="carousel">
                <?php echo implode('', $carousel_list); ?>
            </div>
            <!-- 左右のボタン -->
            <span id="prev" class="prev"></span>
            <span id="next" class="next"></span>
            <!-- インジケーター -->
            <ul class="indicator" id="indicator">
                <?php echo implode('', $indicator_list); ?>
            </ul>
        </div>
    </div>
    <div class="footer-test">
        <p>© All rights reserved by dmmwebcampmedia.</p>
    </div>
</div>
<script>
    const slide = document.getElementById('carousel');
    const prev = document.getElementById('prev');
    const next = document.getElementById('next');
    const indicator = document.getElementById('indicator');
    const lists = document.querySelectorAll('.list');
    // インジケーターの要素数
    const totalSlides = lists.length;
    // スライドのパーセントは、要素数　totalSlides　が 1 の時 100　、2　ならば　50、3　ならば　33.33...、4　ならば　25、5　ならば　20　
    const slidePercent = 100 / totalSlides;
    // 初期の要素数を0にする
    let currentSlide = 0;
    // 自動再生に使用する変数を用意
    let autoPlayInterval;

    // 要素数からパラメータ値を算出し、カスタムパラメータとして設定する
    var width = totalSlides * 100;
    $("#carousel").css({
        "--w": width + "%"
    });
    /* ループ処理ではないから必要ない？
    for (var i=0; i<num; i++) {
    // 要素を追加してしまう
    $("#color-circle").append($("<div>")
     .addClass("item")
     .css({"--i":i+""}));
    }
    */

    // スライドのクラスを更新する為にクラスを削除する　例 2枚目 → 1枚目
    function updateIndicator() { //表示スライドに対応してインジケーターのクラス名を変更？
        // 与えられた関数 (lists) を、配列の各要素 (list, index) に対して一度ずつ実行
        // forやforeachで現在処理中の配列の要素の index を知りたい場合
        // オブジェクトを管理しているのが配列の要素で、先頭から順番にインデックスと呼ばれる番号が割り当てられており、インデックスを指定することで配列から特定の要素を取り出すことができる
        lists.forEach((list, index) => {
            // list にクラス名があれば、クラス名を除去し、クラス名がなければ追加 (active,厳密等価演算子、厳密に等しいならば真を返す)
            list.classList.toggle('active', index === currentSlide);
        });
    }

    function nextSlide() {
        // % は、算術演算子の一つで、余りを計算します。（例: 9 % 2 ⇒ 1）
        // よって、(currentSlide + 1) % totalSlides は、(currentSlide + 1) を totalSlides で割った余りを返します。こうすることで、スライド番号をループさせることができます。
        // +1を足す理由は、スライド番号が1から始まるように調整しているため
        currentSlide = (currentSlide + 1) % totalSlides;

        // 表示するためのCSS（全体の 1/5 右に移動）
        slide.style.transform = `translateX(-${currentSlide * slidePercent}%)`;
        updateIndicator();
    }

    function prevSlide() {
        // % は、算術演算子の一つで、余りを計算します。（例: 9 % 2 ⇒ 1）
        // よって、(currentSlide - 1 + totalSlides) % totalSlides は、(currentSlide - 1 + totalSlides) を totalSlides で割った余りを返します。こうすることで、スライド番号をループさせることができます。
        // +1を足す理由は、スライド番号が1から始まるように調整しているため
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;

        // 表示するためのCSS（全体の 1/5 左に移動）
        slide.style.transform = `translateX(-${currentSlide * slidePercent}%)`;
        updateIndicator();
    }

    function startAutoPlay() {
        // 30秒ごとに自動でスライドを切り替える関数（startAutoPlay）
        // 30000 は30000ミリ秒 = 30秒を表します
        autoPlayInterval = setInterval(nextSlide, 30000);
    }

    function resetAutoPlayInterval() {
        // 自動再生をリセットする関数（resetAutoPlayInterval）
        // 30秒ごとの自動再生を一旦停止します
        clearInterval(autoPlayInterval);
        // 自動再生を再度スタートさせる
        startAutoPlay();
    }
    // 進むボタンクリックイベントを発生させる
    next.addEventListener('click', () => {
        nextSlide();
        resetAutoPlayInterval();
    });
    // 戻るボタンクリックイベントを発生させる
    prev.addEventListener('click', () => {
        prevSlide();
        resetAutoPlayInterval();
    });
    // インジケーターをクリックすることで「対応スライドを表示させる」
    // ↓クリックイベントのリスナーを登録
    indicator.addEventListener('click', (event) => {
        // クリックされた要素が .list クラスを持つかどうかを判定しています
        // event.target はクリックされた要素を指し、classList.contains('list') はその要素のクラスリストに 'list' クラスが含まれているかどうかをチェックする
        if (event.target.classList.contains('list')) {
            // クリックされた要素が .list クラスを持つ要素である場合、lists 配列からその要素のインデックスを取得する
            // Array.from(lists) を使って lists 配列を新しい配列に変換しており、indexOf(event.target) でクリックされた要素のインデックスを検索する
            // これにより、定数indexには何番目のlistがクリックされたかの数値が入る
            const index = Array.from(lists).indexOf(event.target);
            currentSlide = index;

            slide.style.transform = `translateX(-${currentSlide * slidePercent}%)`;
            updateIndicator();
            resetAutoPlayInterval();
        }
    });
    startAutoPlay();

    //ここから無限スクロール
    let contentsCount = 0; //countから変更
    let adding = false; //add処理判定フラグ（初期値：add未処理に設定）
    $(function() {
        add()
        $(window).on("scroll", function() {
            if ($(window).scrollTop() + $(window).height() >= $("html").height()) {
                add()
            }
        })
    });

    function add() {
        //add処理判定
        if (adding) {
            //add処理中のため抜ける
            return;
        }
        adding = true; //add処理中に設定
        const formData = new FormData();
        formData.append("action", "bbs_que_list_items");
        formData.append("count", contentsCount); //countから変更
        const opt = {
            method: "post",
            body: formData
        }
        fetch("<?php echo home_url('wp-admin/admin-ajax.php'); ?>", opt)
            .then(response => {
                return response.json();
            })
            .then(json => {
                for (let idx = 0; idx < json.items.length; idx++) {
                    contentsCount++; //countから変更
                    const item = json.items[idx];
                    const imageCardWrap = document.createElement("div");
                    imageCardWrap.classList.add("image-card-wrap");
                    //imageCardWrap.style.display = "inline-block";
                    const link = document.createElement("a");
                    link.href = item.url;
                    const imageThumbnailCard = document.createElement("div");
                    imageThumbnailCard.classList.add("image-thumbnail-card");
                    const img = document.createElement("img");
                    if (item.type == "img") {
                        img.src = item.img1;
                        img.style.height = "150px";
                        img.style.width = "263px";
                    } else {
                        img.src = "../wp-content/themes/sample_theme/images/alternative.png";
                        img.style.height = "150px";
                        img.style.width = "263px";
                    }
                    imageThumbnailCard.appendChild(img);
                    link.appendChild(imageThumbnailCard);
                    const imageTitleLink = document.createElement("div");
                    imageTitleLink.classList.add("image-title-link");
                    imageTitleLink.textContent = truncate(item.title, 25);
                    link.appendChild(imageTitleLink);
                    imageCardWrap.appendChild(link);
                    $(".inline-player").append(imageCardWrap);
                }
                adding = false; //add未処理に設定
            })
            .catch(error => {
                console.log(error);
            });
    };
</script>