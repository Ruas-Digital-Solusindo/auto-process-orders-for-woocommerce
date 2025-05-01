(function($) {
    $(document).ready(function() {
        // Menambahkan aksi saat tombol 'Save Settings' diklik
        $('form.ruas-settings-form').on('submit', function(e) {
            var orderStatus = $('#ruas_free_order_status').val();
            
            if (!orderStatus) {
                alert('Please select a status for free orders.');
                e.preventDefault();
            }
        });

        // Bisa menambahkan fungsionalitas lainnya sesuai kebutuhan
        console.log('Admin script loaded');
    });
})(jQuery);
