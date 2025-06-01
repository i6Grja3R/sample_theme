// ボタンのクリックや状態変更をハンドリング
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.like-button');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const uniqueId = button.getAttribute('data-uniqueid');
            const countSpan = button.querySelector('.likeCount');

            // CSRF防止用nonceが存在しなければ中断
            if (typeof like_vars === 'undefined' || !like_vars.nonce) {
                console.error('like_vars.nonce が見つかりません');
                return;
            }

            fetch(like_vars.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams({
                    action: 'handle_like_action',
                    unique_id: uniqueId,
                    nonce: like_vars.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && typeof data.data.count !== 'undefined') {
                    // トグル：SVG色変更用クラスを切り替え
                    button.classList.toggle('liked');
                    // 最新の「いいね数」を更新
                    countSpan.textContent = data.data.count;
                } else {
                    console.error('いいね処理に失敗:', data);
                }
            })
            .catch(error => {
                console.error('AJAXエラー:', error);
            });
        });
    });
});
