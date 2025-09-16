<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Chatbot</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(to right, #6a11cb, #2575fc); font-family: Arial, sans-serif; }
        .chat-container { max-width: 750px; margin: 50px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .chat-header { background: #2575fc; color: white; padding: 15px; font-size: 1.2rem; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .chat-history { height: 400px; overflow-y: auto; padding: 20px; background: #f9f9f9; }
        .chat-message { margin: 10px 0; display: flex; }
        .chat-message.user { justify-content: flex-end; }
        .chat-message.bot { justify-content: flex-start; }
        .chat-bubble { padding: 12px 18px; border-radius: 20px; max-width: 70%; font-size: 0.95rem; line-height: 1.4; }
        .chat-bubble.user { background: #2575fc; color: white; border-bottom-right-radius: 5px; }
        .chat-bubble.bot { background: #e9ecef; color: #333; border-bottom-left-radius: 5px; }
        .chat-footer { padding: 15px; border-top: 1px solid #ddd; background: #fff; display: flex; }
        .chat-footer input { border-radius: 25px; flex-grow: 1; margin-right: 10px; }
        .chat-footer button { border-radius: 25px; }
        
        /* Typing indicator styles */
        .typing-indicator {
            display: inline-flex;
            align-items: center;
            height: 20px;
        }
        .typing-indicator span {
            height: 8px;
            width: 8px;
            background-color: #6c757d;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
            animation: typing 1.2s infinite;
        }
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes typing {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        /* Medical disclaimer styles */
        .medical-disclaimer {
            margin: 10px;
            padding: 10px;
            border-radius: 8px;
        }
        
        /* Clear history button */
        .clear-history-btn {
            background: #ff4757;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        .clear-history-btn:hover {
            background: #ff6b81;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            color: #6c757d;
            padding: 40px 20px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <span>🤖 Medical Chatbot - Welcome, <?php echo $_SESSION['fullname']; ?>!</span>
            <div>
                <button id="clearHistoryBtn" class="clear-history-btn">Clear History</button>
                <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
            </div>
        </div>
        
        <!-- Medical Disclaimer -->
        <div class="alert alert-warning medical-disclaimer" role="alert">
            <strong>Important:</strong> This chatbot provides general health information only and is not a substitute for professional medical advice. For emergencies, please contact your local emergency services immediately.
        </div>
        
        <div class="chat-history" id="chatHistory">
            <div class="empty-state" id="emptyState">
                <p>No messages yet. Start a conversation by typing below!</p>
            </div>
        </div>
        <div class="chat-footer">
            <input type="text" id="message" name="message" class="form-control" placeholder="Type your message..." required>
            <button type="submit" class="btn btn-primary" id="sendBtn">Send</button>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    // Load chat history
    loadChatHistory();
    
    // Function to show typing indicator
    function showTypingIndicator() {
        var typingHtml = `<div class="chat-message bot" id="typingIndicator">
            <div class="chat-bubble bot">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>`;
        $("#chatHistory").append(typingHtml);
        $("#chatHistory").scrollTop($("#chatHistory")[0].scrollHeight);
    }
    
    // Function to remove typing indicator
    function hideTypingIndicator() {
        $("#typingIndicator").remove();
    }
    
    // Handle send message
    $("#sendBtn").click(sendMessage);
    $("#message").keypress(function(e) {
        if(e.which == 13) {
            sendMessage();
            return false;
        }
    });
    
    function sendMessage() {
        var msg = $("#message").val().trim();
        
        if (msg === "") return;
        
        // Hide empty state if visible
        $("#emptyState").hide();
        
        // Disable input while processing
        $("#message").prop("disabled", true);
        $("#sendBtn").prop("disabled", true);
        
        // Show user message immediately
        appendMessage(msg, "user");
        
        // Show typing indicator
        showTypingIndicator();
        
        // Send to server
        $.post("sendmessage.php", {message: msg}, function(data){
            // Remove typing indicator
            hideTypingIndicator();
            
            if (data.error) {
                appendMessage("❌ Error: " + data.error, "bot");
            } else {
                appendMessage(data.bot, "bot");
            }
            
            // Re-enable input
            $("#message").val("").prop("disabled", false);
            $("#sendBtn").prop("disabled", false);
            $("#message").focus();
        }, "json").fail(function(xhr, status, error){
            // Remove typing indicator
            hideTypingIndicator();
            appendMessage("❌ Sorry, I'm having trouble connecting. Please try again.", "bot");
            $("#message").prop("disabled", false);
            $("#sendBtn").prop("disabled", false);
            console.error("Error:", status, error);
        });
    }
    
    // Handle clear history
    $("#clearHistoryBtn").click(function() {
        if(confirm("Are you sure you want to clear your chat history? This action cannot be undone.")) {
            // Show loading state on button
            var originalText = $(this).text();
            $(this).text("Clearing...").prop("disabled", true);
            
            $.post("clear_history.php", function(response) {
                // Restore button state
                $("#clearHistoryBtn").text(originalText).prop("disabled", false);
                
                if (response.success) {
                    // Clear the chat interface
                    $("#chatHistory").empty();
                    
                    // Show empty state
                    $("#chatHistory").html('<div class="empty-state" id="emptyState"><p>No messages yet. Start a conversation by typing below!</p></div>');
                    
                    // Show confirmation message
                    appendMessage("Your chat history has been cleared.", "bot");
                } else {
                    alert("Error clearing history: " + (response.error || "Unknown error"));
                    console.error("Clear history error:", response);
                }
            }, "json").fail(function(xhr, status, error) {
                // Restore button state
                $("#clearHistoryBtn").text(originalText).prop("disabled", false);
                alert("Error clearing history: " + error);
                console.error("AJAX Error:", status, error);
            });
        }
    });
    
    function appendMessage(message, type) {
        // Hide empty state if it exists
        $("#emptyState").hide();
        
        var bubbleClass = type === "user" ? "user" : "bot";
        var html = `<div class="chat-message ${type}"><div class="chat-bubble ${bubbleClass}">${message}</div></div>`;
        $("#chatHistory").append(html);
        $("#chatHistory").scrollTop($("#chatHistory")[0].scrollHeight);
    }
    
    function loadChatHistory() {
        $.get("get_chat_history.php", function(data){
            if (data.messages && data.messages.length > 0) {
                // Hide empty state
                $("#emptyState").hide();
                
                data.messages.forEach(function(msg){
                    if (msg.user_message) appendMessage(msg.user_message, "user");
                    if (msg.bot_response) appendMessage(msg.bot_response, "bot");
                });
            } else {
                // Show empty state if no messages
                $("#emptyState").show();
            }
        }, "json").fail(function(xhr, status, error) {
            console.error("Error loading chat history:", status, error);
            appendMessage("❌ Error loading chat history. Please refresh the page.", "bot");
        });
    }
});
</script>
</body>
</html>