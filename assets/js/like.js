// assets/js/like.js
// 初回訪問時は Cookie がないため、ページリロードなしで機能させたいなら JS も併用（JavaScript 側で Cookie を事前に発行）
document.addEventListener('DOMContentLoaded', function () {
    if (!document.cookie.includes('user_id')) {
        const guestId = crypto.randomUUID(); // 安全なUUID生成
        document.cookie = `user_id=${guestId}; path=/; max-age=${10 * 365 * 24 * 60 * 60}`;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quest-likeButton').forEach(button => {
        button.addEventListener('click', async () => {
            const uniqueId = button.dataset.uniqueid;
            const countElem = button.querySelector('.likeCount');

            const response = await fetch(like_vars.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'handle_like_action', // ← WordPressのアクション名
                    unique_id: uniqueId,
                    nonce: like_vars.nonce
                })
            });

            const result = await response.json();
            if (result.success) {
                countElem.textContent = result.data.count;
                button.classList.toggle('active', result.data.liked);
                button.querySelector('svg').setAttribute('fill', result.data.liked ? '#e0245e' : '#888');
            } else {
                console.error(result.data?.message ?? 'いいねに失敗しました');
            }
        });
    });
});

