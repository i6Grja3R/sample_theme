// console.log('like_vars:', like_vars); // ← この行を追加（確認用）
document.addEventListener('DOMContentLoaded', () => { // DOM（HTML）がすべて読み込まれてから中のコードを実行、jQueryでいう $(document).ready() と同じ意味。
    // ← この直後にログを入れる
    console.log('nonce:', like_vars.nonce);  // ← ここ！
    const buttons = document.querySelectorAll('.quest-likeButton');
    // 各ボタンに click イベントを設定。
    buttons.forEach(button => {
        // ボタンがクリックされたら非同期処理を実行（async 関数）。
        button.addEventListener('click', async () => {
            console.log('clicked');
            const postId = button.getAttribute('data-postid');
            console.log('postId:', postId); // ここを追加
            try {
                const response = await fetch(like_vars.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'handle_like_action',
                        nonce: like_vars.nonce,
                        post_id: postId
                    })
                });
        
                const responseText = await response.text();
                console.log('Raw response:', responseText);
        
                const data = JSON.parse(responseText); // ここで JSON パースが失敗したらサーバー側に問題あり
        
                if (data.success) {
                    button.classList.toggle('active');
                    const countSpan = button.querySelector('.likeCount');
                    if (countSpan) {
                        countSpan.textContent = data.count;
                    }
                } else {
                    console.error('Like failed:', data.message);
                }
            } catch (error) {
                console.error('通信エラーまたは JSON パースエラー:', error);
            }
        });
    });
});