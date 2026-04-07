{{-- Confirm delete modal handled via SweetAlert2 / Alert.confirm() --}}
{{-- This script handles all .btn-delete clicks globally --}}

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;
    e.preventDefault();

    const url = btn.dataset.url;
    const name = btn.dataset.name || 'this item';

    Alert.confirm('Are you sure you want to delete <strong>' + name + '</strong>? This action cannot be undone.', {
        title: 'Delete ' + name + '?',
        icon: 'warning',
        type: 'danger',
        confirmText: 'Yes, delete it',
        cancelText: 'Cancel',
    }).then(function(confirmed) {
        if (!confirmed) return;

        const data = {_method: 'DELETE'};

        axios.post(url, data)
            .then(function(res) {
                Toast.fromResponse(res.data);
                if (res.data.redirect) {
                    setTimeout(() => window.location.href = res.data.redirect, 800);
                } else {
                    setTimeout(() => window.location.reload(), 800);
                }
            })
            .catch(function(err) {
                const data = err.response?.data;
                Toast.error(data?.message || 'Failed to delete. Please try again.');
            });
    });
});
</script>
