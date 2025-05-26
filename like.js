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
            const uniqueId = button.getAttribute('data-uniqueid'); // ← ここ変更
            console.log('uniqueId:', uniqueId); // ← これに変更
            try {
                const response = await fetch(like_vars.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'handle_like_action',
                        nonce: like_vars.nonce,
                        unique_id: uniqueId // ← post_id → unique_id に変更
                    })
                });
        
                // const responseText = await response.text();
                // console.log('Raw response:', responseText);
        
                // body を「一度」読み込み
                // const data = JSON.parse(responseText); // ここで JSON パースが失敗したらサーバー側に問題あり
                const data = await response.json();

                console.log('Response JSON:', data); // ← JSON オブジェクトを直接確認
        
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
                // console.error('通信エラーまたは JSON パースエラー:', error);
                console.error('Error:', error);
            }
        });
    });
});