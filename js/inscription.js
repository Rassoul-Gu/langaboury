document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('api_simple.php?action=register_player', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    alert(data.message);
});