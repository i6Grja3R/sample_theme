<?php
/*
Template Name: bbs_share_link
固定ページ: 共有ボタン
*/
?>
<style>
    .quest-shareButton svg {
        vertical-align: text-bottom;
    }

    .popup-wrapper {
        background-color: rgba(0, 0, 0, .5);
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
    }

    /* ポップアップ時に表示されるコンテンツ位置 */
    .popup-inside {
        text-align: center;
        width: 100%;
        max-width: 300px;
        background: white;
        margin: 10% auto;
        padding: 20px;
        position: relative;
    }

    .close {
        position: absolute;
        top: 0;
        right: 5px;
        cursor: pointer;
    }

    .sns-share-links {
        display: flex;
        align-items: center;
        gap: 0px 50px;
    }

    .spread-information img {
        width: 20px;
        height: 20px;
    }
</style>

<button type="button" class="quest-shareButton">
    <svg version="1.1" class="shareButton-icon" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="width: 256px; height: 256px; opacity: 1;" xml:space="preserve">
        <style type="text/css">
            .st0 {
                fill: #4B4B4B;
            }
        </style>
        <g>
            <path class="st0" d="M398.73,227.402c62.563,0,113.27-50.793,113.27-113.359c0-62.656-50.707-113.36-113.27-113.36
		c-62.656,0-113.364,50.704-113.364,113.36c0,11.587,1.733,22.711,4.926,33.292l-114.914,69.397
		c-18.512-20.154-44.959-32.739-74.417-32.739C45.146,183.993,0,229.228,0,284.954c0,55.816,45.146,100.962,100.962,100.962
		c30.736,0,58.278-13.778,76.79-35.482l86.824,45.787c-2.646,8.39-4.106,17.323-4.106,26.63
		c0.093,48.878,39.673,88.466,88.555,88.466c48.976,0,88.556-39.588,88.556-88.466c0-48.976-39.58-88.554-88.556-88.554
		c-26.812,0-50.886,11.942-67.122,30.825l-84.726-49.431c3.104-9.672,4.742-19.976,4.742-30.736c0-10.393-1.55-20.43-4.56-29.827
		l118.013-64.294C335.985,213.268,365.715,227.402,398.73,227.402z M344.282,59.687c14.045-13.956,33.11-22.524,54.448-22.524
		c21.251,0,40.31,8.567,54.356,22.524c13.862,13.956,22.434,33.016,22.434,54.356c0,21.25-8.572,40.399-22.434,54.354
		c-14.046,13.956-33.105,22.525-54.356,22.525c-19.059,0-36.298-6.84-49.794-18.419h-0.094c-1.55-1.273-3.099-2.645-4.56-4.106
		c-10.852-10.946-18.422-24.991-21.246-40.852c-0.824-4.382-1.189-8.942-1.189-13.502C321.846,92.703,330.419,73.644,344.282,59.687
		z M164.343,296.532c-2.28,13.138-8.661,24.902-17.781,34.022c-0.731,0.73-1.55,1.461-2.373,2.192
		c-11.49,10.393-26.536,16.69-43.227,16.69c-17.874,0-33.928-7.205-45.6-18.881c-11.676-11.765-18.881-27.725-18.881-45.6
		c0-17.874,7.205-33.834,18.881-45.6c11.672-11.676,27.726-18.881,45.6-18.881c16.232,0,30.825,5.932,42.225,15.782
		c1.185,0.997,2.28,2.004,3.376,3.099c9.027,9.12,15.413,20.698,17.781,33.746c0.73,3.83,1.095,7.748,1.095,11.854
		C165.438,288.873,165.074,292.801,164.343,296.532z M297.773,413.73c1.915-10.767,7.022-20.253,14.499-27.725
		c0.638-0.641,1.367-1.372,2.098-1.915c9.21-8.39,21.251-13.314,34.654-13.314c14.504,0,27.36,5.745,36.846,15.23
		c9.485,9.485,15.23,22.346,15.23,36.845c0,14.411-5.745,27.272-15.23,36.748c-9.486,9.486-22.342,15.238-36.846,15.238
		c-14.406,0-27.266-5.753-36.752-15.238c-9.485-9.476-15.23-22.337-15.322-36.748C296.95,419.751,297.225,416.643,297.773,413.73z" style="fill: rgb(75, 75, 75);"></path>
        </g>
    </svg>
    <div class="share-info-text">共有（share）</div>
</button>

<div class="popup-wrapper">
    <div class="popup-inside">
        <div class="close">x</div>
        <div class="spread-information">
            <!-- LINE -->
            <a class="sns-link" href="//timeline.line.me/social-plugin/share?url=&text=" target="_blank" rel="nofollow noopener noreferrer">
                <img src="http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/line.png">
            </a>

            <!-- X -->
            <a class="sns-link" href="//x.com/intent/post?text=&url=" target="_blank" rel="nofollow noopener noreferrer">
                <img src="http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/x.png">
            </a>

            <!-- Facebook -->
            <a class="sns-link" href="//www.facebook.com/sharer/sharer.php?u=&t=" target="_blank" rel="nofollow noopener noreferrer">
                <img src="http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/facebook.png">
            </a>

            <!-- ピンタレスト -->
            <a class="sns-link" href="//www.pinterest.com/pin/create/button/?url=&media=" target="_blank" rel="nofollow noopener noreferrer">
                <img src="http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/pinterest.png">
            </a>

            <!-- reddit
            <a class="sns-link" href="//www.reddit.com/submit?url=" target="_blank" rel="nofollow noopener noreferrer">
                <img src="http://www.irasuto.cfbx.jp/wp-content/themes/sample_theme/images/reddit.png">
            </a> -->
        </div>

        <input type="text" class="scope-renderer">
        <div class="shape-text"></div>
    </div>
</div>

// 今開いてるページをシェア
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    const questShareButton = document.querySelector('.quest-shareButton');
    const popupWrapper = document.querySelector('.popup-wrapper');
    const popupInside = document.querySelector('.popup-inside'); // 追加
    const spreadInformation = document.querySelector('.spread-information'); // SNSのシェアリンクアイコン
    const divScopeRenderer = document.querySelector(".scope-renderer"); // テキストボックス
    const divShapeText = document.querySelector(".shape-text"); // コピーするボタン

    // ボタンをクリックしたときにポップアップを表示させる
    questShareButton.addEventListener('click', () => {
        popupWrapper.style.display = "block";

        /* テキストボックスに出力されたURL表示 */
        // divScopeRenderer.innerHTML("textboxHref");
        divScopeRenderer.value = location.href;

        /* コピーするボタン要素作成 */
        // const divShapeText = document.createElement("div");
        // divShapeText.classList.add("shape-text"); // classの追加
        divShapeText.textContent = "コピーする"; // 文字表示

        // execCommandは廃止されている機能で利用が非推奨
        // スマートフォンでHTMLタグのonclick属性の挙動がおかしくしっかり作動しないことがあるので、今回はbuttonタグで作成
        // onclick だとイベントが重複登録され押すごとに追加される
        /* コピーするボタンをクリック後コピーしましたに変更 */
        divShapeText.addEventListener('click', () => {
            divShapeText.textContent = "コピーされました";
        });

        // ボタンをクリックしたときに HTML を生成
        /* テキストボックス要素作成 */
        // const divScopeRenderer = document.createElement("input");
        // divScopeRenderer.type = 'text';
        // divScopeRenderer.classList.add("scope-renderer"); // classの追加

        /* テキストボックス要素配置 */
        // popupInside.appendChild(divScopeRenderer); // popupInside (親要素) の末尾に div を追加

        /* コピーするボタン要素配置 */
        // popupInside.appendChild(divShapeText); // popupInside (親要素) の末尾に div を追加
    });

    /* コピーするボタンをクリック */
    divShapeText.addEventListener('click', () => {
        /* select() で設定したURLを選択した状態にする */
        divScopeRenderer.select();

        /* document.execCommand('copy') でコピーを実行する */
        document.execCommand('copy');
    });

    // ポップアップの外側又は「x」のマークをクリックしたときポップアップを閉じる
    popupWrapper.addEventListener('click', e => {
        if ((e.target.id === popupWrapper.id || e.target.id === close.id) && !e.target.classList.contains("shape-text")) {
            popupWrapper.style.display = 'none';
        }
    });

    let snsLinks = document.querySelectorAll(".sns-link")
    snsLinks.forEach(snsLink => {
        let href = snsLink.getAttribute('href');
        href = href.replace("u=", "u=" + url)
        href = href.replace("url=", "url=" + url)
        snsLink.setAttribute('href', href);
    });
</script>