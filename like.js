// console.log('like_vars:', like_vars); // ← この行を追加（確認用）
document.addEventListener('DOMContentLoaded', () => { // DOM（HTML）がすべて読み込まれてから中のコードを実行、jQueryでいう $(document).ready() と同じ意味。
    // ← この直後にログを入れる
    console.log('nonce:', like_vars.nonce);  // ← ここ！
    const buttons = document.querySelectorAll('.quest-likeButton');
    // 各ボタンに click イベントを設定。
    buttons.forEach(button => {
        // ボタンがクリックされたら非同期処理を実行（async 関数）。
        button.addEventListener('click', async () => {
            console.log('clicked'); // ← ここを追加
            // ボタンの data-postid 属性から、対象の投稿IDを取得。
            const postId = button.getAttribute('data-postid');

            try { // WordPressのAjaxエンドポイント（admin-ajax.php）に非同期POSTリクエストを送る。
                const response = await fetch(like_vars.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'handle_like_action', // action: 'handle_like_action' は WordPress側で処理を振り分けるキー。
                        nonce: like_vars.nonce, // like_vars.nonce は WordPress が発行したセキュリティトークン（CSRF対策）。
                        post_id: postId
                    })
                });
                // サーバーから返ってきたJSONレスポンスをJSオブジェクトに変換。
                const data = await response.json();
                // 成功時、ボタンに active クラスを付け外し（いいね状態の切替を表現）。
                if (data.success) {
                    // DOM更新: スタイル切替やカウント更新
                    button.classList.toggle('active');
                    const countSpan = button.querySelector('.likeCount');
                    if (countSpan) {
                        countSpan.textContent = data.count; // .likeCount という要素を探して、表示されている「いいね数」をサーバーから返された値に更新。
                    }
                } else {
                    console.error('Like failed:', data.message); // サーバーから success: false が返ってきたら、コンソールにエラー表示。
                }
            } catch (error) {
                console.error('Error:', error); // 通信エラーやネットワーク異常が起きた場合も安全にキャッチしてエラーを表示。
            }
        });
    });
});
