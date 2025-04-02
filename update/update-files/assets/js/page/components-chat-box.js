"use strict";

var loaded_chat = [];
var msg_offset = '';
var msg_loaded = 0;
var chat_set_offset_id = false;
var target_height = '';
var new_msg_count = 0;
var new_msg_arrived = false;
var config = '';
var intervalChatHeart = '';
var window_is_focused = true;
var old_unread_msg = 0;

$(document).ready(function(){
  $.ajax({
    url : base_url + "chat/get_system_settings/",
    async: false,
    type: "POST",
    data: csrfName+"="+csrfHash,
    dataTpe:'json',
    success: function(result){ 
      var res = JSON.parse(result);
      
        csrfName = res['csrfName'];
        csrfHash = res['csrfHash'];
        
      $.each(res, function (key, val) {
        if(val.type == 'web_fcm_settings'){
          config = JSON.parse(val.data); 
           
        }
      });

        firebase.initializeApp(config);

        const messaging = firebase.messaging(); 

        messaging.requestPermission()
          .then(function () {
            
            // get the token in the form of promise
            return messaging.getToken() 
          })
          .then(function(token) {
              
            updateWebFCM(token)
          })
          .catch(function(err) {
              var NotAllowed = document.getElementById("noti_permission");
              NotAllowed.classList.remove("d-none");
              var NotAllowedhref = document.getElementById("noti_permission_href");
              NotAllowedhref.addEventListener("click", requestNotiPermi, false);
            console.log("Unable to get permission to notify.", err);
          });
          
        function requestNotiPermi(){
            messaging.requestPermission()
          .then(function () {
            // console.log("Notification permission granted.");
            // get the token in the form of promise
            return messaging.getToken() 
          })
          .then(function(token) {
            updateWebFCM(token)
          })
          .catch(function(err) {
              var NotAllowed = document.getElementById("noti_permission");
              NotAllowed.classList.remove("d-none");
              NotAllowed.innerHTML = 'Unable to get permission. Please Allow From Settings. <a href="https://www.digitalcitizen.life/how-allow-block-notifications-google-chrome" target="_blank"><b>View Documation</b></a>';

            console.log("Unable to get permission to notify.", err);
          });
        }
        var typing_timer = [];
        messaging.onMessage(function(payload) {
          
          var notification = JSON.parse(payload['data']['data']);

          if(notification['type'] == 'typing'){
            var from_id_fmc = notification['from_id'];
            var from_id_input = $("#opposite_user_id").val();
            if(from_id_fmc == from_id_input){
              $("#chat_typing").show().delay(6000).fadeOut();
            }else{
            
                if(notification['message_type'] == 'person'){
                    var i = $("li").find("[data-id='"+from_id_fmc+"'][data-type='person'] i");
                    i.removeClass('text-success fa-circle fas');
                    i.addClass('typing-loader');
                    if(typing_timer[from_id_fmc] != undefined)
                      window.clearTimeout(typing_timer[from_id_fmc]);
                    
                    typing_timer[from_id_fmc] = setTimeout(function () {
                      i.removeClass('typing-loader');
                      i.addClass('text-success fa-circle fas');
                    }, 4000);
                }
            }
          }else{
            $("#chat_typing").hide();
          }
          
          if(notification['type'] == 'msg_delete'){
            var msg_id = notification['body'];
            var oppo_id = notification['from_id'];
            var from_id_input = $("#opposite_user_id").val();
            var type = notification['message_type'];
            
            var chat_type = $("#chat_type").val();
            
            if(oppo_id == from_id_input && type == chat_type){
              $("[data-delete_msg_id='"+msg_id+"']").slideUp("normal", function() { $(this).remove(); } );
            }
            
            if(!! loaded_chat[type+'_'+oppo_id]){
              for (var i = 0; i < loaded_chat[type+'_'+oppo_id].length; i++){
                if (loaded_chat[type+'_'+oppo_id][i].id == msg_id) {
                    loaded_chat[type+'_'+oppo_id].splice(i, 1);
                }
              }
            }
            
          }
          
          if(notification['type'] == 'message'){

            ion.sound.play("intuition");
            
            var type = notification['message_type'];
            var from_id_fmc = notification['from_id'];
            var to_id_fcm = notification['to_id'];
            var new_msg = JSON.parse(notification['new_msg']);
            var msg_id = notification['msg_id'];

            if(notification['profile'] !== undefined && notification['profile'] !== null){
              var picture = '<figure class="avatar avatar-md"><img src="assets/profiles/'+notification['profile']+'" class="rounded-circle"></figure>';
            }else{
              var picture = '<figure class="avatar avatar-md" data-initial="'+notification['picture']+'"></figure>';
            }

            var user_name = notification['senders_name'];
            var from_id_input = $("#opposite_user_id").val();
            var chat_type = $("#chat_type").val();
            var message = notification['body'];
            var convert = new Markdown.getSanitizingConverter().makeHtml;
            var string = convert(message);
            var chat_content = string.replace(/<[\/]{0,1}(p)[^><]*>/ig,"");
            
            // single user msg
            if(notification['message_type'] == 'person'){
              var i = $("li").find("[data-id='"+from_id_fmc+"'][data-type='person']");
              new_msg_count = i.data("unread_msg");
              new_msg_count = new_msg_count+1;
              if(new_msg_count > 0){
                i.find(".badge-chat").remove();
                if(new_msg_count > 9){
                  i.append('<div class="badge-chat">9 +</div>');
                }else{
                  i.append('<div class="badge-chat">'+new_msg_count+'</div>');
                }
                i.data("unread_msg", new_msg_count);
              }else{
                i.append('<div class="badge-chat">'+new_msg_count+'</div>');
              }

              if(from_id_fmc == from_id_input && chat_type == type){
                
                $.chatCtrl('#mychatbox2', {
                  text: chat_content,
                  picture: picture,
                  user_name: user_name,
                  position: 'chat-left',
                  visiblity: 1,
                  media_files: (!! new_msg[0].media_files ? new_msg[0].media_files:''),
                  msg_id: msg_id,
                }, 'bottom');

                new_msg_arrived = true;
          
                // make sure loaded_chat var declared before adding a msg in var when first time msg received from fcm this var is not declared
                if(loaded_chat[type+'_'+from_id_fmc] != undefined){
                  var chat_length = loaded_chat[type+'_'+from_id_fmc].length;
                  loaded_chat[type+'_'+from_id_fmc].unshift( Object.assign({}, new_msg[0]) );
                  loaded_chat[type+'_'+from_id_fmc][0]['position'] = 'left';
                }
              }else{
                
                // make sure loaded_chat var declared before adding a msg in var when first time msg received from fcm this var is not declared
                if(loaded_chat[type+'_'+from_id_fmc] != undefined){
                  var chat_length = loaded_chat[type+'_'+from_id_fmc].length;
                  loaded_chat[type+'_'+from_id_fmc].unshift( Object.assign({}, new_msg[0]) );
                  loaded_chat[type+'_'+from_id_fmc][0]['position'] = 'left';
                  var i = $("li").find("[data-id='"+from_id_fmc+"'][data-type='person']");
                  i.addClass('new-msg-rcv');
                }
              }

            }else{

              // group user msg
            
              var i = $("li").find("[data-id='"+to_id_fcm+"'][data-type='group']");
              i.find(".badge-group-chat").remove();
              i.append('<div class="badge-chat badge-group-chat">New</div>');
              
              if(to_id_fcm == from_id_input && chat_type == type){
                $.chatCtrl('#mychatbox2', {
                  text: chat_content,
                  picture: picture,
                  user_name: user_name,
                  position: 'chat-left',
                  visiblity: 1,
                  media_files: (!! new_msg[0].media_files ? new_msg[0].media_files:''),
                  msg_id: msg_id,
                }, 'bottom');
                new_msg_arrived = true;
          
                // make sure loaded_chat var declared before adding a msg in var when first time msg received from fcm this var is not declared
                if(loaded_chat[type+'_'+to_id_fcm] != undefined){
                  var chat_length = loaded_chat[type+'_'+to_id_fcm].length;
                  loaded_chat[type+'_'+to_id_fcm].unshift( Object.assign({}, new_msg[0]) );
                  loaded_chat[type+'_'+to_id_fcm][0]['position'] = 'left';
                }
              }else{
                
                // make sure loaded_chat var declared before adding a msg in var when first time msg received from fcm this var is not declared
                if(loaded_chat[type+'_'+to_id_fcm] != undefined){
                  var chat_length = loaded_chat[type+'_'+to_id_fcm].length;
                  loaded_chat[type+'_'+to_id_fcm].unshift( Object.assign({}, new_msg[0]) );
                  loaded_chat[type+'_'+to_id_fcm][0]['position'] = 'left';
                 
                  var i = $("li").find("[data-id='"+to_id_fcm+"'][data-type='group']");
                  i.addClass('new-msg-rcv');
                }
              }

            }
            
          }

        });
      
    }
  });
});

ion.sound({
    sounds: [{name: "out_of_bounds"},{name: "intuition"}],
    path: "../assets/sounds/",
    preload: true,
    multiplay: true,
    volume: 0.4
});
            
$(document).ready(function(){
  getOnlineMemebers();
});

window.onblur= function() {
  window_is_focused = false;
};

window.onfocus= function () {
  window_is_focused = true;
  var oppo_user_id = $("#opposite_user_id").val();
  var type = $("#chat_type").val();
  if(loaded_chat[type+'_'+oppo_user_id] != undefined && !! loaded_chat[type+'_'+oppo_user_id]){
    newLoadChat(oppo_user_id,type);
  }
};

function getOnlineMemebers(){
  $.ajax({
    url : base_url + "chat/get_online_members",
    type: "POST",
    data: csrfName+"="+csrfHash,
    dataTpe:'json',
    success: function(result){  
      var data = JSON.parse(result);

    $.each(data.data.members, function (key, val) {

      var i = $("li").find("[data-id='"+val.id+"'][data-type='person'] i");
      if(val.is_online == 1){
        i.removeClass("far");
        i.addClass("fas text-success");
      }else{
        i.removeClass("fas text-success");
        i.addClass("far");
      } 
      i = $("li").find("[data-id='"+val.id+"'][data-type='person']");
      old_unread_msg = ( i.data("unread_msg") !== 0 )? i.data("unread_msg"):0;
      
      if(val.unread_msg > 0 && val.unread_msg > old_unread_msg ){
        if(window_is_focused == false){
          newLoadChat(val.id,'person');
        }
        i.find(".badge-chat").remove();
        if(val.unread_msg > 9){
          i.append('<div class="badge-chat">9 +</div>');
        }else{
          i.append('<div class="badge-chat">'+val.unread_msg+'</div>');
        }
        i.data("unread_msg", val.unread_msg);
      }else if(val.unread_msg == 0){
        i.find(".badge-chat").remove();
      }

    });

    $.each(data.data.groups, function (key, val) {
   
      if(val.is_read != 0){
        if(window_is_focused == false){
          newLoadChat(val.group_id,'group');
        }
        var i = $("li").find("[data-id='"+val.group_id+"'][data-type='group']");
        i.addClass("new-msg-rcv");
        i.find(".badge-group-chat").remove();
        i.append('<div class="badge-chat badge-group-chat">New</div>');
      }  
      

    });

    setTimeout(getOnlineMemebers, 10000);

    }                
  });
}
 
function makeMeOnline(){
  $.ajax({
    url : base_url + "chat/make_me_online",
    type: "POST",
    data: csrfName+"="+csrfHash,
    dataTpe:'json',
    success: function(result){  
  
    }                
  });
}

    
$(document).on('click','#chat-preview-btn',function(){
  if($("#chat-input-textarea-result").is(':visible')){
    $('#chat-input-textarea-result').addClass("d-none");
  }else{
    $('#chat-input-textarea-result').removeClass("d-none");
  }
});


$(document).on('keyup','#chat-input-textarea',function(){
  var char_length = $('#chat-input-textarea').val().length;
  var text = $(this).val();
  var convert = new Markdown.getSanitizingConverter().makeHtml;
  var string = convert(text);
  var chat_content = string.replace(/<[\/]{0,1}(p)[^><]*>/ig,"");
  $('#chat-input-textarea-result').html(chat_content);
  if(text != ''){
    $('#chat-preview-btn').removeClass("d-none");
    if(char_length % 9 == 0 || char_length == 1 ){
    
      var receiver_id = $("#opposite_user_id").val();
      var title = 'typing';
      var msg = 'typing';
      var type = 'typing';
      var msg_type = $("#chat_type").val();
      sendFCM(receiver_id, title, msg, type, msg_type);
    } 
  }else{
    $('#chat-preview-btn').removeClass("d-none");
  }
});
  
$(document).on('keyup','#in-chat-search',function(){
  $("#search-result").html('');
  
  var search = $("#in-chat-search").val();
  var from_id = $("#opposite_user_id").val();
  var type = $("#chat_type").val();
  if(!! search){
    $.ajax({
      url : base_url + "chat/load_chat",
      type: "POST",
      data: {from_id:from_id,type:type,search:search},
      dataTpe:'json',
      success: function(result){
        var chats = JSON.parse(result);
        var html = ''
        var media_files = '';
        if(chats['error'] != true){

          $.each(chats['msg'], function (key, val) {
    
            media_files = '';
            if(!! val.media_files){
            
                $.each(val.media_files, function (key, val) {
                  if(val['file_extension'] == '.jpg' || val['file_extension'] == '.png' || val['file_extension'] == '.jpeg'){
                    media_files += '<span class="chat-image-view" style="display: grid;"><a href="'+base_url+'assets/chats/'+val['file_name']+'" download="'+val['original_file_name']+'" class="download-btn-styling delete-msg fas fa-download"></a><img width="150px" src="'+base_url+'assets/chats/'+val['file_name']+'"></span>';
                  }else{
                    media_files += '<span class="chat-files-search" style="position: relative;"><a href="'+base_url+'assets/chats/'+val['file_name']+'" download="'+val['original_file_name']+'" class="download-btn-styling delete-msg fas fa-download"></a><div class="chat_media_img"><i class="fas fa-file-alt text-primary"></i></div><div class="chat_media_file">'+val['original_file_name']+'</div><div class="chat_media_size">'+val['file_size']+'</div></span>';
                  }
                });
            }
            html += '<div class="text-title ml-2 mb-2 text-muted">'+val.group_name+' - '+moment(val.date_created).utc().format('MMM Do, YYYY')+'</div><li class="media"><div class="chat-avtar-search">'+val.picture+'</div><div class="media-body"><div class="float-right text-primary">'+moment(val.date_created).utc().format('hh:mm A')+'</div><div class="media-title">'+val.senders_name+'</div><span class="text-small text-muted">'+val.message+' '+media_files+'</span></div></li>'
          });
        }else{
          html += '<li class="media"><div class="media-body"><div class="media-title">No Result Found</div><span class="text-small text-muted">Try different keywords</span></div></li>';
        }   
        
        $("#show-search-result").removeClass("d-none");
        $("#search-result").html(html);

      }           
    });
  }else{
    $("#show-search-result").addClass("d-none");
  }

});


$(document).on('mousemove','.main-wrapper',function(){
  $("#add-scroll-js").getNiceScroll().resize();
  $(".chat-content").getNiceScroll().resize();
});

$(document).on('click','#chat-scrn',function() {
  var data = $(this).data("value");
  // alert(data);
  if(data == 'max'){
    $('.main-footer').removeClass('chat-hide-show');
    $('.navbar-bg').removeClass('chat-hide-show');
    $('.navbar').removeClass('chat-hide-show');
    $('.main-sidebar').removeClass('chat-hide-show');
    $('.chat-full-screen').addClass('main-content').removeClass('chat-full-screen'); 
    $('.chat-max').addClass('chat-min').removeClass('chat-max');
    $('.fa-compress').addClass('fa-arrows-alt').removeClass('fa-compress');
    $(this).data("value", "min");
  }else{
    $('.main-footer').addClass('chat-hide-show');
    $('.navbar-bg').addClass('chat-hide-show');
    $('.navbar').addClass('chat-hide-show');
    $('.main-sidebar').addClass('chat-hide-show');
    $('.main-content').addClass('chat-full-screen').removeClass('main-content');
    $('.chat-min').addClass('chat-max').removeClass('chat-min');
    $('.fa-arrows-alt').addClass('fa-compress').removeClass('fa-arrows-alt');
    $(this).data("value", "max");
  }
});

$(document).on("dragenter","#chat-box-content",function(e) {
  showDropZone();
})

function closeDropZone(){
  $('#chat-box-content').show();
  $('#chat-dropbox').addClass("d-none");
  Dropzone.forElement("#myAlbum").removeAllFiles(true);
}

function showDropZone(){
  $('#chat-dropbox').removeClass("d-none");
  $('#chat-box-content').hide();
  
}

$(document).on('keyup','textarea',function (event) {
  if (event.keyCode == 13) {
    var content = this.value;  
    var caret = getCaret(this);          
    if(event.shiftKey){
        this.value = content.substring(0, caret - 1) + "\n" + content.substring(caret, content.length);
        var rowCount = $(this).attr("rows");
        if(rowCount != 5){
          $(this).attr({rows: parseInt(rowCount) + 1});
        }
        event.stopPropagation();
    } else {
        this.value = content.substring(0, caret - 1) + content.substring(caret, content.length);
        $('#chat-form2').submit();
    }
  }
});

function getCaret(el) { 
  if (el.selectionStart) { 
    return el.selectionStart; 
  } else if (document.selection) { 
    el.focus();
    var r = document.selection.createRange(); 
    if (r == null) { 
        return 0;
    }
    var re = el.createTextRange(), rc = re.duplicate();
    re.moveToBookmark(r.getBookmark());
    rc.setEndPoint('EndToStart', re);
    return rc.text.length;
  }  
  return 0; 
}

function getUserPicture(user_id){
  $.ajax({
    url : base_url+"chat/get_user_picture/"+user_id,
    type: "POST",
    data: csrfName+"="+csrfHash,
    success: function(result){
      var i = $("li").find("[data-id='"+user_id+"']");
      i.data("picture", result);
    }
  });
}

// sending msg
$(document).on('submit','#chat-form2',function(e) { 
  e.preventDefault();
  var drop_file_count= myDropzone.files.length;
  var to_id = $("#opposite_user_id").val();
  var type = $("#chat_type").val();
  var my_user_id = $("#my_user_id").val();
  var i = $("li").find("[data-id='"+my_user_id+"']");
  // var picture = $("#chat-avtar-main").html(); 
  var me = $(this);
  var message = $("#chat-input-textarea").val();
  var convert = new Markdown.getSanitizingConverter().makeHtml;
  var string = convert(message);
  var chat_content = string.replace(/<[\/]{0,1}(p)[^><]*>/ig,"");
  if(message.length > 0) {    
    var formData = new FormData(this);
    
    if(drop_file_count > 0){
      myDropzone.on('sending', function(file, xhr, formData){
        formData.append('opposite_user_id', jQuery('#opposite_user_id').val());
        formData.append('my_user_id', jQuery('#my_user_id').val());
        formData.append('chat_type', jQuery('#chat_type').val());
        formData.append('chat-input-textarea', jQuery('#chat-input-textarea').val());
      });

      myDropzone.processQueue();

      return false;

    }
    
    
    formData.append(csrfName, csrfHash);
    
    $.ajax({
    type:'POST',
    url: base_url + "chat/send_msg",
    data:formData,
    cache:false,
    contentType: false,
    processData: false,
    dataType: "json",
    success:function(result){

      var new_msg = result.new_msg;

      // make sure loaded_chat var declared before adding a msg in var when first time msg received from fcm this var is not declared
      if(loaded_chat[type+'_'+to_id] != undefined){
        var chat_length = loaded_chat[type+'_'+to_id].length;
        loaded_chat[type+'_'+to_id].unshift( Object.assign({}, new_msg[0]) );
      }
      var user_name = new_msg[0].senders_name;

      if(new_msg[0].profile !== undefined && new_msg[0].profile !== null){
        var picture = '<figure class="avatar avatar-md"><img src="assets/profiles/'+new_msg[0].profile+'" class="rounded-circle"></figure>';
      }else{
        var picture = '<figure class="avatar avatar-md" data-initial="'+new_msg[0].picture+'"></figure>';
      }

      $.chatCtrl('#mychatbox2', {
        text: chat_content,
        picture: picture,
        user_name: user_name,
        visiblity: 1,
        media_files: '',
        msg_id: (result.msg_id != undefined ? result.msg_id : ''),
      }, 'bottom');

      scrollToBottom();
      
    }

    });
    $('#chat-input-textarea-result').addClass("d-none");
    $("#chat-input-textarea").val('');
    $("#chat-box-content").find('.chat-text').addClass('selectable');

  }else{
    if(drop_file_count > 0){
      myDropzone.on('sending', function(file, xhr, formData){
        formData.append('opposite_user_id', jQuery('#opposite_user_id').val());
        formData.append('my_user_id', jQuery('#my_user_id').val());
        formData.append('chat_type', jQuery('#chat_type').val());
        formData.append('chat-input-textarea', jQuery('#chat-input-textarea').val());
      });

      myDropzone.processQueue();
    }
  }
  return false;
});


$(document).on('click', '#delete_group', function(e) {
  e.preventDefault();
  var group_id = $(this).data("id");
  swal({
    title: 'Are you sure want to delete group ?',
    text: 'All messages and related media & attachments will be deleted forever, you will not be able to recover it later!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
    })
    .then((willDelete) => {

        if (willDelete) {
          deleteGroup(group_id);
        } 
    });
});

function deleteGroup(group_id){
  $.ajax({
    url : base_url+"chat/delete_group/"+group_id,
    type: "POST",
    data: csrfName+"="+csrfHash,
    success: function(result){
      location.reload();
    }
  });
}

$(document).on('click', '.delete-msg-alert', function(e) {
  e.preventDefault();
  var msg_id = $(this).data("msg_id");
  swal({
    title: 'Are you sure?',
    text: 'Message Once deleted, you will not be able to recover that message!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
    })
    .then((willDelete) => {

        if (willDelete) {
          deleteMsg(msg_id);
        } 
    });
}); 

$(document).on('click', '#modal-search-msg', function(e) {
  $("#in-chat-search").val('');
  $("#show-search-result").addClass("d-none");
}); 

$(document).on('click', '#out-chat-search', function(e) {
  // $(".chat-person").each(function() {
    $('.chat-person').removeClass("active");
  // });
  $("#chat_type").val('');
  $("#chat_area_wait").removeClass("d-none");
  $("#chat_area").addClass("d-none");
  $("#in-chat-search").val('');
  $("#show-search-result").addClass("d-none");
}); 

  

function deleteMsg(msg_id){
  $.ajax({
    url : base_url+"chat/delete_msg/"+msg_id,
    type: "POST",
    data: csrfName+"="+csrfHash,
    success: function(result){
        var oppo_id = $("#opposite_user_id").val();
        var type = $("#chat_type").val();
        if(!! loaded_chat[type+'_'+oppo_id]){

          for (var i = 0; i < loaded_chat[type+'_'+oppo_id].length; i++){
            if (loaded_chat[type+'_'+oppo_id][i].id == msg_id) { 
                loaded_chat[type+'_'+oppo_id].splice(i, 1);
            }
          }

        }

        $("[data-delete_msg_id='"+msg_id+"']").slideUp("normal", function() { $(this).remove(); } );

        var receiver_id = $("#opposite_user_id").val();
        var title = 'msg deleted';
        var msg = msg_id;
        var type_1 = 'msg_delete';
        sendFCM(receiver_id, title, msg, type_1, type);
    }
  });
}

function updateWebFCM(token){
  var fcmtoken = token;
  $.ajax({
    url : base_url + "chat/update_web_fcm",
    type: "POST",
    data:  csrfName+"="+csrfHash+"&web_fcm="+fcmtoken,
    dataTpe:'json',
    success: function(result){
    }                
  });
}

$(document).ready(function(){
  $("#you_not_in_group_btn").click(function(){
    var group_id = $("#opposite_user_id").val();
    window.location = base_url+"chat/super_admin_make_group_admin/"+group_id;
  });
});

// switching users
$(document).on('click','.chat-person',function(){
  
  if($(this).data("not_in_group") == true){
    $("#chat_area_wait").addClass("d-none");
    $("#you_not_in_group").removeClass("d-none");
    $("#chat_area").addClass("d-none");
  }else{
    $("#chat_area_wait").addClass("d-none");
    $("#you_not_in_group").addClass("d-none");
    $("#chat_area").removeClass("d-none");
  }
  
  $(".go-to-bottom-btn").hide();
  var oppo_user_id = $(this).data("id");
  var type = $(this).data("type");

  // $(".chat-person").each(function() {
    $('.chat-person').removeClass("active");
  // });
  
  $("#opposite_user_id").val(oppo_user_id);
  $("#chat_type").val(type);
  $(this).addClass("active");
  $("#chat-box-content").html('');
  switchChat(oppo_user_id, type);
  if(!! loaded_chat[type+'_'+oppo_user_id]){
    var chat = loaded_chat[type+'_'+oppo_user_id]
    printChat(chat, oppo_user_id);
    
  }else{
    loadChat(oppo_user_id,type);
  }
  markMsgRead(oppo_user_id, type);

});

function markMsgRead(from_id, type){
  $.ajax({
    url : base_url + "chat/mark_msg_read",
    type: "POST",
    data: {from_id:from_id,type:type},
    dataTpe:'json',
    success: function(result){  
      var person = JSON.parse(result);
      if(person.error == false){
        var i = $("li").find("[data-id='"+from_id+"']");
        i.find(".badge-chat").remove();
        i.removeClass('new-msg-rcv');
        i.data("unread_msg", 0);
      }else{
        console.log('error');
      }
    }                
  });

}

// this function run when tab is not active and user recieved a msg
function newLoadChat(from_id,type,offset = '',limit = '',sort ='', order = ''){
  offset = (!! offset)?offset:0;
  limit = (!! limit)?limit:10;
  $.ajax({
    url : base_url + "chat/load_chat",
    type: "POST",
    data: {from_id:from_id,type:type,offset:offset,limit:limit,sort:sort,order:order},
    dataTpe:'json',
    success: function(result){
      var chats = JSON.parse(result);
      if(chats['error'] != true){
        if(!! loaded_chat[type+'_'+from_id] && loaded_chat[type+'_'+from_id] != undefined){

          var new_msg_arved = 0;
          for(var i = 0; i < chats['msg'].length; i++) {
            if(loaded_chat[type+'_'+from_id][0].id < chats['msg'][i].id){

              new_msg_arved++; 
              new_msg_arrived = true;

              loaded_chat[type+'_'+from_id].unshift( Object.assign({}, chats['msg'][i]) );
              loaded_chat[type+'_'+from_id]['msg_loaded'] = loaded_chat[type+'_'+from_id]['msg_loaded']+new_msg_arved;
              loaded_chat[type+'_'+from_id]['total_msg'] = loaded_chat[type+'_'+from_id]['total_msg']+new_msg_arved;
              
              var oppo_user_id = $("#opposite_user_id").val();
              var type1 = $("#chat_type").val();

              if(oppo_user_id == from_id && type1 == type){
                var convert = new Markdown.getSanitizingConverter().makeHtml;
                var string = convert(chats['msg'][i].text);
                var chat_content = string.replace(/<[\/]{0,1}(p)[^><]*>/ig,"");

                if(chats['msg'][i].profile !== undefined && chats['msg'][i].profile !== null){
                  var picture = '<figure class="avatar avatar-md"><img src="assets/profiles/'+chats['msg'][i].profile+'" class="rounded-circle"></figure>';
                }else{
                  var picture = '<figure class="avatar avatar-md" data-initial="'+chats['msg'][i].picture+'"></figure>';
                }

                $.chatCtrl('#mychatbox2', {
                  text: chat_content,
                  picture: picture,
                  user_name: chats['msg'][i].senders_name,
                  position: 'chat-'+chats['msg'][i].position,
                  visiblity: 1,
                  media_files: (!! chats['msg'][i].media_files ? chats['msg'][i].media_files : ''),
                  msg_id: chats['msg'][i].id
                }, 'bottom');
                
                $("#chat-box-content").find('.chat-text').addClass('selectable');
              }
              
            }
          }
          
        }
      }     
    }           
  });
}

function loadChat(from_id,type,offset = '',limit = '',sort ='', order = ''){
  offset = (!! offset)?offset:0;
  limit = (!! limit)?limit:10;
  $.ajax({
    url : base_url + "chat/load_chat",
    type: "POST",
    data: {from_id:from_id,type:type,offset:offset,limit:limit,sort:sort,order:order},
    dataTpe:'json',
    success: function(result){
      var chats = JSON.parse(result);
      if(chats['error'] != true){
        if(!! loaded_chat[type+'_'+from_id] && loaded_chat[type+'_'+from_id] != undefined){
          loaded_chat[type+'_'+from_id] = loaded_chat[type+'_'+from_id].concat(chats['msg']);
          loaded_chat[type+'_'+from_id]['msg_loaded'] = chats['msg'].length;
          loaded_chat[type+'_'+from_id]['total_msg'] = chats['total_msg'];
          loaded_chat[type+'_'+from_id]['offset'] = offset;
          loaded_chat[type+'_'+from_id]['limit'] = limit;
        }else{
          loaded_chat[type+'_'+from_id] = chats['msg'];
          loaded_chat[type+'_'+from_id]['msg_loaded'] = chats['msg'].length;
          loaded_chat[type+'_'+from_id]['total_msg'] = chats['total_msg'];
          loaded_chat[type+'_'+from_id]['offset'] = offset;
          loaded_chat[type+'_'+from_id]['limit'] = limit;
        }
        printChat(chats['msg'], from_id);
      }     
    }           
  });
}

function sendFCM(receiver_id, title, msg, type, message_type=''){
  $.ajax({
    url : base_url + "chat/send_fcm",
    type: "POST",
    data: {receiver_id:receiver_id,title:title,msg:msg,type:type,message_type:message_type},
    dataTpe:'json',
    success: function(result){ 
    }                
  });
}

function printChat(chats, id_of_user){
  if(0 < chats.length){
    var last_msg = chats.length - 1;
    var picture = ''
    var old_date  =  '';
    for(var i = 0; i < chats.length; i++) {
      var type = 'text';
      var convert = new Markdown.getSanitizingConverter().makeHtml;
      var string = convert(chats[i].text);
      var chat_content = string.replace(/<[\/]{0,1}(p)[^><]*>/ig,"");
      var is_divide = 'no';
      var new_date = moment(chats[i].date_created).format('YYYYMMDD');
 
      if(i == 0){
        old_date  =  moment(chats[i].date_created).format('YYYYMMDD');
        is_divide = 'yes';
      }else if(old_date != new_date){
        is_divide = 'yes';
        old_date = new_date;
      }else if(i == last_msg){
        is_divide = 'yes';
      }
      
      if(chats[i].profile !== undefined && chats[i].profile !== null){
        picture = '<figure class="avatar avatar-md"><img src="assets/profiles/'+chats[i].profile+'" class="rounded-circle"></figure>';
      }else{
        picture = '<figure class="avatar avatar-md" data-initial="'+chats[i].picture+'"></figure>';
      }

      if(chats[i].typing != undefined) type = 'typing';
      $.chatCtrl('#mychatbox2', {
        text: (chats[i].text != undefined ? chat_content : ''),
        picture: picture,
        user_name: chats[i].senders_name,
        position: 'chat-'+chats[i].position,
        time: moment(chats[i].date_created).format('hh:mm A'),
        chat_divider: is_divide,
        chat_divider_date: chats[i].date_created,
        visiblity: '',
        media_files: (!! chats[i].media_files ? chats[i].media_files : ''),
        msg_id: (chats[i].id != undefined ? chats[i].id : ''),
        type: type
      });

      $("#chat-box-content").find('.chat-text').addClass('selectable');
    }
    $( ".chat_loader" ).remove();
    $("#chat-box-content").prepend('<div class="chat_loader">Loading...</div>');
  }else{
    $("#chat-box-content").html('');
    $(".go-to-bottom-btn").hide();
  }

  var type = $("#chat_type").val();
  if(!! loaded_chat[type+'_'+id_of_user]['msg_offset']){
    
    $("#chat-box-content").scrollTop(loaded_chat[type+'_'+id_of_user]['msg_offset']);
  }else{
      if(target_height === ''){
        target_height = 640;
      }
      $("#mychatbox2").find('.chat-content').scrollTop(target_height, -1);
  }
  
}


function switchChat(from_id, type){
  
  $.ajax({
    url : base_url + "chat/switch_chat",
    type: "POST",
    data: {from_id:from_id,type:type},
    dataTpe:'json',
    success: function(result){  
      
      var person = JSON.parse(result);
      if(type == 'person'){
        $("#chat_title").text(person[0].first_name + " " + person[0].last_name);

        if(person[0].profile !== '' && person[0].profile !== null){
          var html = '<figure class="avatar avatar-md"><img src="assets/profiles/'+person[0].profile+'" class="rounded-circle"></figure>';

          $("#chat-avtar-main").html(html);
        }else{

          var html = '<figure class="avatar avatar-md" data-initial="'+person[0].picture+'"></figure>';
          $("#chat-avtar-main").html(html);
        }
        

        if(person[0].is_online == 1){
          $("#chat_online_status").addClass("text-success");
          $("#chat_online_status").html("<i class='fas fa-circle'></i> Online <span class='text-info' id='chat_typing' style='display: none;'> Typing...</span>");
        }else{
          $("#chat_online_status").removeClass("text-success");
          $("#chat_online_status").html("<i class='far fa-circle'></i> Offline");
        }

      }else{

        if(person[0].is_admin == true){
          var is_admin = "<a href='#' data-id='"+person[0].id+"' id='modal-edit-group-call'> Edit</a>";
        }else{
          var is_admin = "<a href='#' data-id='"+person[0].id+"' id='modal-info-group-call'> Info</a>";
        }

        $("#chat_title").html(person[0].title +' '+is_admin);
        var html = '<figure class="avatar avatar-md" data-initial="'+person[0].picture+'"></figure>';
        $("#chat-avtar-main").html(html);
        $("#chat_online_status").html('');
      }
       
    }                
  });

}

function onSrollTopLoadChat(){
    if($("#chat-box-content").scrollTop() == 0){
        var oppo_user_id = $("#opposite_user_id").val();
        var type = $("#chat_type").val();
        if(loaded_chat[type+'_'+oppo_user_id] !== undefined){
            var offset = loaded_chat[type+'_'+oppo_user_id]['offset'];
            var limit = loaded_chat[type+'_'+oppo_user_id]['limit'];
            offset = offset+limit;
            msg_loaded = msg_loaded + loaded_chat[type+'_'+oppo_user_id]['msg_loaded'];
            if(msg_loaded < loaded_chat[type+'_'+oppo_user_id]['total_msg']){
              target_height = 640;
              $(".chat_loader").show();
              loadChat(oppo_user_id,type,offset,limit);
            }else{
              target_height = 0;
            }
            
        }else{
            target_height = 0;
        }
        $(".go-to-bottom-btn").show();

    }
}

function scrollToBottom(){
  var target_height_send = 0;
  var target_height_send_divider = 0;
  $('.chat-content .chat-item').each(function() {
    target_height_send += $(this).outerHeight();
  });
  $('.chat-content .chat_divider').each(function() {
    target_height_send_divider += $(this).outerHeight();
  });
  target_height_send = target_height_send+target_height_send_divider+1000;
  $("#chat-box-content").scrollTop(target_height_send);
  $(".go-to-bottom-btn").hide();
}

function checkInView(elem,partial)
{
  var container = $("#chat-box-content");
  var contHeight = container.height();
  var contTop = container.scrollTop();
  var contBottom = contTop + contHeight ;

  var elemTop = $(elem).offset().top - container.offset().top;
  var elemBottom = elemTop + $(elem).height();

  var isTotal = (elemTop >= 0 && elemBottom <=contHeight);
  var isPart = ((elemTop < 0 && elemBottom > 0 ) || (elemTop > 0 && elemTop <= container.height())) && partial ;

  return  isTotal  || isPart ;
}

$('#chat-box-content').scroll(function () {
    // console.log('called #chat-box-content');
  $("#chat-box-content").find('.show').removeClass('show');
  msg_offset = $("#chat-box-content").scrollTop();
  var id_of_user = $("#opposite_user_id").val();
  var type = $("#chat_type").val();
  if(loaded_chat[type+'_'+id_of_user] !== undefined){
    loaded_chat[type+'_'+id_of_user]['msg_offset'] = msg_offset;
  }
  if(new_msg_arrived){
    var checkinview = checkInView($(".visiblity_msg"),true);
    
    if(checkinview == true){
      markMsgRead(id_of_user, type);
      new_msg_arrived =  false;
    }
  }

  var chat_div_height = 0;
  $('.chat-content .chat-item').each(function() {
    chat_div_height += $(this).outerHeight();
  });
  var check_msg_offset = $("#chat-box-content").scrollTop();
  check_msg_offset = check_msg_offset + 400;

  if(chat_div_height > check_msg_offset){
    $(".go-to-bottom-btn").show();
  }else{
    $(".go-to-bottom-btn").hide();
  }
  onSrollTopLoadChat();
    
});


$(document).on('click','.go-to-bottom-btn',function() {
  scrollToBottom();
  $(".go-to-bottom-btn").hide();
});

$(document).on("click","#modal-edit-group-call", function() {

  var id = $(this).data("id");
  $.ajax({
    type: "POST",
    url: base_url + "chat/get_group_members", 
    data: {group_id:id},
    dataType: "json",
    success: function(result) 
    {
      var title = result['data'][0].title;
      var description = result['data'][0].description;
      var update_id = result['data'][0].group_id;
      
      var user_ids = [];
      var admin_ids = [];
      $.each(result['data'], function (key, val) {
        user_ids[key] = val.user_id;
        if(val.is_admin == 1){
          admin_ids[key] = val.user_id;
        }
      });
      
      $('#update_id').val(update_id);
      $('#delete_group').attr("data-id",update_id);
      $('#update_title').val(title);
      $('#update_description').val(description);
      $('#update_users').val(user_ids);
      $('#update_users').trigger('change');

      $('#update_admins').val(admin_ids);
      $('#update_admins').trigger('change');

      $("#modal-edit-group").trigger("click");
    }        
  });
  
});

$(document).on("click","#modal-info-group-call", function() {

  var id = $(this).data("id");
  $.ajax({
    type: "POST",
    url: base_url + "chat/get_group_members", 
    data: {group_id:id},
    dataType: "json",
    success: function(result) 
    {
      var title = result['data'][0].title;
      var description = result['data'][0].description;
      var update_id = result['data'][0].group_id;
      
      var user_ids = [];
      var admin_ids = [];
      
      $.each(result['data'], function (key, val) {
        user_ids[key] = val.user_id;
        if(val.is_admin == 1){
          admin_ids[key] = val.user_id;
        }
      });

      $('#update_id_info').val(update_id);
      $('#update_title_info').val(title);
      $('#update_description_info').val(description);
      $('#update_users_info').val(user_ids);
      $('#update_users_info').trigger('change');

      $('#update_admins_info').val(admin_ids);
      $('#update_admins_info').trigger('change');

      $("#modal-info-group").trigger("click");
    }        
  });
  
});

Dropzone.autoDiscover = false;
var myDropzone = new Dropzone("#myAlbum", {
  url: "chat/send_msg",
  autoProcessQueue:false,
  dictDefaultMessage:dictDefaultMessage,
  addRemoveLinks: true,
  parallelUploads:10,
  uploadMultiple:true,
  maxFiles: 3,
  dictMaxFilesExceeded: 'Only 3 file are allow at once',
  dictRemoveFile:'x',

});

myDropzone.on("addedfile", function(file) {
  var i=0;
  if(this.files.length) {
  var _i, _len;
  for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) // -1 to exclude current file
  {
    if(this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString())
    {
      this.removeFile(file);
    }else if(this.files[4]!=null){
      this.removeFile(file);

    }
    i++;
  }
  }
});

myDropzone.on('successmultiple', function(file, response) {

var data = JSON.parse(response);
var new_msg = data.new_msg;
var to_id = $("#opposite_user_id").val();
var message = $("#chat-input-textarea").val();

if(new_msg[0].profile !== undefined && new_msg[0].profile !== null){
  var picture = '<figure class="avatar avatar-md"><img src="assets/profiles/'+new_msg[0].profiles+'" class="rounded-circle"></figure>';
}else{
  var picture = '<figure class="avatar avatar-md" data-initial="'+new_msg[0].picture+'"></figure>';
}

$.chatCtrl('#mychatbox2', {
  text: new_msg[0].message,
  picture: picture,
  visiblity: '',
  media_files: (!! new_msg[0].media_files )?new_msg[0].media_files:'',
  msg_id: (data.msg_id != undefined ? data.msg_id : ''),
}, 'bottom');

$('#chat-input-textarea-result').addClass("d-none");
$("#chat-input-textarea").val('');
$("#chat-box-content").find('.chat-text').addClass('selectable');

// make sure loaded_chat var declared before adding a msg in var when first time msg received from fcm this var is not declared
var type = $("#chat_type").val();
if(loaded_chat[type+'_'+to_id] != undefined){  
  var chat_length = loaded_chat[type+'_'+to_id].length;
  loaded_chat[type+'_'+to_id].unshift( Object.assign({}, new_msg[0]) );
}
closeDropZone();
scrollToBottom(); 

});

