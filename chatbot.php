<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    $("#chatForm").submit(function(e){
        e.preventDefault();
        var msg = $("#message").val();

        $.post("send_message.php", {message: msg}, function(data){
            let res = JSON.parse(data);

            $(".chat-history").append(
                `<p class="user-msg">${res.user}</p>
                 <p class="bot-msg">${res.bot}</p>`
            );
            $("#message").val("");
        });
    });
});
</script>
