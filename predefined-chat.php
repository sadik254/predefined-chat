<?php
/**
 * Plugin Name: Predefined Chat Plugin
 * Description: A simple chat plugin with predefined greetings, questions, and answers.
 * Version: 1.3
 * Author: Saleh Sadik
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enqueue Scripts and Styles.
function chat_plugin_enqueue_scripts() {
    // Enqueue CSS
    wp_enqueue_style('chat-plugin-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), '1.2');
    
    // Enqueue JS with jQuery dependency
    wp_enqueue_script('chat-plugin-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), '1.2', true);
    
    // Localize script with chat data
    wp_localize_script('chat-plugin-script', 'chatData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'chat_plugin_enqueue_scripts');

function send_contact_emails($email, $phone) {
    // Static admin email (replace with your desired email)
    // $admin_email = 'example@example.com';

    // Create a new PHPMailer instance
    $mail_admin = new PHPMailer(true);
    $mail_user = new PHPMailer(true);

    try {
        // Admin Email Configuration
        $mail_admin->isSMTP();
        $mail_admin->Host       = 'smtp.example.email'; // Your SMTP host
        $mail_admin->SMTPAuth   = true;
        $mail_admin->Username   = '';
        $mail_admin->Password   = '';
        $mail_admin->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail_admin->Port       = 465;

        // Set admin email details
        $mail_admin->setFrom('example@example.com', 'DtechServices Agent');
        $mail_admin->addAddress('');
        $mail_admin->Subject = 'New Agent Contact Request';
        $mail_admin->Body    = "New contact request:\nEmail: $email\nPhone: $phone";

        // User Email Configuration (similar to admin, but for user)
        $mail_user->isSMTP();
        $mail_user->Host       = 'smtp.example.email';
        $mail_user->SMTPAuth   = true;
        $mail_user->Username   = '';
        $mail_user->Password   = '';
        $mail_user->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail_user->Port       = 465;

        // Set user email details
        $mail_user->setFrom('example@example.com', 'DtechServices Agent');
        $mail_user->addAddress($email);
        $mail_user->Subject = 'Agent Contact Request Received';
        $mail_user->Body    = "Thank you for your request. An agent will contact you soon.\n\nWe will reach out to you at the email and phone number you provided.";

        // Send emails
        $admin_result = $mail_admin->send();
        $user_result = $mail_user->send();

        // Return results
        return [
            'admin_email_sent' => $admin_result,
            'user_email_sent' => $user_result
        ];

    } catch (Exception $e) {
        // Log any errors
        error_log('Email Sending Error: ' . $mail_admin->ErrorInfo);
        $log_file = plugin_dir_path(__FILE__) . 'error_log.txt';
file_put_contents($log_file, 'Error: ' . $mail_admin->ErrorInfo . PHP_EOL, FILE_APPEND);

        return false;
    }
}


// Handle agent contact form submission via AJAX
function handle_agent_contact_form() {
    // More robust nonce verification
    if (!wp_verify_nonce($_POST['security'], 'agent_contact_form_nonce')) {
        wp_send_json_error('Security check failed');
        exit;
    }

    // Sanitize and validate input
    $email = sanitize_email($_POST['user_email']);
    $phone = sanitize_text_field($_POST['user_phone']);

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error('Invalid email address');
        exit;
    }

    // Send emails using PHPMailer
    $email_result = send_contact_emails($email, $phone);

    if ($email_result) {
        wp_send_json_success('Contact request submitted successfully');
    } else {
        wp_send_json_error('Failed to send emails');
    }

    exit;
}

add_action('wp_ajax_agent_contact_form', 'handle_agent_contact_form');
add_action('wp_ajax_nopriv_agent_contact_form', 'handle_agent_contact_form');

// Shortcode to Display Chat Widget.
function chat_plugin_shortcode() {
    ob_start();
    ?>
    <div id="chat-plugin-container">
        <div id="chat-box">
            <!-- Initial greeting will be added via JavaScript -->
        </div>
        <div id="predefined-questions">
            <button class="predefined-question">What services do you provide?</button>
            <button class="predefined-question">What is DtechServices?</button>
            <button class="predefined-question">Talk to an agent</button>
        </div>
        <div id="agent-contact-form" style="display: none;">
            <form id="agent-contact-form-fields">
                <?php wp_nonce_field('agent_contact_form_nonce', 'security', false, true); ?>
                <div class="form-group">
                    <label for="user-email">Email Address:</label>
                    <input type="email" id="user-email" name="user_email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="user-phone">Phone Number:</label>
                    <input type="tel" id="user-phone" name="user_phone" required placeholder="Enter your phone number">
                </div>
                <button type="submit" id="submit-contact-form">Submit</button>
            </form>
        </div>
        <div id="chat-input">
            <input type="text" id="user-input" placeholder="Type your message..." />
            <button id="send-btn">Send</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('predefined_chat', 'chat_plugin_shortcode');
?>
