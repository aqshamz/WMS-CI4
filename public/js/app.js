document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleBtn");
    const sidebar = document.getElementById("sidebar");

    toggleBtn.addEventListener("click", function () {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle("show"); // mobile slide in/out
        } else {
            sidebar.classList.toggle("collapsed"); // desktop shrink
        }
    });

     $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            let tokenName = $('meta[name="csrf-token-name"]').attr('content');
            let tokenHash = $('meta[name="csrf-token-hash"]').attr('content');
            if (settings.type === 'POST' || settings.type === 'PUT' || settings.type === 'DELETE') {
                settings.data = settings.data || {};
                if (typeof settings.data === "string") {
                    settings.data += `&${tokenName}=${tokenHash}`;
                } else {
                    settings.data[tokenName] = tokenHash;
                }
            }
        },
        complete: function(xhr) {
            let newToken = xhr.responseJSON?.csrfHash;
            if (newToken) {
                $('meta[name="csrf-token-hash"]').attr('content', newToken);
            }
        }
    });

    $(document).on('submit', 'form', function () {
        let tokenName = $('meta[name="csrf-token-name"]').attr('content');
        let tokenHash = $('meta[name="csrf-token-hash"]').attr('content');

        let csrfField = $(this).find('input[name="' + tokenName + '"]');

        if (csrfField.length) {
            csrfField.val(tokenHash); // update existing
        } else {
            $(this).append('<input type="hidden" name="' + tokenName + '" value="' + tokenHash + '">');
        }
    });

});
