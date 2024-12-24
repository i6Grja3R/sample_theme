<?php
/*
Template Name: bbs_lile_count
固定ページ: いいねボタン
*/
?>
<style>
    .quest_likeButton svg {
        vertical-align: text-bottom;
    }
</style>

<button type="button" class="quest_likeButton">
    <svg version="1.1" class="likeButton_icon" id="レイヤー_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
        y="0px" viewBox="0 0 256 256" style="enable-background:new 0 0 256 256;" xml:space="preserve">
        <style type="text/css">
            .st0 {
                fill: #FFFFFF;
                stroke: #000000;
                stroke-width: 8.7931;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-miterlimit: 10;
            }
        </style>
        <path class="st0" d="M101.5,175.5c3.9,5.9,16.6,9.3,16.6,9.3h58.6c13.2-4.4,5.9-17.1,5.9-17.1s7.8-1,11.2-9.3
	c3.4-8.3-3.4-12.7-3.4-12.7s10.3-1.5,10.3-11.2c0-12.2-11.7-10.3-11.7-10.3s10.7,1,10.7-11.7s-11.2-10.7-11.2-10.7h-40.1
	c0,0,2.9-8.3,3.4-14.7c0.5-6.4,2.9-18.6-7.8-28.8s-17.1-4.4-17.1-4.4v22.5c0,0,0.1,5.1-3.4,10.1l-15.3,29.7l-6.7,4.6" />
        <path class="st0" d="M101.5,120.8v59.6c0,0,0.5,7.3-4.9,7.3H65.4c0,0-6.4-0.5-6.4-4.9v-60.1c0,0,0.5-8.3,4.9-8.3h30.8
	C94.7,114.4,101.5,113.4,101.5,120.8z" />
    </svg>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() { // キーが押された瞬間に一度だけ発生
        //function countClickbutton() {
        // カウント用の変数
        let count = 0;
        // いいねボタンの要素を取得
        const likeButtonIcon = document.querySelector("likeButton_icon");
        // 取得したいいねボタンがクリックされた時、カウントを1つ増やして再代入する
        likeButtonIcon.addEventListener("click", function() {
            count++;
            // ボタンが押された時にいいねされた状態の見た目に変更する
            likeButtonIcon.classList.toggle('liked');
        })
        // }
    }, false);
</script>