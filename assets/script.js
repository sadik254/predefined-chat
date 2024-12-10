jQuery(document).ready(function ($) {
    const chatBox = $('#chat-box');
    const userInput = $('#user-input');
    const agentContactForm = $('#agent-contact-form');
    
    // Centralized responses to ensure consistency
    const responses = {
        'services': 'We offer HVAC and renovation services for both homes and businesses. Our expert team specializes in home maintenance and improvement.',
        'dtechservices': 'At DtechServices, we are committed to enhancing your living spaces with our expert home renovation, HVAC, and essential maintenance services. With years of experience and a dedicated team of professionals, we ensure your home looks great and functions at its best.',
        'agent': 'Please fill out the contact form to connect with an agent. An agent will reach out to you shortly.',
        'default': 'I\'m afraid I didn\'t quite understand. Could you please rephrase your question or choose from the predefined options?'
    };

    // Function to add message to chat box
    function addMessage(sender, message) {
        chatBox.append(`<div class="${sender}-message"><strong>${sender === 'user' ? 'User' : 'Agent'}:</strong> ${message}</div>`);
        chatBox.scrollTop(chatBox[0].scrollHeight);
    }

    // Function to get bot response
    function getBotResponse(message) {
        message = message.toLowerCase();
        
        if (message.includes('services')) return responses.services;
        if (message.includes('dtechservices')) return responses.dtechservices;
        if (message.includes('agent')) {
            // Show agent contact form
            agentContactForm.show();
            return responses.agent;
        }
        
        return responses.default;
    }

    // Initial greeting
    addMessage('bot', 'Welcome to DtechServices, I am DTechservices AI bot. How can I help you today?');

    // Handle send button click
    $('#send-btn').on('click', function () {
        const message = userInput.val().trim();
        if (!message) return;

        // Add user message
        addMessage('user', message);
        
        // Get and add bot response
        setTimeout(() => {
            const botResponse = getBotResponse(message);
            addMessage('bot', botResponse);
        }, 1000);

        // Clear input
        userInput.val('');
    });

    // Handle predefined question clicks
    $('.predefined-question').on('click', function () {
        const question = $(this).text();
        
        // Add user's question
        addMessage('user', question);

        // Get and add bot response
        setTimeout(() => {
            let botResponse = '';
            switch(question) {
                case 'What services do you provide?':
                    botResponse = responses.services;
                    break;
                case 'What is DtechServices?':
                    botResponse = responses.dtechservices;
                    break;
                case 'Talk to an agent':
                    // Show agent contact form
                    agentContactForm.show();
                    botResponse = responses.agent;
                    break;
                default:
                    botResponse = responses.default;
            }
            addMessage('bot', botResponse);
        }, 1000);
    });

    // Handle agent contact form submission
    $('#agent-contact-form-fields').on('submit', function (e) {
        e.preventDefault();

        const email = $('#user-email').val().trim();
        const phone = $('#user-phone').val().trim();

        // Basic validation
        if (!email || !phone) {
            addMessage('bot', 'Please fill in both email and phone number.');
            return;
        }

        // Debug logging
        console.log('Form submitted');
        console.log('Email:', email);
        console.log('Phone:', phone);
        console.log('AJAX URL:', chatData.ajaxurl);
        console.log('Nonce:', $('input[name="security"]').val());

        // AJAX submission
        $.ajax({
            url: chatData.ajaxurl,
            type: 'POST',
            data: {
                action: 'agent_contact_form',
                security: $('input[name="security"]').val(), // Corrected nonce selector
                user_email: email,
                user_phone: phone
            },
            success: function(response) {
                console.log('AJAX Success Response:', response);
                if (response.success) {
                    addMessage('bot', 'Thank you! An agent will contact you soon.');
                    // Hide the form after successful submission
                    agentContactForm.hide();
                    // Clear form fields
                    $('#user-email, #user-phone').val('');
                } else {
                    console.log('Error details:', response);
                    addMessage('bot', response.data || 'There was an error submitting your request. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.log('XHR Response Text:', xhr.responseText);
                addMessage('bot', 'There was a network error. Please try again.');
            }
        });
    });

    // Optional: Handle Enter key in input
    userInput.on('keypress', function (e) {
        if (e.which === 13) {
            $('#send-btn').click();
        }
    });
});