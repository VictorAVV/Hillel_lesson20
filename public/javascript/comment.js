$(document).ready(function(){
    $(".btn-slide").click(function(e){
        e.preventDefault();
        var parentComment = $(this).closest("div[class*='comment-']");
        var commentId = parentComment.attr('class').split("-")[1];
        console.log(commentId);
        console.log(parentComment.children("form[id=panel-" + commentId +"]").length);
  
        if (parentComment.children("form[id=panel-" + commentId +"]").length == 0) {
            //как-то переделать, чтобы брать форму из твига.

            console.log($("#comment > #comment__token").val());

            parentComment.append("<form id='panel-" + commentId + "' style='display:none;' name='comment' method='post'>" +
                    "<div id='commentToComment'>" +
                        "<div class='form-group'>" +
                            "<label class='control-label' for='comment_content'>Ваш комментарий:</label>" +
                            "<textarea id='comment_content' name='comment[content]' maxlength='2000' pattern='.{3,}' class='form-control'></textarea>" +
                        "</div>"+
                        "<input type='hidden' id='comment__token' name='comment[_token]' value='" + $("#comment > #comment__token").val() + "'>" +
                        "<input type='hidden' id='parentCommentId' name='parentCommentId' value='" + commentId + "' />" +
                    "</div>" +
                    "<button class='btn btn-primary mb-3'>Сохранить</button>" +
                "</form>"
            );
        }

        $("form[id=panel-" + commentId +"]").slideToggle("slow");
        $(this).toggleClass("active");
    });
});