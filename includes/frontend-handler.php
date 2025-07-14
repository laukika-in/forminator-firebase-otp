<?php

add_action('wp_footer', 'ffotp_enqueue_otp_script');

function ffotp_enqueue_otp_script() {
    if (!is_singular()) return;

    $settings = get_option('ffotp_settings');
    $mappings = $settings['form_mappings'] ?? [];

    if (empty($mappings)) return;

    // Enqueue Firebase SDK
    echo '<script src="https://www.gstatic.com/firebasejs/8.6.2/firebase-app.js"></script>';
    echo '<script src="https://www.gstatic.com/firebasejs/8.6.2/firebase-auth.js"></script>';

    // Init Firebase
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var firebaseConfig = {
            <?php echo ffotp_get_firebase_config_js(); ?>
        };
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
    });
    </script>
    <?php

    // Enqueue OTP handler script
    wp_enqueue_script('ffotp-js', FFOTP_URL . 'assets/js/otp-handler.js', [], null, true);
    wp_localize_script('ffotp-js', 'FFOTP_Settings', [
        'forms' => $mappings
    ]);
}
