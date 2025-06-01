// ボタンのクリックや状態変更をハンドリング
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.like-toggle').forEach(button => {
        button.addEventListener('click', async () => {
            const wrapper = button.closest('.like-button');
            const uniqueId = wrapper.dataset.uniqueId;
            const countElem = wrapper.querySelector('.like-count');

            const response = await fetch('/wp-json/like/v1/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ unique_id: uniqueId })
            });

            const result = await response.json();
            if (result.success) {
                countElem.textContent = result.count;
                button.classList.toggle('liked', result.liked);
                button.querySelector('svg').setAttribute('fill', result.liked ? '#e0245e' : '#888');
            }
        });
    });
});
