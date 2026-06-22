/* ===================================================
   mobile_tablet_home_target.js コメント詳解版
   ・元ファイル：mobile_tablet_home_target(47).js
   ・各コード行の直前に「// 説明:」で意味を追加
   ・動作自体は元コードと同じになるよう、コメントだけを追加
   ・学習用なのでコメント量が多いです。本番では元ファイルでもOKです。
=================================================== */

/* ===================================================
   スマホ＋タブレット用：表示順変更
   ・月別アーカイブをブログ内検索の上へ移動
   ・月別アーカイブは＋ボタンで開閉
   ・最近のコメントを本文より上へ移動
   ・ランキングはスマホ＋タブレットでは移動しない
   ・PCでは元の位置へ戻す
=================================================== */
// 関数を定義すると同時に実行するための構文
// 説明: この処理だけで使う変数を外へ漏らさないため、即時関数を開始します。
(function () {
    // 説明: 厳格モードを有効にして、JavaScriptのミスを検出しやすくします。
    'use strict';

    // ブレイクポイントの切り替わりに応じて処理を実行
    // 説明: スマホ幅かどうかを判定する条件に、画面幅の判定条件を入れます。
    var mediaQuery = window.matchMedia('(max-width: 599px)');

    // 説明: 最近のコメントの元の親要素を保存する変数を、まだ何も入っていない状態で用意します。
    var commentOriginalParent = null;
    // 説明: 最近のコメントを元の場所に戻すための目印コメントを、まだ何も入っていない状態で用意します。
    var commentOriginalMarker = null;

    // 説明: 月別アーカイブの元の親要素を保存する変数を、まだ何も入っていない状態で用意します。
    var archiveOriginalParent = null;
    // 説明: 月別アーカイブを元の場所に戻すための目印コメントを、まだ何も入っていない状態で用意します。
    var archiveOriginalMarker = null;

    // 説明: rememberOriginalPosition 関数を定義します。引数は「element, type」です。
    function rememberOriginalPosition(element, type) {
        // 説明: 条件「!element」を満たす場合だけ、次の処理を実行します。
        if (!element) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「type === 'comment' && !commentOriginalMarker」を満たす場合だけ、次の処理を実行します。
        if (type === 'comment' && !commentOriginalMarker) {
            // 説明: commentOriginalParent に「element.parentNode」を代入します。
            commentOriginalParent = element.parentNode;
            // 説明: commentOriginalMarker に「document.createComment('original commentlist position')」を代入します。
            commentOriginalMarker = document.createComment('original commentlist position');
            // 参照先ノードの前にこの親ノードの子としてノードを挿入
            // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
            commentOriginalParent.insertBefore(commentOriginalMarker, element);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「type === 'archive' && !archiveOriginalMarker」を満たす場合だけ、次の処理を実行します。
        if (type === 'archive' && !archiveOriginalMarker) {
            // 説明: archiveOriginalParent に「element.parentNode」を代入します。
            archiveOriginalParent = element.parentNode;
            // 説明: archiveOriginalMarker に「document.createComment('original archive position')」を代入します。
            archiveOriginalMarker = document.createComment('original archive position');
            // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
            archiveOriginalParent.insertBefore(archiveOriginalMarker, element);
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: setupArchiveToggle 関数を定義します。引数は「archive」です。
    function setupArchiveToggle(archive) {
        // 説明: 条件「!archive」を満たす場合だけ、次の処理を実行します。
        if (!archive) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 見出し・タイトル要素に、条件に合う最初のHTML要素を取得して入れます。
        var title = archive.querySelector('.side-title');

        // 説明: 条件「!title」を満たす場合だけ、次の処理を実行します。
        if (!title) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 対象要素にHTML属性を設定します。
        title.setAttribute('role', 'button');
        // 説明: 対象要素にHTML属性を設定します。
        title.setAttribute('tabindex', '0');
        // 説明: 対象要素に指定クラスが付いているか確認します。
        title.setAttribute('aria-expanded', archive.classList.contains('is-open') ? 'true' : 'false');

        // 説明: 条件「title.getAttribute('data-archive-toggle-ready') === '1'」を満たす場合だけ、次の処理を実行します。
        if (title.getAttribute('data-archive-toggle-ready') === '1') {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 対象要素にHTML属性を設定します。
        title.setAttribute('data-archive-toggle-ready', '1');

        // 説明: toggleArchive 関数を定義します。引数は「なし」です。
        function toggleArchive() {
            // 説明: 条件「!mediaQuery.matches」を満たす場合だけ、次の処理を実行します。
            if (!mediaQuery.matches) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 対象要素のCSSクラスを、付いていれば外し、無ければ付けます。
            archive.classList.toggle('is-open');
            // 説明: 対象要素に指定クラスが付いているか確認します。
            title.setAttribute('aria-expanded', archive.classList.contains('is-open') ? 'true' : 'false');
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        title.addEventListener('click', toggleArchive);

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        title.addEventListener('keydown', function (event) {
            // 説明: 条件「event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar'」を満たす場合だけ、次の処理を実行します。
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                // イベントが発生したときに、ブラウザが本来行う動作をキャンセルするメソッド
                // 説明: クリックやキー入力などのブラウザ標準動作を止めます。
                event.preventDefault();
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                toggleArchive();
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: コールバック関数または処理ブロックを閉じます。
        });
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: moveForMobile 関数を定義します。引数は「なし」です。
    function moveForMobile() {
        // 説明: メイン全体の要素に、条件に合う最初のHTML要素を取得して入れます。
        var mainBox = document.querySelector('#main-box');
        // 説明: 右側コンテンツの要素に、条件に合う最初のHTML要素を取得して入れます。
        var rightBox = document.querySelector('#right-box');
        // 説明: 最近のコメント一覧の要素に、条件に合う最初のHTML要素を取得して入れます。
        var commentList = document.querySelector('.commentlist');
        // 説明: 月別アーカイブの要素に、条件に合う最初のHTML要素を取得して入れます。
        var archive = document.querySelector('.widget_archive');
        // 説明: 検索フォームの要素に、条件に合う最初のHTML要素を取得して入れます。
        var searchForm = document.querySelector('form#searchform');

        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        document.documentElement.classList.add('is-mobile-tablet-home');

        // 説明: 条件「archive && searchForm && searchForm.parentNode」を満たす場合だけ、次の処理を実行します。
        if (archive && searchForm && searchForm.parentNode) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            rememberOriginalPosition(archive, 'archive');
            // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
            archive.classList.add('mobile-search-archive');
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            setupArchiveToggle(archive);

            // 指定した要素の「次にある兄弟要素」を取得
            // 説明: 条件「archive.nextElementSibling !== searchForm」を満たす場合だけ、次の処理を実行します。
            if (archive.nextElementSibling !== searchForm) {
                // 参照先ノードの前にこの親ノードの子としてノードを挿入
                // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
                searchForm.parentNode.insertBefore(archive, searchForm);
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「commentList && mainBox && rightBox」を満たす場合だけ、次の処理を実行します。
        if (commentList && mainBox && rightBox) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            rememberOriginalPosition(commentList, 'comment');

            // 説明: 条件「commentList.parentNode !== mainBox」を満たす場合だけ、次の処理を実行します。
            if (commentList.parentNode !== mainBox) {
                // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
                mainBox.insertBefore(commentList, rightBox);
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: restoreForPc 関数を定義します。引数は「なし」です。
    function restoreForPc() {
        // 説明: 最近のコメント一覧の要素に、条件に合う最初のHTML要素を取得して入れます。
        var commentList = document.querySelector('.commentlist');
        // 説明: 月別アーカイブの要素に、条件に合う最初のHTML要素を取得して入れます。
        var archive = document.querySelector('.widget_archive');
        // 説明: 月別アーカイブの見出し要素に、条件に合う最初のHTML要素を取得して入れます。
        var archiveTitle = archive ? archive.querySelector('.side-title') : null;

        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.documentElement.classList.remove('is-mobile-tablet-home');

        // 説明: 条件「archive && archiveOriginalParent && archiveOriginalMarker && archiveOriginalMarker.parentNode === archiveOriginalParent」を満たす場合だけ、次の処理を実行します。
        if (archive && archiveOriginalParent && archiveOriginalMarker && archiveOriginalMarker.parentNode === archiveOriginalParent) {
            // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
            archive.classList.remove('mobile-search-archive');
            // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
            archive.classList.remove('is-open');

            // 説明: 条件「archiveTitle」を満たす場合だけ、次の処理を実行します。
            if (archiveTitle) {
                // 説明: 対象要素にHTML属性を設定します。
                archiveTitle.setAttribute('aria-expanded', 'false');
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
            archiveOriginalParent.insertBefore(archive, archiveOriginalMarker.nextSibling);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「commentList && commentOriginalParent && commentOriginalMarker && commentOriginalMarker.parentNode === commentOriginalParent」を満たす場合だけ、次の処理を実行します。
        if (commentList && commentOriginalParent && commentOriginalMarker && commentOriginalMarker.parentNode === commentOriginalParent) {
            // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
            commentOriginalParent.insertBefore(commentList, commentOriginalMarker.nextSibling);
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: applyMobileLayout 関数を定義します。引数は「なし」です。
    function applyMobileLayout() {
        // 説明: 条件「mediaQuery.matches」を満たす場合だけ、次の処理を実行します。
        if (mediaQuery.matches) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            moveForMobile();
        // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
        } else {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            restoreForPc();
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「document.readyState === 'loading'」を満たす場合だけ、次の処理を実行します。
    if (document.readyState === 'loading') {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        document.addEventListener('DOMContentLoaded', applyMobileLayout);
    // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
    } else {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        applyMobileLayout();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「mediaQuery.addEventListener」を満たす場合だけ、次の処理を実行します。
    if (mediaQuery.addEventListener) {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        mediaQuery.addEventListener('change', applyMobileLayout);
    // 説明: 前の条件に当てはまらず、条件「mediaQuery.addListener」を満たす場合に実行します。
    } else if (mediaQuery.addListener) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        mediaQuery.addListener(applyMobileLayout);
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 即時関数の定義を閉じ、その場で実行します。
}());


/* ===================================================
   記事一覧メタ情報の文字整形
   ・Font Awesomeなどのアイコン文字をメタ情報から除去
   ・カテゴリーをスマホの記事一覧では非表示
   ・日付の "-" を "/" に変換
   ・「1件のコメント」などを「コメント(1)」へ変換
=================================================== */
// 説明: fixMobileArticleMetaText 関数を定義します。引数は「なし」です。
function fixMobileArticleMetaText() {
    // 説明: 厳格モードを有効にして、JavaScriptのミスを検出しやすくします。
    'use strict';

    // ブレイクポイントの切り替わりに応じて処理を実行する
    // 説明: 条件「!window.matchMedia('(max-width: 599px)').matches」を満たす場合だけ、次の処理を実行します。
    if (!window.matchMedia('(max-width: 599px)').matches) {
        // 説明: これ以上処理せず、この関数を終了します。
        return;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 取得した要素を1つずつ取り出し、「meta」として処理します。
    document.querySelectorAll('.entry-post .post-meta').forEach(function (meta) {

        // 説明: 取得した要素を1つずつ取り出し、「icon」として処理します。
        meta.querySelectorAll('.fa-fw, .fa, .fas, .far, i, .material-icons').forEach(function (icon) {
            // 説明: 対象要素をHTML上から削除します。
            icon.remove();
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 取得した要素を1つずつ取り出し、「category」として処理します。
        meta.querySelectorAll('.category-link').forEach(function (category) {
            // 説明: 対象要素をHTML上から削除します。
            category.remove();
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 取得した要素を1つずつ取り出し、「node」として処理します。
        meta.querySelectorAll('span, a').forEach(function (node) {
            //  子要素の件数を取得
            // 説明: 条件「node.childElementCount === 0」を満たす場合だけ、次の処理を実行します。
            if (node.childElementCount === 0) {
                // 説明: node.textContent の表示文字を「node.textContent.replace(」に変更します。textContentなのでHTMLとして実行されません。
                node.textContent = node.textContent.replace(
                    // 説明: 前の処理の続きとして、この値を並べて指定します。
                    /(\d{4})-(\d{2})-(\d{2})/g,
                    // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                    '$1/$2/$3'
                // 説明: 直前から続く複数行の処理を閉じます。
                );
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: commentBox 用の変数に、条件に合う最初のHTML要素を取得して入れます。
        var commentBox = meta.querySelector('.comment-count');
        // 説明: 条件「commentBox」を満たす場合だけ、次の処理を実行します。
        if (commentBox) {
            // 説明: target 用の変数に、条件に合う最初のHTML要素を取得して入れます。
            var target = commentBox.querySelector('a') || commentBox;
            // 説明: 元の文字列に「target.textContent.trim()」の結果を入れます。
            var raw = target.textContent.trim();
            // 説明: 文字列から取り出した数字の一致結果に「raw.match(/\d+/)」の結果を入れます。
            var numberMatch = raw.match(/\d+/);

            // 説明: 条件「numberMatch」を満たす場合だけ、次の処理を実行します。
            if (numberMatch) {
                // 説明: target.textContent の表示文字を「'コメント(' + numberMatch[0] + ')'」に変更します。textContentなのでHTMLとして実行されません。
                target.textContent = 'コメント(' + numberMatch[0] + ')';
                // 指定の文字列が含まれているか検索し見つかった場合は位置を返す
            // 説明: 前の条件に当てはまらず、条件「raw.indexOf('コメント') !== -1」を満たす場合に実行します。
            } else if (raw.indexOf('コメント') !== -1) {
                // 説明: target.textContent の表示文字を「'コメント(0)'」に変更します。textContentなのでHTMLとして実行されません。
                target.textContent = 'コメント(0)';
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: コールバック関数または処理ブロックを閉じます。
    });
// 説明: 現在の処理ブロックを閉じます。
}

// 説明: 指定したイベントが発生した時に実行する処理を登録します。
document.addEventListener('DOMContentLoaded', fixMobileArticleMetaText);
// 説明: 指定したイベントが発生した時に実行する処理を登録します。
window.addEventListener('resize', fixMobileArticleMetaText);


/* ===================================================
   スマホ用ページナビ整形
   目的：
   ・元の「<< < 1 2 > >>」形式を、スマホだけ
     「1 / 全ページ」＋「≪ / 前へ / 次へ / ≫」へ組み替える
   ・PCに戻ったら元のHTMLへ復元する
=================================================== */
// 説明: この処理だけで使う変数を外へ漏らさないため、即時関数を開始します。
(function () {
    // 説明: 厳格モードを有効にして、JavaScriptのミスを検出しやすくします。
    'use strict';

    // 説明: スマホ幅かどうかを判定する条件に、画面幅の判定条件を入れます。
    var mediaQuery = window.matchMedia('(max-width: 599px)');

    // 説明: getPageParamName 関数を定義します。引数は「nav」です。
    function getPageParamName(nav) {
        // 説明: ページ番号に使われる可能性があるURLパラメータ名の配列として、複数の値を入れる配列を作ります。
        var names = ['cp', 'paged', 'page'];
        // オブジェクトを作る関数 配列風オブジェクトやコレクションを新しい配列に変換
        // 説明: 対象内のリンク要素を配列化したものに、条件に合う複数のHTML要素を取得して入れます。
        var links = Array.prototype.slice.call(nav.querySelectorAll('a[href]'));

        // 説明: 条件「var i = 0; i < links.length; i += 1」に従って繰り返し処理を行います。
        for (var i = 0; i < links.length; i += 1) {
            // 説明: URL解析などでエラーが出ても止まらないように試行処理を開始します。
            try {
                // 説明: URLを扱いやすくするためのURLオブジェクトに、URL文字列を解析しやすいURLオブジェクトとして入れます。
                var url = new URL(links[i].getAttribute('href'), window.location.href);
                // 説明: 条件「var n = 0; n < names.length; n += 1」に従って繰り返し処理を行います。
                for (var n = 0; n < names.length; n += 1) {
                    // 指定された値をもつ要素がこの集合内に存在するかどうかを示す
                    // 説明: 条件「url.searchParams.has(names[n])」を満たす場合だけ、次の処理を実行します。
                    if (url.searchParams.has(names[n])) {
                        // 説明: 計算・取得した結果「names[n];」を呼び出し元へ返します。
                        return names[n];
                    // 説明: 現在の処理ブロックを閉じます。
                    }
                // 説明: 現在の処理ブロックを閉じます。
                }
            // 説明: try内でエラーが出た場合でも、何もしないで処理を続けます。
            } catch (e) {}
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「'cp';」を呼び出し元へ返します。
        return 'cp';
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getPageFromHref 関数を定義します。引数は「href, paramName」です。
    function getPageFromHref(href, paramName) {
        // 説明: 条件「!href」を満たす場合だけ、次の処理を実行します。
        if (!href) {
            // 説明: 計算・取得した結果「null;」を呼び出し元へ返します。
            return null;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: URL解析などでエラーが出ても止まらないように試行処理を開始します。
        try {
            // 説明: URLを扱いやすくするためのURLオブジェクトに、URL文字列を解析しやすいURLオブジェクトとして入れます。
            var url = new URL(href, window.location.href);
            // 説明: URLクエリから取得したページ番号に「url.searchParams.get(paramName) || url.searchParams.get('cp') || url.searchParams.get('paged') || url.searchParams.get('page')」の結果を入れます。
            var byQuery = url.searchParams.get(paramName) || url.searchParams.get('cp') || url.searchParams.get('paged') || url.searchParams.get('page');

            // 説明: 条件「byQuery && /^\d+$/.test(byQuery)」を満たす場合だけ、次の処理を実行します。
            if (byQuery && /^\d+$/.test(byQuery)) {
                // 文字列を整数に変換した値を返す
                // 説明: 計算・取得した結果「parseInt(byQuery, 10);」を呼び出し元へ返します。
                return parseInt(byQuery, 10);
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: URLパスから取得したページ番号の正規表現結果に「url.pathname.match(/\/page\/(\d+)\/?/)」の結果を入れます。
            var byPath = url.pathname.match(/\/page\/(\d+)\/?/);
            // 説明: 条件「byPath」を満たす場合だけ、次の処理を実行します。
            if (byPath) {
                // 説明: 計算・取得した結果「parseInt(byPath[1], 10);」を呼び出し元へ返します。
                return parseInt(byPath[1], 10);
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: try内でエラーが出た場合でも、何もしないで処理を続けます。
        } catch (e) {}

        // 説明: 計算・取得した結果「null;」を呼び出し元へ返します。
        return null;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getCurrentPage 関数を定義します。引数は「nav, paramName」です。
    function getCurrentPage(nav, paramName) {
        // URL全体を取得
        // 説明: 現在URLから取得したページ番号に「getPageFromHref(window.location.href, paramName)」の結果を入れます。
        var fromUrl = getPageFromHref(window.location.href, paramName);

        // 説明: 条件「fromUrl」を満たす場合だけ、次の処理を実行します。
        if (fromUrl) {
            // 説明: 計算・取得した結果「fromUrl;」を呼び出し元へ返します。
            return fromUrl;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 「イベント発火の起因になった要素」を取る,ユーザーによってアクティブ化されている要素（ボタンなど）を表す,
        // 説明: 現在ページを表す要素に、条件に合う最初のHTML要素を取得して入れます。
        var current = nav.querySelector('.current, .active, .page-nav-current, .page-navi-current');

        // 説明: 条件「current」を満たす場合だけ、次の処理を実行します。
        if (current) {
            // 説明: 文字列から数字だけを取り出した値に「current.textContent.replace(/[^\d]/g, '')」の結果を入れます。
            var n = current.textContent.replace(/[^\d]/g, '');
            // 説明: 条件「n」を満たす場合だけ、次の処理を実行します。
            if (n) {
                // 説明: 計算・取得した結果「parseInt(n, 10);」を呼び出し元へ返します。
                return parseInt(n, 10);
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「1;」を呼び出し元へ返します。
        return 1;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getMaxPage 関数を定義します。引数は「nav, paramName」です。
    function getMaxPage(nav, paramName) {
        // 説明: 最大ページ番号を保存する変数に「1」の結果を入れます。
        var max = 1;
        // 説明: 対象内のリンク要素を配列化したものに、条件に合う複数のHTML要素を取得して入れます。
        var links = Array.prototype.slice.call(nav.querySelectorAll('a[href], .page-nav-link'));

        // 説明: 取得した要素を1つずつ取り出し、「node」として処理します。
        links.forEach(function (node) {
            // 表示ページ名を取得する
            // 説明: リンク先URLから取得したページ番号に「node.getAttribute ? getPageFromHref(node.getAttribute('href'), paramName) : null」の結果を入れます。
            var hrefPage = node.getAttribute ? getPageFromHref(node.getAttribute('href'), paramName) : null;
            // 説明: リンク文字から取得したページ番号に「parseInt((node.textContent || '').replace(/[^\d]/g, ''), 10)」の結果を入れます。
            var textPage = parseInt((node.textContent || '').replace(/[^\d]/g, ''), 10);

            // 説明: 条件「hrefPage && hrefPage > max」を満たす場合だけ、次の処理を実行します。
            if (hrefPage && hrefPage > max) {
                // 説明: max に「hrefPage」を代入します。
                max = hrefPage;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 条件「textPage && textPage > max」を満たす場合だけ、次の処理を実行します。
            if (textPage && textPage > max) {
                // 説明: max に「textPage」を代入します。
                max = textPage;
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 計算・取得した結果「max;」を呼び出し元へ返します。
        return max;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: makePageUrl 関数を定義します。引数は「page, paramName」です。
    function makePageUrl(page, paramName) {
        // 説明: URLを扱いやすくするためのURLオブジェクトに、URL文字列を解析しやすいURLオブジェクトとして入れます。
        var url = new URL(window.location.href);

        // 説明: 条件「page <= 1」を満たす場合だけ、次の処理を実行します。
        if (page <= 1) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            url.searchParams.delete(paramName);
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            url.searchParams.delete('cp');
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            url.searchParams.delete('paged');
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            url.searchParams.delete('page');
        // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
        } else {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            url.searchParams.set(paramName, String(page));
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 数値を文字列に変換
        // 説明: 計算・取得した結果「url.toString();」を呼び出し元へ返します。
        return url.toString();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: findLink 関数を定義します。引数は「nav, kind」です。
    function findLink(nav, kind) {
        // 説明: 対象内のリンク要素を配列化したものに、条件に合う複数のHTML要素を取得して入れます。
        var links = Array.prototype.slice.call(nav.querySelectorAll('a[href]'));

        // 提供されたテスト関数を満たす配列内の最初の要素を返す
        // 説明: 計算・取得した結果「links.find(function (link) {」を呼び出し元へ返します。
        return links.find(function (link) {
            // 説明: リンク文字から空白を除いた判定用文字列に「(link.textContent || '').replace(/\s+/g, '')」の結果を入れます。
            var t = (link.textContent || '').replace(/\s+/g, '');

            // 説明: 条件「kind === 'first'」を満たす場合だけ、次の処理を実行します。
            if (kind === 'first') {
                // 説明: 計算・取得した結果「/^(<<|≪|«|最初)$/.test(t);」を呼び出し元へ返します。
                return /^(<<|≪|«|最初)$/.test(t);
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 条件「kind === 'prev'」を満たす場合だけ、次の処理を実行します。
            if (kind === 'prev') {
                // 説明: 計算・取得した結果「/^(<|‹|前|前へ)$/.test(t);」を呼び出し元へ返します。
                return /^(<|‹|前|前へ)$/.test(t);
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 条件「kind === 'next'」を満たす場合だけ、次の処理を実行します。
            if (kind === 'next') {
                // 説明: 計算・取得した結果「/^(>|›|次|次へ)$/.test(t);」を呼び出し元へ返します。
                return /^(>|›|次|次へ)$/.test(t);
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 条件「kind === 'last'」を満たす場合だけ、次の処理を実行します。
            if (kind === 'last') {
                // 説明: 計算・取得した結果「/^(>>|≫|»|最後)$/.test(t);」を呼び出し元へ返します。
                return /^(>>|≫|»|最後)$/.test(t);
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 条件に合わないため false を返します。
            return false;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        }) || null;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: createButton 関数を定義します。引数は「label, href, disabled, ariaLabel」です。
    function createButton(label, href, disabled, ariaLabel) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var node;

        // 説明: 条件「disabled || !href」を満たす場合だけ、次の処理を実行します。
        if (disabled || !href) {
            // 説明: node に「document.createElement('span')」を代入します。
            node = document.createElement('span');
            // 説明: node.className にCSS用のクラス名を設定します。
            node.className = 'mobile-page-button is-disabled';
        // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
        } else {
            // 説明: node に「document.createElement('a')」を代入します。
            node = document.createElement('a');
            // 説明: node.className にCSS用のクラス名を設定します。
            node.className = 'mobile-page-button';
            // 説明: node.href にリンク先URLを設定します。
            node.href = href;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: node.textContent の表示文字を「label」に変更します。textContentなのでHTMLとして実行されません。
        node.textContent = label;
        // 説明: 対象要素にHTML属性を設定します。
        node.setAttribute('aria-label', ariaLabel);
        // 説明: 計算・取得した結果「node;」を呼び出し元へ返します。
        return node;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: buildMobilePageNavi 関数を定義します。引数は「なし」です。
    function buildMobilePageNavi() {
        // 説明: 取得した要素を1つずつ取り出し、「nav」として処理します。
        document.querySelectorAll('.page-navi').forEach(function (nav) {
            // 説明: 条件「!mediaQuery.matches」を満たす場合だけ、次の処理を実行します。
            if (!mediaQuery.matches) {
                // 説明: 条件「nav.getAttribute('data-mobile-pagenavi-original') === '1'」を満たす場合だけ、次の処理を実行します。
                if (nav.getAttribute('data-mobile-pagenavi-original') === '1') {
                    // 説明: nav.innerHTML の中身をHTMLとして変更します。ユーザー入力を入れる場合はXSSに注意します。
                    nav.innerHTML = nav.getAttribute('data-mobile-pagenavi-html') || nav.innerHTML;
                    // 説明: 対象要素からHTML属性を削除します。
                    nav.removeAttribute('data-mobile-pagenavi-original');
                    // 説明: 対象要素からHTML属性を削除します。
                    nav.removeAttribute('data-mobile-pagenavi-html');
                    // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
                    nav.classList.remove('is-mobile-pagenavi-ready');
                // 説明: 現在の処理ブロックを閉じます。
                }

                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 条件「nav.classList.contains('is-mobile-pagenavi-ready')」を満たす場合だけ、次の処理を実行します。
            if (nav.classList.contains('is-mobile-pagenavi-ready')) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 対象要素にHTML属性を設定します。
            nav.setAttribute('data-mobile-pagenavi-original', '1');
            // 説明: 対象要素にHTML属性を設定します。
            nav.setAttribute('data-mobile-pagenavi-html', nav.innerHTML);

            // 説明: ページ番号に使うURLパラメータ名に「getPageParamName(nav)」の結果を入れます。
            var paramName = getPageParamName(nav);
            // 説明: 現在のページ番号に「getCurrentPage(nav, paramName)」の結果を入れます。
            var currentPage = getCurrentPage(nav, paramName);
            // 説明: 最大ページ番号に「getMaxPage(nav, paramName)」の結果を入れます。
            var maxPage = getMaxPage(nav, paramName);

            // 説明: 条件「maxPage < currentPage」を満たす場合だけ、次の処理を実行します。
            if (maxPage < currentPage) {
                // 説明: maxPage に「currentPage」を代入します。
                maxPage = currentPage;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 最初ページへのリンク要素に「findLink(nav, 'first')」の結果を入れます。
            var firstLink = findLink(nav, 'first');
            // 説明: 前ページへのリンク要素に「findLink(nav, 'prev')」の結果を入れます。
            var prevLink = findLink(nav, 'prev');
            // 説明: 次ページへのリンク要素に「findLink(nav, 'next')」の結果を入れます。
            var nextLink = findLink(nav, 'next');
            // 説明: 最後ページへのリンク要素に「findLink(nav, 'last')」の結果を入れます。
            var lastLink = findLink(nav, 'last');

            // 説明: 最初ページへのURLに「firstLink ? firstLink.href : makePageUrl(1, paramName)」の結果を入れます。
            var firstHref = firstLink ? firstLink.href : makePageUrl(1, paramName);
            // 説明: 前ページへのURLに「prevLink ? prevLink.href : makePageUrl(currentPage - 1, paramName)」の結果を入れます。
            var prevHref = prevLink ? prevLink.href : makePageUrl(currentPage - 1, paramName);
            // 説明: 次ページへのURLに「nextLink ? nextLink.href : makePageUrl(currentPage + 1, paramName)」の結果を入れます。
            var nextHref = nextLink ? nextLink.href : makePageUrl(currentPage + 1, paramName);
            // 説明: 最後ページへのURLに「lastLink ? lastLink.href : makePageUrl(maxPage, paramName)」の結果を入れます。
            var lastHref = lastLink ? lastLink.href : makePageUrl(maxPage, paramName);

            // 説明: 現在ページ数表示用の要素として、新しいHTML要素を作成します。
            var count = document.createElement('div');
            // 説明: count.className にCSS用のクラス名を設定します。
            count.className = 'mobile-page-count';
            // 説明: count.textContent の表示文字を「currentPage + ' / ' + maxPage」に変更します。textContentなのでHTMLとして実行されません。
            count.textContent = currentPage + ' / ' + maxPage;

            // 説明: ページ送りボタンを入れる要素として、新しいHTML要素を作成します。
            var buttons = document.createElement('div');
            // 説明: buttons.className にCSS用のクラス名を設定します。
            buttons.className = 'mobile-page-buttons';

            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            buttons.appendChild(createButton('≪', firstHref, currentPage <= 1, '最初のページへ'));
            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            buttons.appendChild(createButton('‹ 前へ', prevHref, currentPage <= 1, '前のページへ'));
            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            buttons.appendChild(createButton('次へ ›', nextHref, currentPage >= maxPage, '次のページへ'));
            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            buttons.appendChild(createButton('≫', lastHref, currentPage >= maxPage, '最後のページへ'));

            // 説明: nav.innerHTML の中身をHTMLとして変更します。ユーザー入力を入れる場合はXSSに注意します。
            nav.replaceChildren();
            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            nav.appendChild(count);
            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            nav.appendChild(buttons);
            // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
            nav.classList.add('is-mobile-pagenavi-ready');
        // 説明: コールバック関数または処理ブロックを閉じます。
        });
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「document.readyState === 'loading'」を満たす場合だけ、次の処理を実行します。
    if (document.readyState === 'loading') {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        document.addEventListener('DOMContentLoaded', buildMobilePageNavi);
    // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
    } else {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        buildMobilePageNavi();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 指定したイベントが発生した時に実行する処理を登録します。
    window.addEventListener('resize', buildMobilePageNavi);

    // 説明: 条件「mediaQuery.addEventListener」を満たす場合だけ、次の処理を実行します。
    if (mediaQuery.addEventListener) {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        mediaQuery.addEventListener('change', buildMobilePageNavi);
    // 説明: 前の条件に当てはまらず、条件「mediaQuery.addListener」を満たす場合に実行します。
    } else if (mediaQuery.addListener) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        mediaQuery.addListener(buildMobilePageNavi);
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 即時関数の定義を閉じ、その場で実行します。
}());


/* ===================================================
   スマホ＋タブレット：投稿ページ(single.php)専用コメント追尾UI
   セキュリティ方針：
   ・送信処理はWordPress標準の #commentform をそのまま使う
   ・JSは #respond の下固定ドロワー移動、comment_parent の切替、表示制御だけ行う
   ・独自Ajax送信はしない
   ・single.php内の旧返信移動JSと衝突しないよう、スマホ時だけ返信リンクのクラスを付け替える
=================================================== */
// 説明: この処理だけで使う変数を外へ漏らさないため、即時関数を開始します。
(function () {
    // 説明: 厳格モードを有効にして、JavaScriptのミスを検出しやすくします。
    'use strict';

    // 説明: スマホ幅かどうかを判定する条件に、画面幅の判定条件を入れます。
    var mediaQuery = window.matchMedia('(max-width: 599px)');

    // 説明: スマホ用コメント入力ドロワー本体を、まだ何も入っていない状態で用意します。
    var drawer = null;
    // 説明: 閉じている時に表示する下部コメントバーを、まだ何も入っていない状態で用意します。
    var closedBar = null;
    // 説明: コメントフォームを入れるための囲み要素を、まだ何も入っていない状態で用意します。
    var formWrap = null;
    // 説明: WordPress標準のコメントフォーム外枠を、まだ何も入っていない状態で用意します。
    var respond = null;
    // 説明: WordPress標準のコメントフォームを、まだ何も入っていない状態で用意します。
    var commentForm = null;
    // 説明: コメント本文入力欄を、まだ何も入っていない状態で用意します。
    var commentTextarea = null;
    // 説明: 返信先コメントIDを入れるhidden inputを、まだ何も入っていない状態で用意します。
    var commentParentInput = null;
    // 説明: コメント送信ボタンを、まだ何も入っていない状態で用意します。
    var submitButton = null;

    // 説明: 移動前の親要素を保存する変数を、まだ何も入っていない状態で用意します。
    var originalParent = null;
    // 説明: 移動前の位置を覚えるための目印コメントを、まだ何も入っていない状態で用意します。
    var originalMarker = null;
    // 説明: 返信クリック処理を登録済みか判定するフラグに「false」の結果を入れます。
    var replyClickReady = false;
    // 説明: タッチ開始時のY座標に「0」の結果を入れます。
    var touchStartY = 0;
    // 説明: スマホ用に付け替えた返信リンク一覧として、複数の値を入れる配列を作ります。
    var convertedReplyLinks = [];

    // 説明: isSinglePostPage 関数を定義します。引数は「なし」です。
    function isSinglePostPage() {
        // 説明: 条件「!document.body」を満たす場合だけ、次の処理を実行します。
        if (!document.body) {
            // 説明: 条件に合わないため false を返します。
            return false;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「document.body.classList.contains('single-post') ||」を呼び出し元へ返します。
        return document.body.classList.contains('single-post') ||
               // 説明: 対象要素に指定クラスが付いているか確認します。
               document.body.classList.contains('post-template') ||
               // 説明: 対象要素に指定クラスが付いているか確認します。
               document.body.classList.contains('single');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: hasCommentForm 関数を定義します。引数は「なし」です。
    function hasCommentForm() {
        // 説明: 計算・取得した結果「!!(」を呼び出し元へ返します。
        return !!(
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            isSinglePostPage() &&
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            document.getElementById('respond') &&
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            document.getElementById('commentform') &&
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            document.getElementById('comment')
        // 説明: 直前から続く複数行の処理を閉じます。
        );
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getReplyCommentId 関数を定義します。引数は「link」です。
    function getReplyCommentId(link) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var id;

        // 説明: 条件「!link」を満たす場合だけ、次の処理を実行します。
        if (!link) {
            // 説明: 計算・取得した結果「0;」を呼び出し元へ返します。
            return 0;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: id に「link.getAttribute('data-rootid') ||」を代入します。
        id = link.getAttribute('data-rootid') ||
             // 説明: 対象要素からHTML属性の値を取得します。
             link.getAttribute('data-commentid') ||
             // 説明: 対象要素からHTML属性の値を取得します。
             link.getAttribute('data-comment-id') ||
             // 説明: 対象要素からHTML属性の値を取得します。
             link.getAttribute('data-id');

        // 説明: 条件「id && /^\d+$/.test(id)」を満たす場合だけ、次の処理を実行します。
        if (id && /^\d+$/.test(id)) {
            // 説明: 計算・取得した結果「parseInt(id, 10);」を呼び出し元へ返します。
            return parseInt(id, 10);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: URL解析などでエラーが出ても止まらないように試行処理を開始します。
        try {
            // 説明: リンク先URL文字列に「link.getAttribute('href') || ''」の結果を入れます。
            var href = link.getAttribute('href') || '';
            // 説明: URLを扱いやすくするためのURLオブジェクトに、URL文字列を解析しやすいURLオブジェクトとして入れます。
            var url = new URL(href, window.location.href);
            // 説明: URL内のreplytocomパラメータに「url.searchParams.get('replytocom')」の結果を入れます。
            var replyTo = url.searchParams.get('replytocom');

            // 説明: 条件「replyTo && /^\d+$/.test(replyTo)」を満たす場合だけ、次の処理を実行します。
            if (replyTo && /^\d+$/.test(replyTo)) {
                // 説明: 計算・取得した結果「parseInt(replyTo, 10);」を呼び出し元へ返します。
                return parseInt(replyTo, 10);
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: try内でエラーが出た場合でも、何もしないで処理を続けます。
        } catch (e) {}

        // 説明: onclick属性の文字列に「link.getAttribute('onclick') || ''」の結果を入れます。
        var onclick = link.getAttribute('onclick') || '';
        // 説明: 正規表現に一致した結果に「onclick.match(/moveForm\([^,]+,\s*['"]?(\d+)['"]?/)」の結果を入れます。
        var match = onclick.match(/moveForm\([^,]+,\s*['"]?(\d+)['"]?/);

        // 説明: 条件「match && match[1] && /^\d+$/.test(match[1])」を満たす場合だけ、次の処理を実行します。
        if (match && match[1] && /^\d+$/.test(match[1])) {
            // 説明: 計算・取得した結果「parseInt(match[1], 10);」を呼び出し元へ返します。
            return parseInt(match[1], 10);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「0;」を呼び出し元へ返します。
        return 0;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: ensureCommentParentInput 関数を定義します。引数は「なし」です。
    function ensureCommentParentInput() {
        // 説明: 条件「!commentForm」を満たす場合だけ、次の処理を実行します。
        if (!commentForm) {
            // 説明: 計算・取得した結果「null;」を呼び出し元へ返します。
            return null;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: commentParentInput に「commentForm.querySelector('input[name="comment_parent"]')」を代入します。
        commentParentInput = commentForm.querySelector('input[name="comment_parent"]');

        // 説明: 条件「!commentParentInput」を満たす場合だけ、次の処理を実行します。
        if (!commentParentInput) {
            // 説明: commentParentInput に「document.createElement('input')」を代入します。
            commentParentInput = document.createElement('input');
            // 説明: commentParentInput.type にフォーム部品の種類を設定します。
            commentParentInput.type = 'hidden';
            // 説明: commentParentInput.name にフォーム送信用のname属性を設定します。
            commentParentInput.name = 'comment_parent';
            // 説明: commentParentInput.id にID属性を設定します。
            commentParentInput.id = 'comment_parent';
            // 説明: commentParentInput.value にフォームで送る値を設定します。
            commentParentInput.value = '0';
            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            commentForm.appendChild(commentParentInput);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「commentParentInput;」を呼び出し元へ返します。
        return commentParentInput;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getReplyTargetText 関数を定義します。引数は「link」です。
    function getReplyTargetText(link) {
        // 説明: 返信リンクに近いコメント本文の親要素に「link ? link.closest('article, .comment-body, .reply-content, li') : null」の結果を入れます。
        var article = link ? link.closest('article, .comment-body, .reply-content, li') : null;
        // 説明: コメント投稿者名が入っている要素に、条件に合う最初のHTML要素を取得して入れます。
        var author = article ? article.querySelector('.comment-author, .reply-author, cite, p:first-child') : null;
        // 説明: 画面表示用の文字列に「author ? author.textContent.replace(/\s+/g, ' ').trim() : ''」の結果を入れます。
        var text = author ? author.textContent.replace(/\s+/g, ' ').trim() : '';

        // 説明: 条件「!text」を満たす場合だけ、次の処理を実行します。
        if (!text) {
            // 説明: 計算・取得した結果「'このコメント';」を呼び出し元へ返します。
            return 'このコメント';
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「text.length > 24 ? text.slice(0, 24) + '…' : text;」を呼び出し元へ返します。
        return text.length > 24 ? text.slice(0, 24) + '…' : text;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: updateSubmitState 関数を定義します。引数は「なし」です。
    function updateSubmitState() {
        // 説明: 条件「!commentTextarea || !submitButton」を満たす場合だけ、次の処理を実行します。
        if (!commentTextarea || !submitButton) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: submitButton.disabled の有効・無効状態を切り替えます。
        submitButton.disabled = commentTextarea.value.trim() === '';
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: openDrawer 関数を定義します。引数は「expand」です。
    function openDrawer(expand) {
        // 説明: 条件「!drawer」を満たす場合だけ、次の処理を実行します。
        if (!drawer) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        drawer.classList.add('is-open');
        // 説明: 対象要素にHTML属性を設定します。
        drawer.setAttribute('aria-hidden', 'false');
        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        document.body.classList.add('single-comment-drawer-open');

        /*
         * 返信ボタンから開く時も、まずは半分表示にする。
         * 以前に全画面表示した is-expanded が残っていると、
         * openDrawer(false) でも全画面のままになるため、ここで必ず解除する。
         */
        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        drawer.classList.remove('is-expanded');
        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.body.classList.remove('single-comment-drawer-expanded');

        // 説明: 条件「expand」を満たす場合だけ、次の処理を実行します。
        if (expand) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            expandDrawer();
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「closedBar」を満たす場合だけ、次の処理を実行します。
        if (closedBar) {
            // 説明: 対象要素にHTML属性を設定します。
            closedBar.setAttribute('aria-expanded', 'true');
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        updateSubmitState();

        // 説明: 指定時間後に処理を実行します。
        window.setTimeout(function () {
            // 説明: 条件「commentTextarea」を満たす場合だけ、次の処理を実行します。
            if (commentTextarea) {
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                commentTextarea.focus({ preventScroll: true });
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        }, 60);
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: closeDrawer 関数を定義します。引数は「なし」です。
    function closeDrawer() {
        // 説明: 条件「!drawer」を満たす場合だけ、次の処理を実行します。
        if (!drawer) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        drawer.classList.remove('is-open');
        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        drawer.classList.remove('is-expanded');
        // 説明: 対象要素にHTML属性を設定します。
        drawer.setAttribute('aria-hidden', 'true');

        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.body.classList.remove('single-comment-drawer-open');
        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.body.classList.remove('single-comment-drawer-expanded');

        // 説明: 条件「closedBar」を満たす場合だけ、次の処理を実行します。
        if (closedBar) {
            // 説明: 対象要素にHTML属性を設定します。
            closedBar.setAttribute('aria-expanded', 'false');
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: expandDrawer 関数を定義します。引数は「なし」です。
    function expandDrawer() {
        // 説明: 条件「!drawer」を満たす場合だけ、次の処理を実行します。
        if (!drawer) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        drawer.classList.add('is-expanded');
        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        document.body.classList.add('single-comment-drawer-expanded');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: setNormalCommentMode 関数を定義します。引数は「なし」です。
    function setNormalCommentMode() {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        ensureCommentParentInput();

        // 説明: 条件「commentParentInput」を満たす場合だけ、次の処理を実行します。
        if (commentParentInput) {
            // 説明: commentParentInput.value にフォームで送る値を設定します。
            commentParentInput.value = '0';
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「drawer」を満たす場合だけ、次の処理を実行します。
        if (drawer) {
            // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
            drawer.classList.remove('is-replying');
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: コメントドロワー上部の案内文要素に、条件に合う最初のHTML要素を取得して入れます。
        var rule = drawer ? drawer.querySelector('.single-comment-rule') : null;
        // 説明: 条件「rule」を満たす場合だけ、次の処理を実行します。
        if (rule) {
            /* 通常コメント時の案内文は不要なので表示しない。 */
            // 説明: rule.innerHTML の中身をHTMLとして変更します。ユーザー入力を入れる場合はXSSに注意します。
            rule.textContent = '';
            // 説明: rule.style.display に「'none'」を代入します。
            rule.style.display = 'none';
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「closedBar」を満たす場合だけ、次の処理を実行します。
        if (closedBar) {
            // 説明: closedBar.textContent の表示文字を「'コメントする…'」に変更します。textContentなのでHTMLとして実行されません。
            closedBar.textContent = 'コメントする…';
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「commentForm」を満たす場合だけ、次の処理を実行します。
        if (commentForm) {
            // 説明: 対象要素からHTML属性を削除します。
            commentForm.removeAttribute('data-mobile-reply-to');
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: setReplyMode 関数を定義します。引数は「link」です。
    function setReplyMode(link) {
        // 説明: 返信先コメントIDなどの数値IDに「getReplyCommentId(link)」の結果を入れます。
        var id = getReplyCommentId(link);

        // 説明: 条件「!id」を満たす場合だけ、次の処理を実行します。
        if (!id) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        ensureCommentParentInput();

        // 説明: 条件「commentParentInput」を満たす場合だけ、次の処理を実行します。
        if (commentParentInput) {
            // 説明: commentParentInput.value にフォームで送る値を設定します。
            commentParentInput.value = String(id);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「drawer」を満たす場合だけ、次の処理を実行します。
        if (drawer) {
            // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
            drawer.classList.add('is-replying');
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: コメントドロワー上部の案内文要素に、条件に合う最初のHTML要素を取得して入れます。
        var rule = drawer ? drawer.querySelector('.single-comment-rule') : null;
        // 説明: 条件「rule」を満たす場合だけ、次の処理を実行します。
        if (rule) {
            // 説明: rule.style.display に「'block'」を代入します。
            rule.style.display = 'block';
            /* innerHTMLにユーザー由来の文字列を入れない。DOM XSS対策。 */
            // 説明: rule.textContent の表示文字を「''」に変更します。textContentなのでHTMLとして実行されません。
            rule.textContent = '';

            // 説明: 太字表示に使うstrong要素として、新しいHTML要素を作成します。
            var strong = document.createElement('strong');
            // 説明: strong.textContent の表示文字を「'返信モード'」に変更します。textContentなのでHTMLとして実行されません。
            strong.textContent = '返信モード';

            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            rule.appendChild(strong);
            // 説明: 指定した要素を、親要素の最後の子要素として追加します。
            rule.appendChild(document.createTextNode('：' + getReplyTargetText(link) + ' へ返信します。'));
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「closedBar」を満たす場合だけ、次の処理を実行します。
        if (closedBar) {
            // 説明: closedBar.textContent の表示文字を「'返信を書く…'」に変更します。textContentなのでHTMLとして実行されません。
            closedBar.textContent = '返信を書く…';
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「commentForm」を満たす場合だけ、次の処理を実行します。
        if (commentForm) {
            // 説明: 対象要素にHTML属性を設定します。
            commentForm.setAttribute('data-mobile-reply-to', String(id));
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        openDrawer(false);
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: prepareReplyLinksForDrawer 関数を定義します。引数は「なし」です。
    function prepareReplyLinksForDrawer() {
        // 説明: convertedReplyLinks に「[]」を代入します。
        convertedReplyLinks = [];

        // 説明: 取得した要素を1つずつ取り出し、「link」として処理します。
        document.querySelectorAll('.custom-flat-reply-link').forEach(function (link) {
            // 説明: 条件「link.classList.contains('mobile-drawer-reply-link')」を満たす場合だけ、次の処理を実行します。
            if (link.classList.contains('mobile-drawer-reply-link')) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
            link.classList.add('mobile-drawer-reply-link');
            // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
            link.classList.remove('custom-flat-reply-link');
            // 説明: 配列の末尾に値を追加します。
            convertedReplyLinks.push(link);
        // 説明: コールバック関数または処理ブロックを閉じます。
        });
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: restoreReplyLinksForPc 関数を定義します。引数は「なし」です。
    function restoreReplyLinksForPc() {
        // 説明: 取得した要素を1つずつ取り出し、「link」として処理します。
        convertedReplyLinks.forEach(function (link) {
            // 説明: 条件「!link || !link.classList」を満たす場合だけ、次の処理を実行します。
            if (!link || !link.classList) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
            link.classList.add('custom-flat-reply-link');
            // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
            link.classList.remove('mobile-drawer-reply-link');
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: convertedReplyLinks に「[]」を代入します。
        convertedReplyLinks = [];
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: buildDrawer 関数を定義します。引数は「なし」です。
    function buildDrawer() {
        // 説明: respond に「document.getElementById('respond')」を代入します。
        respond = document.getElementById('respond');
        // 説明: commentForm に「document.getElementById('commentform')」を代入します。
        commentForm = document.getElementById('commentform');
        // 説明: commentTextarea に「document.getElementById('comment')」を代入します。
        commentTextarea = document.getElementById('comment');

        // 説明: 条件「!respond || !commentForm || !commentTextarea || drawer」を満たす場合だけ、次の処理を実行します。
        if (!respond || !commentForm || !commentTextarea || drawer) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: submitButton に「commentForm.querySelector('#submit, input[type="submit"], button[type="submit"]')」を代入します。
        submitButton = commentForm.querySelector('#submit, input[type="submit"], button[type="submit"]');
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        ensureCommentParentInput();

        // 説明: originalParent に「respond.parentNode」を代入します。
        originalParent = respond.parentNode;
        // 説明: originalMarker に「document.createComment('original respond position for mobile comment drawer')」を代入します。
        originalMarker = document.createComment('original respond position for mobile comment drawer');
        // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
        originalParent.insertBefore(originalMarker, respond);

        // 説明: drawer に「document.createElement('div')」を代入します。
        drawer = document.createElement('div');
        // 説明: drawer.className にCSS用のクラス名を設定します。
        drawer.className = 'single-comment-drawer';
        // 説明: 対象要素にHTML属性を設定します。
        drawer.setAttribute('aria-hidden', 'true');

        // 説明: ドロワー上部のつまみボタンとして、新しいHTML要素を作成します。
        var grip = document.createElement('button');
        // 説明: grip.type にフォーム部品の種類を設定します。
        grip.type = 'button';
        // 説明: grip.className にCSS用のクラス名を設定します。
        grip.className = 'single-comment-grip';
        // 説明: 対象要素にHTML属性を設定します。
        grip.setAttribute('aria-label', 'コメント欄を広げる');

        // 説明: closedBar に「document.createElement('button')」を代入します。
        closedBar = document.createElement('button');
        // 説明: closedBar.type にフォーム部品の種類を設定します。
        closedBar.type = 'button';
        // 説明: closedBar.className にCSS用のクラス名を設定します。
        closedBar.className = 'single-comment-closedbar';
        // 説明: closedBar.textContent の表示文字を「'コメントする…'」に変更します。textContentなのでHTMLとして実行されません。
        closedBar.textContent = 'コメントする…';
        // 説明: 対象要素にHTML属性を設定します。
        closedBar.setAttribute('aria-expanded', 'false');

        // 説明: ドロワー内のヘッダー部分として、新しいHTML要素を作成します。
        var header = document.createElement('div');
        // 説明: header.className にCSS用のクラス名を設定します。
        header.className = 'single-comment-header';

        // 説明: コメントドロワー上部の案内文要素として、新しいHTML要素を作成します。
        var rule = document.createElement('p');
        // 説明: rule.className にCSS用のクラス名を設定します。
        rule.className = 'single-comment-rule';
        // 説明: rule.style.display に「'none'」を代入します。
        rule.style.display = 'none';
        /* 通常コメント時の案内文は不要なので表示しない。 */
            // 説明: rule.innerHTML の中身をHTMLとして変更します。ユーザー入力を入れる場合はXSSに注意します。
            rule.textContent = '';
            // 説明: rule.style.display に「'none'」を代入します。
            rule.style.display = 'none';

        // 説明: 返信キャンセルボタンとして、新しいHTML要素を作成します。
        var cancel = document.createElement('button');
        // 説明: cancel.type にフォーム部品の種類を設定します。
        cancel.type = 'button';
        // 説明: cancel.className にCSS用のクラス名を設定します。
        cancel.className = 'single-comment-cancel';
        // 説明: cancel.textContent の表示文字を「'返信キャンセル'」に変更します。textContentなのでHTMLとして実行されません。
        cancel.textContent = '返信キャンセル';

        // 説明: ドロワーを閉じるボタンとして、新しいHTML要素を作成します。
        var close = document.createElement('button');
        // 説明: close.type にフォーム部品の種類を設定します。
        close.type = 'button';
        // 説明: close.className にCSS用のクラス名を設定します。
        close.className = 'single-comment-close';
        // 説明: close.textContent の表示文字を「'×'」に変更します。textContentなのでHTMLとして実行されません。
        close.textContent = '×';
        // 説明: 対象要素にHTML属性を設定します。
        close.setAttribute('aria-label', 'コメント欄を閉じる');

        // 説明: 新しいHTML要素を安全に作成します。
        formWrap = document.createElement('div');
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        formWrap.className = 'single-comment-formwrap';

        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        header.appendChild(rule);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        header.appendChild(cancel);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        header.appendChild(close);

        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        drawer.appendChild(grip);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        drawer.appendChild(closedBar);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        drawer.appendChild(header);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        drawer.appendChild(formWrap);

        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        formWrap.appendChild(respond);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        document.body.appendChild(drawer);

        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        prepareReplyLinksForDrawer();

        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        document.body.classList.add('single-comment-drawer-ready');

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        closedBar.addEventListener('click', function () {
            /*
             * コメントバーをもう一度押したら閉じる。
             * 返信アイコンでは閉じない。通常コメント/返信コメントのバーだけで開閉する。
             */
            // 説明: 条件「drawer.classList.contains('is-open')」を満たす場合だけ、次の処理を実行します。
            if (drawer.classList.contains('is-open')) {
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                closeDrawer();
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            /*
             * 返信モードで閉じている場合は、そのまま返信モードで再表示する。
             * 通常モードの時だけ comment_parent を 0 に戻す。
             */
            // 説明: 条件「!drawer.classList.contains('is-replying')」を満たす場合だけ、次の処理を実行します。
            if (!drawer.classList.contains('is-replying')) {
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                setNormalCommentMode();
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            openDrawer(false);
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        grip.addEventListener('click', function () {
            /*
             * 開いている時は、上のグレーのバーをもう一度押すと閉じる。
             * 全画面化は従来通り、上方向のスワイプで行う。
             */
            // 説明: 条件「drawer.classList.contains('is-open')」を満たす場合だけ、次の処理を実行します。
            if (drawer.classList.contains('is-open')) {
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                closeDrawer();
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            openDrawer(false);
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        cancel.addEventListener('click', function () {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            setNormalCommentMode();
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            openDrawer(false);
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        close.addEventListener('click', function () {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            closeDrawer();
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        commentTextarea.addEventListener('focus', function () {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            openDrawer(false);
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        commentTextarea.addEventListener('input', updateSubmitState);
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        updateSubmitState();

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        drawer.addEventListener('touchstart', function (event) {
            // 説明: 条件「!event.touches || !event.touches.length」を満たす場合だけ、次の処理を実行します。
            if (!event.touches || !event.touches.length) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: touchStartY に「event.touches[0].clientY」を代入します。
            touchStartY = event.touches[0].clientY;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        }, { passive: true });

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        drawer.addEventListener('touchmove', function (event) {
            // 説明: 条件「!event.touches || !event.touches.length」を満たす場合だけ、次の処理を実行します。
            if (!event.touches || !event.touches.length) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: タッチ移動中のY座標に「event.touches[0].clientY」の結果を入れます。
            var currentY = event.touches[0].clientY;

            // 説明: 条件「touchStartY - currentY > 35 && drawer.classList.contains('is-open')」を満たす場合だけ、次の処理を実行します。
            if (touchStartY - currentY > 35 && drawer.classList.contains('is-open')) {
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                expandDrawer();
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        }, { passive: true });

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        drawer.addEventListener('wheel', function (event) {
            // 説明: 条件「event.deltaY < -10 && drawer.classList.contains('is-open')」を満たす場合だけ、次の処理を実行します。
            if (event.deltaY < -10 && drawer.classList.contains('is-open')) {
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                expandDrawer();
            // 説明: 現在の処理ブロックを閉じます。
            }
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        }, { passive: true });
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: restoreDrawer 関数を定義します。引数は「なし」です。
    function restoreDrawer() {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        restoreReplyLinksForPc();

        // 説明: 条件「respond && originalParent && originalMarker && originalMarker.parentNode === originalParent」を満たす場合だけ、次の処理を実行します。
        if (respond && originalParent && originalMarker && originalMarker.parentNode === originalParent) {
            // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
            originalParent.insertBefore(respond, originalMarker.nextSibling);
            // 説明: 対象要素をHTML上から削除します。
            originalMarker.remove();
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「drawer && drawer.parentNode」を満たす場合だけ、次の処理を実行します。
        if (drawer && drawer.parentNode) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            drawer.parentNode.removeChild(drawer);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.body.classList.remove('single-comment-drawer-ready');
        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.body.classList.remove('single-comment-drawer-open');
        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.body.classList.remove('single-comment-drawer-expanded');

        // 説明: drawer に「null」を代入します。
        drawer = null;
        // 説明: closedBar に「null」を代入します。
        closedBar = null;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        formWrap = null;
        // 説明: respond に「null」を代入します。
        respond = null;
        // 説明: commentForm に「null」を代入します。
        commentForm = null;
        // 説明: commentTextarea に「null」を代入します。
        commentTextarea = null;
        // 説明: commentParentInput に「null」を代入します。
        commentParentInput = null;
        // 説明: submitButton に「null」を代入します。
        submitButton = null;
        // 説明: originalParent に「null」を代入します。
        originalParent = null;
        // 説明: originalMarker に「null」を代入します。
        originalMarker = null;
        // 説明: touchStartY に「0」を代入します。
        touchStartY = 0;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: bindReplyClickOnce 関数を定義します。引数は「なし」です。
    function bindReplyClickOnce() {
        // 説明: 条件「replyClickReady」を満たす場合だけ、次の処理を実行します。
        if (replyClickReady) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: replyClickReady に「true」を代入します。
        replyClickReady = true;

        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        document.addEventListener('click', function (event) {
            // 説明: 条件「!mediaQuery.matches || !drawer」を満たす場合だけ、次の処理を実行します。
            if (!mediaQuery.matches || !drawer) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: リンク要素に「event.target.closest ? event.target.closest('.mobile-drawer-reply-link, .comment-reply-link') : null」の結果を入れます。
            var link = event.target.closest ? event.target.closest('.mobile-drawer-reply-link, .comment-reply-link') : null;

            // 説明: 条件「!link || link.id === 'cancel-comment-reply-link'」を満たす場合だけ、次の処理を実行します。
            if (!link || link.id === 'cancel-comment-reply-link') {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 返信先コメントIDなどの数値IDに「getReplyCommentId(link)」の結果を入れます。
            var id = getReplyCommentId(link);

            // 説明: 条件「!id」を満たす場合だけ、次の処理を実行します。
            if (!id) {
                // 説明: これ以上処理せず、この関数を終了します。
                return;
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: クリックやキー入力などのブラウザ標準動作を止めます。
            event.preventDefault();
            // 説明: 他のクリック処理が続けて実行されるのを止めます。
            event.stopImmediatePropagation();
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            setReplyMode(link);
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        }, true);
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: applyMobileCommentDrawer 関数を定義します。引数は「なし」です。
    function applyMobileCommentDrawer() {
        // 説明: 条件「mediaQuery.matches && hasCommentForm()」を満たす場合だけ、次の処理を実行します。
        if (mediaQuery.matches && hasCommentForm()) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            buildDrawer();
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            bindReplyClickOnce();
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        restoreDrawer();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「document.readyState === 'loading'」を満たす場合だけ、次の処理を実行します。
    if (document.readyState === 'loading') {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        document.addEventListener('DOMContentLoaded', applyMobileCommentDrawer);
    // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
    } else {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        applyMobileCommentDrawer();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 指定したイベントが発生した時に実行する処理を登録します。
    window.addEventListener('resize', applyMobileCommentDrawer);

    // 説明: 条件「mediaQuery.addEventListener」を満たす場合だけ、次の処理を実行します。
    if (mediaQuery.addEventListener) {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        mediaQuery.addEventListener('change', applyMobileCommentDrawer);
    // 説明: 前の条件に当てはまらず、条件「mediaQuery.addListener」を満たす場合に実行します。
    } else if (mediaQuery.addListener) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        mediaQuery.addListener(applyMobileCommentDrawer);
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 即時関数の定義を閉じ、その場で実行します。
}());

/* ===================================================
   スマホ＋タブレット用：コメントいいね・悪いね処理について
   カウント処理はPC側で正常に動いている既存JSに任せる。
   ここにスマホ専用Ajaxを置くと、PC側処理と二重実行になり、
   01表示・増えない・戻る等のカウントバグが起きるため削除。
   スマホ側の見た目は style.css のCSSだけで維持する。
=================================================== */

/* ===================================================
   スマホ＋タブレット：フッターリンクをヘッダー下の横スクロールメニューに変換
   ・フッターそのものは移動しない
   ・footer-menu / footer-sns のリンクだけをコピーして使う
   ・PCでは追加メニューを削除して元の表示に戻す
=================================================== */
// 説明: この処理だけで使う変数を外へ漏らさないため、即時関数を開始します。
(function () {
    // 説明: 厳格モードを有効にして、JavaScriptのミスを検出しやすくします。
    'use strict';

    // 説明: スマホ幅かどうかを判定する条件に、画面幅の判定条件を入れます。
    var mediaQuery = window.matchMedia('(max-width: 599px)');
    // 説明: スマホ用横スクロールメニューのクラス名に「'mobile-header-footer-nav'」の結果を入れます。
    var navClass = 'mobile-header-footer-nav';

    // 説明: getHeaderTarget 関数を定義します。引数は「なし」です。
    function getHeaderTarget() {
        // 説明: 計算・取得した結果「document.querySelector('ul.header-box') ||」を呼び出し元へ返します。
        return document.querySelector('ul.header-box') ||
            // 説明: 指定したCSSセレクタに一致する最初の要素を取得します。
            document.querySelector('#header-box') ||
            // 説明: 指定したCSSセレクタに一致する最初の要素を取得します。
            document.querySelector('header');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getFooter 関数を定義します。引数は「なし」です。
    function getFooter() {
        // 説明: 計算・取得した結果「document.querySelector('.footer');」を呼び出し元へ返します。
        return document.querySelector('.footer');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: createNavLink 関数を定義します。引数は「text, href, className」です。
    function createNavLink(text, href, className) {
        // 説明: リンク要素として、新しいHTML要素を作成します。
        var link = document.createElement('a');

        // 説明: link.href にリンク先URLを設定します。
        link.href = href;
        // 説明: link.textContent の表示文字を「text」に変更します。textContentなのでHTMLとして実行されません。
        link.textContent = text;
        // 説明: link.className にCSS用のクラス名を設定します。
        link.className = 'mobile-header-footer-nav-link';

        // 説明: 条件「className」を満たす場合だけ、次の処理を実行します。
        if (className) {
            // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
            link.classList.add(className);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「link;」を呼び出し元へ返します。
        return link;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getCleanText 関数を定義します。引数は「link」です。
    function getCleanText(link) {
        // 説明: 計算・取得した結果「(link.textContent || '')」を呼び出し元へ返します。
        return (link.textContent || '')
            // 説明: 文字列の一部を置換します。
            .replace(/\s+/g, ' ')
            // 説明: 文字列の前後の余計な空白を削除します。
            .trim();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: addLinkOnce 関数を定義します。引数は「nav, used, text, href, className」です。
    function addLinkOnce(nav, used, text, href, className) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var key;

        // 説明: 条件「!text || !href」を満たす場合だけ、次の処理を実行します。
        if (!text || !href) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: key に「href + '|' + text」を代入します。
        key = href + '|' + text;

        // 説明: 条件「used[key]」を満たす場合だけ、次の処理を実行します。
        if (used[key]) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        used[key] = true;
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        nav.appendChild(createNavLink(text, href, className));
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: buildMobileHeaderFooterNav 関数を定義します。引数は「なし」です。
    function buildMobileHeaderFooterNav() {
        // 説明: スマホ用メニューを挿入するヘッダー側の基準要素に「getHeaderTarget()」の結果を入れます。
        var headerTarget = getHeaderTarget();
        // 説明: フッター要素に「getFooter()」の結果を入れます。
        var footer = getFooter();
        // 説明: すでに作成済みのスマホ用メニューに、条件に合う最初のHTML要素を取得して入れます。
        var oldNav = document.querySelector('.' + navClass);
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var nav;
        // 説明: 重複追加を防ぐための記録用オブジェクトとして、キーと値を保存するオブジェクトを作ります。
        var used = {};

        // 説明: 条件「!mediaQuery.matches」を満たす場合だけ、次の処理を実行します。
        if (!mediaQuery.matches) {
            // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
            document.documentElement.classList.remove('is-mobile-header-footer-nav');

            // 説明: 条件「oldNav」を満たす場合だけ、次の処理を実行します。
            if (oldNav) {
                // 説明: 対象要素をHTML上から削除します。
                oldNav.remove();
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「!headerTarget || !headerTarget.parentNode || !footer」を満たす場合だけ、次の処理を実行します。
        if (!headerTarget || !headerTarget.parentNode || !footer) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「oldNav」を満たす場合だけ、次の処理を実行します。
        if (oldNav) {
            // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
            document.documentElement.classList.add('is-mobile-header-footer-nav');
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: nav に「document.createElement('nav')」を代入します。
        nav = document.createElement('nav');
        // 説明: nav.className にCSS用のクラス名を設定します。
        nav.className = navClass;
        // 説明: 対象要素にHTML属性を設定します。
        nav.setAttribute('aria-label', 'サイトメニュー');

        /* 先頭にTOPを追加 */
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        addLinkOnce(nav, used, 'TOP', window.location.origin + '/', 'is-top');

        /* フッターメニューのリンクを先に追加 */
        // 説明: 取得した要素を1つずつ取り出し、「link」として処理します。
        footer.querySelectorAll('.footer-menu a[href]').forEach(function (link) {
            // 説明: 画面表示用の文字列に「getCleanText(link)」の結果を入れます。
            var text = getCleanText(link);
            // 説明: リンク先URL文字列に「link.href」の結果を入れます。
            var href = link.href;

            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            addLinkOnce(nav, used, text, href, '');
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        /* X / RSS を後ろに追加 */
        // 説明: 取得した要素を1つずつ取り出し、「link」として処理します。
        footer.querySelectorAll('.footer-sns a[href]').forEach(function (link) {
            // 説明: 画面表示用の文字列に「getCleanText(link)」の結果を入れます。
            var text = getCleanText(link);
            // 説明: リンク先URL文字列に「link.href」の結果を入れます。
            var href = link.href;
            // 説明: 追加するCSSクラス名に「''」の結果を入れます。
            var className = '';

            // 説明: 条件「link.classList.contains('footer-x')」を満たす場合だけ、次の処理を実行します。
            if (link.classList.contains('footer-x')) {
                // 説明: className に「'is-x'」を代入します。
                className = 'is-x';
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: 条件「link.classList.contains('footer-rss')」を満たす場合だけ、次の処理を実行します。
            if (link.classList.contains('footer-rss')) {
                // 説明: className に「'is-rss'」を代入します。
                className = 'is-rss';
            // 説明: 現在の処理ブロックを閉じます。
            }

            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            addLinkOnce(nav, used, text, href, className);
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        /* 今見ているページと同じURLなら下線を付ける */
        // 説明: 取得した要素を1つずつ取り出し、「link」として処理します。
        nav.querySelectorAll('a[href]').forEach(function (link) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            var linkUrl;
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            var currentUrl;

            // 説明: URL解析などでエラーが出ても止まらないように試行処理を開始します。
            try {
                // 説明: linkUrl に「new URL(link.href)」を代入します。
                linkUrl = new URL(link.href);
                // 説明: currentUrl に「new URL(window.location.href)」を代入します。
                currentUrl = new URL(window.location.href);

                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                if (
                    // 説明: linkUrl.origin に「== currentUrl.origin &&」を代入します。
                    linkUrl.origin === currentUrl.origin &&
                    // 説明: 文字列の一部を置換します。
                    linkUrl.pathname.replace(/\/$/, '') === currentUrl.pathname.replace(/\/$/, '')
                // 説明: 直前から続く複数行の処理を閉じます。
                ) {
                    // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
                    link.classList.add('is-current');
                // 説明: 現在の処理ブロックを閉じます。
                }
            // 説明: try内でエラーが出た場合でも、何もしないで処理を続けます。
            } catch (e) {}
        // 説明: コールバック関数または処理ブロックを閉じます。
        });

        // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
        headerTarget.parentNode.insertBefore(nav, headerTarget.nextSibling);
        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        document.documentElement.classList.add('is-mobile-header-footer-nav');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「document.readyState === 'loading'」を満たす場合だけ、次の処理を実行します。
    if (document.readyState === 'loading') {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        document.addEventListener('DOMContentLoaded', buildMobileHeaderFooterNav);
    // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
    } else {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        buildMobileHeaderFooterNav();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 指定したイベントが発生した時に実行する処理を登録します。
    window.addEventListener('resize', buildMobileHeaderFooterNav);

    // 説明: 条件「mediaQuery.addEventListener」を満たす場合だけ、次の処理を実行します。
    if (mediaQuery.addEventListener) {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        mediaQuery.addEventListener('change', buildMobileHeaderFooterNav);
    // 説明: 前の条件に当てはまらず、条件「mediaQuery.addListener」を満たす場合に実行します。
    } else if (mediaQuery.addListener) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        mediaQuery.addListener(buildMobileHeaderFooterNav);
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 即時関数の定義を閉じ、その場で実行します。
}());

/* ===================================================
   スマホ＋タブレット：コメントがない時だけ案内文を追加
   ・PCでは追加しない
   ・コメントがある記事では追加しない
   ・single.phpは変更しない
=================================================== */
// 説明: この処理だけで使う変数を外へ漏らさないため、即時関数を開始します。
(function () {
    // 説明: 厳格モードを有効にして、JavaScriptのミスを検出しやすくします。
    'use strict';

    // 説明: スマホ幅かどうかを判定する条件に、画面幅の判定条件を入れます。
    var mediaQuery = window.matchMedia('(max-width: 599px)');
    // 説明: コメントなし案内文に使うクラス名に「'mobile-single-comment-empty'」の結果を入れます。
    var emptyClass = 'mobile-single-comment-empty';

    // 説明: isSinglePostPage 関数を定義します。引数は「なし」です。
    function isSinglePostPage() {
        // 説明: 条件「!document.body」を満たす場合だけ、次の処理を実行します。
        if (!document.body) {
            // 説明: 条件に合わないため false を返します。
            return false;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「document.body.classList.contains('single-post') ||」を呼び出し元へ返します。
        return document.body.classList.contains('single-post') ||
               // 説明: 対象要素に指定クラスが付いているか確認します。
               document.body.classList.contains('post-template') ||
               // 説明: 対象要素に指定クラスが付いているか確認します。
               document.body.classList.contains('single');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: hasCommentList 関数を定義します。引数は「なし」です。
    function hasCommentList() {
        // 説明: 計算・取得した結果「!!document.querySelector('#right-box > ol > li');」を呼び出し元へ返します。
        return !!document.querySelector('#right-box > ol > li');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getCommentTitle 関数を定義します。引数は「なし」です。
    function getCommentTitle() {
        // 説明: 計算・取得した結果「document.querySelector('.comment-section-title');」を呼び出し元へ返します。
        return document.querySelector('.comment-section-title');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: removeEmptyMessage 関数を定義します。引数は「なし」です。
    function removeEmptyMessage() {
        // 説明: すでに表示されているコメントなし案内文に、条件に合う最初のHTML要素を取得して入れます。
        var oldMessage = document.querySelector('.' + emptyClass);

        // 説明: 条件「oldMessage」を満たす場合だけ、次の処理を実行します。
        if (oldMessage) {
            // 説明: 対象要素をHTML上から削除します。
            oldMessage.remove();
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: addMobileEmptyMessage 関数を定義します。引数は「なし」です。
    function addMobileEmptyMessage() {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var title;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var message;

        // 説明: 条件「!mediaQuery.matches || !isSinglePostPage()」を満たす場合だけ、次の処理を実行します。
        if (!mediaQuery.matches || !isSinglePostPage()) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            removeEmptyMessage();
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「hasCommentList()」を満たす場合だけ、次の処理を実行します。
        if (hasCommentList()) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            removeEmptyMessage();
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「document.querySelector('.' + emptyClass)」を満たす場合だけ、次の処理を実行します。
        if (document.querySelector('.' + emptyClass)) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: title に「getCommentTitle()」を代入します。
        title = getCommentTitle();

        // 説明: 条件「!title || !title.parentNode」を満たす場合だけ、次の処理を実行します。
        if (!title || !title.parentNode) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: message に「document.createElement('div')」を代入します。
        message = document.createElement('div');
        // 説明: message.className にCSS用のクラス名を設定します。
        message.className = emptyClass;
        // 説明: message.textContent の表示文字を「'コメントはまだありません。'」に変更します。textContentなのでHTMLとして実行されません。
        message.textContent = 'コメントはまだありません。';

        // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
        title.parentNode.insertBefore(message, title.nextSibling);
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「document.readyState === 'loading'」を満たす場合だけ、次の処理を実行します。
    if (document.readyState === 'loading') {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        document.addEventListener('DOMContentLoaded', addMobileEmptyMessage);
    // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
    } else {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        addMobileEmptyMessage();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 指定したイベントが発生した時に実行する処理を登録します。
    window.addEventListener('resize', addMobileEmptyMessage);

    // 説明: 条件「mediaQuery.addEventListener」を満たす場合だけ、次の処理を実行します。
    if (mediaQuery.addEventListener) {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        mediaQuery.addEventListener('change', addMobileEmptyMessage);
    // 説明: 前の条件に当てはまらず、条件「mediaQuery.addListener」を満たす場合に実行します。
    } else if (mediaQuery.addListener) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        mediaQuery.addListener(addMobileEmptyMessage);
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 即時関数の定義を閉じ、その場で実行します。
}());

/* ===================================================
   スマホ＋タブレット：投稿ページタイトルをカレンダー付き表示にする
   ・PCでは元のタイトル / メタ情報の位置へ戻す
   ・single.php は変更しない
   ・日付 / カテゴリ / コメント数のアイコンはCSS側で非表示
=================================================== */
// 説明: この処理だけで使う変数を外へ漏らさないため、即時関数を開始します。
(function () {
    // 説明: 厳格モードを有効にして、JavaScriptのミスを検出しやすくします。
    'use strict';

    // 説明: スマホ幅かどうかを判定する条件に、画面幅の判定条件を入れます。
    var mediaQuery = window.matchMedia('(max-width: 599px)');

    // 説明: 移動前の位置を覚えるための目印コメントを、まだ何も入っていない状態で用意します。
    var originalMarker = null;
    // 説明: 移動前の親要素を保存する変数を、まだ何も入っていない状態で用意します。
    var originalParent = null;

    // 説明: isSinglePostPage 関数を定義します。引数は「なし」です。
    function isSinglePostPage() {
        // 説明: 条件「!document.body」を満たす場合だけ、次の処理を実行します。
        if (!document.body) {
            // 説明: 条件に合わないため false を返します。
            return false;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「document.body.classList.contains('single-post') ||」を呼び出し元へ返します。
        return document.body.classList.contains('single-post') ||
               // 説明: 対象要素に指定クラスが付いているか確認します。
               document.body.classList.contains('post-template') ||
               // 説明: 対象要素に指定クラスが付いているか確認します。
               document.body.classList.contains('single');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getTitle 関数を定義します。引数は「なし」です。
    function getTitle() {
        // 説明: 計算・取得した結果「document.querySelector('.postpage-title');」を呼び出し元へ返します。
        return document.querySelector('.postpage-title');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getMeta 関数を定義します。引数は「なし」です。
    function getMeta() {
        // 説明: 計算・取得した結果「document.querySelector('.postpage-meta');」を呼び出し元へ返します。
        return document.querySelector('.postpage-meta');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getDateText 関数を定義します。引数は「meta」です。
    function getDateText(meta) {
        // 説明: 日付文字を持つ要素に、条件に合う最初のHTML要素を取得して入れます。
        var dateNode = meta ? meta.querySelector('.published .meta-text') : null;
        // 説明: 計算・取得した結果「dateNode ? dateNode.textContent.trim() : '';」を呼び出し元へ返します。
        return dateNode ? dateNode.textContent.trim() : '';
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: getMobileDateText 関数を定義します。引数は「meta」です。
    function getMobileDateText(meta) {
    // 説明: 日付文字を持つ要素に、条件に合う最初のHTML要素を取得して入れます。
    var dateNode = meta ? meta.querySelector('.published .meta-text') : null;

    // 説明: 条件「!dateNode」を満たす場合だけ、次の処理を実行します。
    if (!dateNode) {
        // 説明: 計算・取得した結果「'';」を呼び出し元へ返します。
        return '';
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 計算・取得した結果「(」を呼び出し元へ返します。
    return (
        // 説明: 対象要素からHTML属性の値を取得します。
        dateNode.getAttribute('data-mobile-date') ||
        // 説明: 前の条件がfalseの場合に、次の条件も確認します。
        dateNode.textContent ||
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        ''
    // 説明: 文字列の前後の余計な空白を削除します。
    ).trim();
// 説明: 現在の処理ブロックを閉じます。
}

// 説明: applyMobileDateText 関数を定義します。引数は「meta」です。
function applyMobileDateText(meta) {
    // 説明: 日付文字を持つ要素に、条件に合う最初のHTML要素を取得して入れます。
    var dateNode = meta ? meta.querySelector('.published .meta-text') : null;
    // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
    var mobileDate;

    // 説明: 条件「!dateNode」を満たす場合だけ、次の処理を実行します。
    if (!dateNode) {
        // 説明: これ以上処理せず、この関数を終了します。
        return;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「!dateNode.getAttribute('data-original-date-text')」を満たす場合だけ、次の処理を実行します。
    if (!dateNode.getAttribute('data-original-date-text')) {
        // 説明: 対象要素にHTML属性を設定します。
        dateNode.setAttribute('data-original-date-text', dateNode.textContent);
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: mobileDate に「getMobileDateText(meta)」を代入します。
    mobileDate = getMobileDateText(meta);

    // 説明: 条件「mobileDate」を満たす場合だけ、次の処理を実行します。
    if (mobileDate) {
        // 説明: dateNode.textContent の表示文字を「mobileDate」に変更します。textContentなのでHTMLとして実行されません。
        dateNode.textContent = mobileDate;
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 現在の処理ブロックを閉じます。
}

// 説明: restoreDateText 関数を定義します。引数は「meta」です。
function restoreDateText(meta) {
    // 説明: 日付文字を持つ要素に、条件に合う最初のHTML要素を取得して入れます。
    var dateNode = meta ? meta.querySelector('.published .meta-text') : null;
    // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
    var original;

    // 説明: 条件「!dateNode」を満たす場合だけ、次の処理を実行します。
    if (!dateNode) {
        // 説明: これ以上処理せず、この関数を終了します。
        return;
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: original に「dateNode.getAttribute('data-original-date-text')」を代入します。
    original = dateNode.getAttribute('data-original-date-text');

    // 説明: 条件「original !== null」を満たす場合だけ、次の処理を実行します。
    if (original !== null) {
        // 説明: dateNode.textContent の表示文字を「original」に変更します。textContentなのでHTMLとして実行されません。
        dateNode.textContent = original;
        // 説明: 対象要素からHTML属性を削除します。
        dateNode.removeAttribute('data-original-date-text');
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 現在の処理ブロックを閉じます。
}

    // 説明: parseDateParts 関数を定義します。引数は「dateText」です。
    function parseDateParts(dateText) {
        // 説明: 正規表現に一致した結果に「dateText.match(/(\d{4})[\/.-](\d{1,2})[\/.-](\d{1,2})/)」の結果を入れます。
        var match = dateText.match(/(\d{4})[\/.-](\d{1,2})[\/.-](\d{1,2})/);

        // 説明: 条件「!match」を満たす場合だけ、次の処理を実行します。
        if (!match) {
            // 説明: 計算・取得した結果「{」を呼び出し元へ返します。
            return {
                // 説明: 前の処理の続きとして、この値を並べて指定します。
                month: '',
                // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
                day: ''
            // 説明: オブジェクトまたは関数定義を閉じます。
            };
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 計算・取得した結果「{」を呼び出し元へ返します。
        return {
            // 説明: 文字列を整数に変換します。
            month: String(parseInt(match[2], 10)) + '月',
            // 説明: 文字列を整数に変換します。
            day: String(parseInt(match[3], 10))
        // 説明: オブジェクトまたは関数定義を閉じます。
        };
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: normalizeCommentText 関数を定義します。引数は「meta」です。
    function normalizeCommentText(meta) {
        // 説明: コメント数リンク要素に、条件に合う最初のHTML要素を取得して入れます。
        var commentLink = meta ? meta.querySelector('.comment-count a') : null;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var raw;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var numberMatch;

        // 説明: 条件「!commentLink」を満たす場合だけ、次の処理を実行します。
        if (!commentLink) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「!commentLink.getAttribute('data-original-comment-text')」を満たす場合だけ、次の処理を実行します。
        if (!commentLink.getAttribute('data-original-comment-text')) {
            // 説明: 対象要素にHTML属性を設定します。
            commentLink.setAttribute('data-original-comment-text', commentLink.textContent);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: raw に「commentLink.textContent.trim()」を代入します。
        raw = commentLink.textContent.trim();
        // 説明: numberMatch に「raw.match(/\d+/)」を代入します。
        numberMatch = raw.match(/\d+/);

        // 説明: 条件「numberMatch」を満たす場合だけ、次の処理を実行します。
        if (numberMatch) {
            // 説明: commentLink.textContent の表示文字を「'コメント(' + numberMatch[0] + ')'」に変更します。textContentなのでHTMLとして実行されません。
            commentLink.textContent = 'コメント(' + numberMatch[0] + ')';
        // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
        } else {
            // 説明: commentLink.textContent の表示文字を「'コメント(0)'」に変更します。textContentなのでHTMLとして実行されません。
            commentLink.textContent = 'コメント(0)';
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: restoreCommentText 関数を定義します。引数は「meta」です。
    function restoreCommentText(meta) {
        // 説明: コメント数リンク要素に、条件に合う最初のHTML要素を取得して入れます。
        var commentLink = meta ? meta.querySelector('.comment-count a') : null;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var original;

        // 説明: 条件「!commentLink」を満たす場合だけ、次の処理を実行します。
        if (!commentLink) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: original に「commentLink.getAttribute('data-original-comment-text')」を代入します。
        original = commentLink.getAttribute('data-original-comment-text');

        // 説明: 条件「original !== null」を満たす場合だけ、次の処理を実行します。
        if (original !== null) {
            // 説明: commentLink.textContent の表示文字を「original」に変更します。textContentなのでHTMLとして実行されません。
            commentLink.textContent = original;
            // 説明: 対象要素からHTML属性を削除します。
            commentLink.removeAttribute('data-original-comment-text');
        // 説明: 現在の処理ブロックを閉じます。
        }
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: buildMobileTitle 関数を定義します。引数は「なし」です。
    function buildMobileTitle() {
        // 説明: 見出し・タイトル要素に「getTitle()」の結果を入れます。
        var title = getTitle();
        // 説明: 投稿の日付・カテゴリ・コメント数のメタ情報要素に「getMeta()」の結果を入れます。
        var meta = getMeta();
        // 説明: 作成済みのスマホ用タイトル囲みに、条件に合う最初のHTML要素を取得して入れます。
        var oldWrap = document.querySelector('.mobile-post-head');
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var wrap;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var calendar;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var month;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var day;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var body;
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        var dateParts;

        // 説明: 条件「!mediaQuery.matches || !isSinglePostPage()」を満たす場合だけ、次の処理を実行します。
        if (!mediaQuery.matches || !isSinglePostPage()) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            restoreTitle();
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「!title || !meta」を満たす場合だけ、次の処理を実行します。
        if (!title || !meta) {
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「oldWrap」を満たす場合だけ、次の処理を実行します。
        if (oldWrap) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            normalizeCommentText(meta);
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            applyMobileDateText(meta);
            // 説明: これ以上処理せず、この関数を終了します。
            return;
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: originalParent に「title.parentNode」を代入します。
        originalParent = title.parentNode;
        // 説明: originalMarker に「document.createComment('original post title position')」を代入します。
        originalMarker = document.createComment('original post title position');
        // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
        originalParent.insertBefore(originalMarker, title);

        // 説明: dateParts に「parseDateParts(getDateText(meta))」を代入します。
        dateParts = parseDateParts(getDateText(meta));

        // 説明: wrap に「document.createElement('div')」を代入します。
        wrap = document.createElement('div');
        // 説明: wrap.className にCSS用のクラス名を設定します。
        wrap.className = 'mobile-post-head';

        // 説明: calendar に「document.createElement('div')」を代入します。
        calendar = document.createElement('div');
        // 説明: calendar.className にCSS用のクラス名を設定します。
        calendar.className = 'mobile-post-calendar';

        // 説明: month に「document.createElement('div')」を代入します。
        month = document.createElement('div');
        // 説明: month.className にCSS用のクラス名を設定します。
        month.className = 'mobile-post-calendar-month';
        // 説明: month.textContent の表示文字を「dateParts.month」に変更します。textContentなのでHTMLとして実行されません。
        month.textContent = dateParts.month;

        // 説明: day に「document.createElement('div')」を代入します。
        day = document.createElement('div');
        // 説明: day.className にCSS用のクラス名を設定します。
        day.className = 'mobile-post-calendar-day';
        // 説明: day.textContent の表示文字を「dateParts.day」に変更します。textContentなのでHTMLとして実行されません。
        day.textContent = dateParts.day;

        // 説明: body に「document.createElement('div')」を代入します。
        body = document.createElement('div');
        // 説明: body.className にCSS用のクラス名を設定します。
        body.className = 'mobile-post-head-body';

        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        calendar.appendChild(month);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        calendar.appendChild(day);

        // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
        originalParent.insertBefore(wrap, originalMarker.nextSibling);

        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        body.appendChild(title);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        body.appendChild(meta);

        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        wrap.appendChild(calendar);
        // 説明: 指定した要素を、親要素の最後の子要素として追加します。
        wrap.appendChild(body);

        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        normalizeCommentText(meta);
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        applyMobileDateText(meta);

        // 説明: 対象要素にCSSクラスを追加して、表示や状態を切り替えます。
        document.documentElement.classList.add('is-mobile-post-head-ready');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: restoreTitle 関数を定義します。引数は「なし」です。
    function restoreTitle() {
        // 説明: 見出し・タイトル要素に「getTitle()」の結果を入れます。
        var title = getTitle();
        // 説明: 投稿の日付・カテゴリ・コメント数のメタ情報要素に「getMeta()」の結果を入れます。
        var meta = getMeta();
        // 説明: スマホ用タイトル全体の囲み要素に、条件に合う最初のHTML要素を取得して入れます。
        var wrap = document.querySelector('.mobile-post-head');

        // 説明: 条件「meta」を満たす場合だけ、次の処理を実行します。
        if (meta) {
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            restoreCommentText(meta);
            // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
            restoreDateText(meta);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「title && meta && originalParent && originalMarker && originalMarker.parentNode === originalParent」を満たす場合だけ、次の処理を実行します。
        if (title && meta && originalParent && originalMarker && originalMarker.parentNode === originalParent) {
            // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
            originalParent.insertBefore(title, originalMarker.nextSibling);
            // 説明: 指定した要素を、基準になる要素の直前に移動または追加します。
            originalParent.insertBefore(meta, title.nextSibling);
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 条件「wrap」を満たす場合だけ、次の処理を実行します。
        if (wrap) {
            // 説明: 対象要素をHTML上から削除します。
            wrap.remove();
        // 説明: 現在の処理ブロックを閉じます。
        }

        // 説明: 対象要素からCSSクラスを削除して、表示や状態を戻します。
        document.documentElement.classList.remove('is-mobile-post-head-ready');
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 条件「document.readyState === 'loading'」を満たす場合だけ、次の処理を実行します。
    if (document.readyState === 'loading') {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        document.addEventListener('DOMContentLoaded', buildMobileTitle);
    // 説明: 前の条件に当てはまらなかった場合の処理を開始します。
    } else {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        buildMobileTitle();
    // 説明: 現在の処理ブロックを閉じます。
    }

    // 説明: 指定したイベントが発生した時に実行する処理を登録します。
    window.addEventListener('resize', buildMobileTitle);

    // 説明: 条件「mediaQuery.addEventListener」を満たす場合だけ、次の処理を実行します。
    if (mediaQuery.addEventListener) {
        // 説明: 指定したイベントが発生した時に実行する処理を登録します。
        mediaQuery.addEventListener('change', buildMobileTitle);
    // 説明: 前の条件に当てはまらず、条件「mediaQuery.addListener」を満たす場合に実行します。
    } else if (mediaQuery.addListener) {
        // 説明: この行は、直前の処理に必要なJavaScriptの命令です。
        mediaQuery.addListener(buildMobileTitle);
    // 説明: 現在の処理ブロックを閉じます。
    }
// 説明: 即時関数の定義を閉じ、その場で実行します。
}());
