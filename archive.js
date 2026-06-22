jQuery(document).ready(function ($) {
            // @link: http://kachibito.net/snippets/basic-panel
            //年月別アーカイブ表示用
            $(".archive-list li").click(function () {
                //@link: https://stackoverflow.com/questions/6656202/jquery-slidedown-child-ul
                if ($(this).next(".month-archive-list").is(":visible") || $(this).hasClass("acv_open")) { //既に開いている場所なら
                    $(".month-archive-list", this).slideUp("fast"); //閉じる
                    $(this).removeClass("acv_open"); //.acv_open削除

                }
                else { //閉じている場所なら
                    $(this).siblings().children(".month-archive-list").slideUp("fast"); //その他のリストを閉じる
                    $(".month-archive-list", this).slideDown("fast"); //開く
                    $(".year").removeClass("acv_open"); //.acv_open削除
                    $(this).addClass("acv_open"); //.acv_open付加
                }
            });
        });