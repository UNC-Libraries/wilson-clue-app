clue = {

  admin: function(){

    $('#archiveGame').submit(function(e){
      if($('select[name="winning_team"]').val() == 0){
        alert('You must select a winning team');
        e.preventDefault();
      }
    });

    $('#flash-message').delay(3000).slideUp(300);
  },

  dnaCorrect: function(target, dnaId, topOrBottom){
    $(target).closest('.question-div').removeClass('submitting').removeClass('incorrect');
    $(target+'-response').html('');
    $(dnaId).addClass(topOrBottom);
    var tOffset = $(dnaId).offset().top;
    $('html,body').animate({scrollTop:tOffset-20},'fast');
  },

  generateGoogleDrivePermalink: function(link){
    var src = link;
    if(link.indexOf('drive.google.com') !== -1){
      if(link.indexOf('view?') !== -1){
        var url_parts = link.split('/');
        var file_id = url_parts[url_parts.length - 2];
        src = 'https://drive.google.com/uc?id=' + file_id
      } else {
        src = link.replace('\/open\?','/uc?')
      }
    }
    return src;
  },

  getGlobalAlert: function() {
    var url = $('#alertModal').data('url');
    $.ajax({
      url: url,
      type: 'post',
      data: {_token: $('meta[name="csrf-token"]').attr('content') },
      dataType: 'json',
      success: function(data, status, xhr) {
        if(data.html){
          $('#alertModalBody').html(data.html);
          $('#alertModal').modal('show');
        }
      },
      error: function(xhr, status, error){
        console.log(xhr);
      }
    });
  },

  getPageAlert: function() {
    var url = $('#pageAlert').data('check-alert-url');
    $.ajax({
      url: url,
      type: 'post',
      dataType: 'json',
      data: {_token: $('meta[name="csrf-token"]').attr('content') },
      success: function(data, status, xhr){
        $('#pageAlert').html('<p>' + data.message + '</p>');
      },
      error: function(xhr, status, error){
        console.log(xhr);
      }
    })
  },

  getIndictmentConfirmationText: function(which){
    var text = $('input[name="'+ which +'"]:checked').data('full-name');
    return text ? text : '<span class="text-danger">YOU HAVE NO '+ which.toUpperCase() +' SELECTED!!!</span>';
  },

  initAjaxContentLoaders: function(){

    $('.refresh-content').click(function(){
      let url = $(this).data('url');
      let target = $(this).data('target');
      $(target).load(url);
    });
  },

  initAutoSubmit: function(){
      $('.auto-submit').change(function(){
          var target = $(this).data('target');
          if(target){
            $(target).submit();
          } else {
            $(this).closest('form').submit();
          }
      });
  },

  initClipboard: function(){
    new Clipboard('.clipboard-btn');

  },

  initConfirmSubmit: function(){
      $('.confirm-submit').submit(function() {
        var message = $(this).data('message');
        return confirm(message);
      });
  },

  initCaseFileForm: function(){
      $('.load-case-file-form').on('click',function(e){
          e.preventDefault();
          var url = $(this).data('url');
          var button = $(this);

          $.ajax({
              url: url,
              type: 'get',
              success: function(html){
                  button.before(html);
              }
          });
      });
  },

  initClickableRow: function(){
      $('body').on('click','.clickable-row',function(){
          window.document.location = $(this).data("href");
      });
  },

  initDateTimePicker: function(){
      $('.datetime-picker').each(function(i){
          var date = $(this).data('date-default-date');
          $('.datetime-picker').datetimepicker({
              defaultDate: date
          });
      });
  },

  initDnaForm: function(){
    $('#dnaForm').submit(function(e){
      e.preventDefault();
      var target = '#' + $(this).prop('id');
      clue.questionSubmit(target);

      $.ajax({
        url: $(this).prop('action'),
        type: $(this).prop('method'),
        data: $(this).serialize(),
        dataType: 'json',
        success: function(data, status, xhr){
          if(data.correct){
            clue.dnaCorrect(target, data.dnaId, data.topOrBottom);
          } else {
            clue.questionIncorrect(target, data.message);
          }
        },
        error: function(xhr, status, error){
          console.log(xhr);
        }
      });

    });
  },

  initEvidenceRoom: function(){
    $('#evidenceForm').submit(function(e){
      e.preventDefault();
      var target = '#' + $(this).prop('id');
      clue.questionSubmit(target);

      $.ajax({
        url: $(this).prop('action'),
        type: $(this).prop('method'),
        data: $(this).serialize(),
        dataType: 'json',
        success: function(data, status, xhr){
          clue.setEvidence(target);
        },
        error: function(xhr, status, error){
          console.log(xhr);
        }
      });

    });
  },

  initImagePreview: function() {
    $('button.preview-image').click(function(e){
      e.preventDefault();
      var src = clue.generateGoogleDrivePermalink($('input[name="src"]').val());
      $('input[name="src"]').val(src);
      $($(this).data('target')).prop('src',src).removeClass('hidden');
    });
  },

  initIndictmentForm: function(){
    $('#indictmentConfirmModal').on('show.bs.modal', function(e) {
      $('#suspectSelection').html(clue.getIndictmentConfirmationText('suspect'));
      $('#locationSelection').html(clue.getIndictmentConfirmationText('location'));
      $('#evidenceSelection').html(clue.getIndictmentConfirmationText('evidence'));
    });

    $('#indictmentSubmit').click(function(e){
      $('#indictmentForm').submit();
    })
  },

  initMinigame: function(){

    // Sortable
    var list = document.getElementById('minigameContainer');

    if(list){
      clue.updateMinigameOrder('#minigameContainer');

      Sortable.create(list,{
        draggable:'.draggable',
        sort:true,
        onUpdate: function() {
          clue.updateMinigameOrder('#minigameContainer');
        }
      });

      $('.minigame-image').click(function(){
          var target = "#minigameImageModal-" + $(this).data('id');
          $(target).modal('show');
      });
    }

    // Ajax Form
    $('#minigameForm').submit(function(e){
      e.preventDefault();
      var target = '#' + $(this).prop('id');

      clue.questionSubmit(target);

      $.ajax({
        url: $(this).prop('action'),
        type: $(this).prop('method'),
        data: $(this).serialize(),
        dataType: 'json',
        success: function(data, status, xhr){
          if(data.correct){
            clue.questionCorrect(target);
            $('#minigameContainer').html('');
          } else {
            clue.questionIncorrect(target);
          }
        },
        error: function(xhr, status, error){
          console.log(xhr);
        }
      });
    })
  },

  initQuestionForm: function(){
    $('.question-form').submit(function(e){
      e.preventDefault();
      var target = '#' + $(this).prop('id');
      clue.questionSubmit(target);

      $.ajax({
        url: $(this).prop('action'),
        type: $(this).prop('method'),
        data: $(this).serialize(),
        dataType: 'json',
        success: function(data, status, xhr){
          if(data.correct){
            clue.questionCorrect(target);
          } else {
            clue.questionIncorrect(target, data.message);
          }
        },
        error: function(xhr, status, error){
          console.log(xhr);
        }
      });

    });
  },

  initPostcard: function(){
      $('#postcard-flip').click(function(){
          $('.flip-container').toggleClass('flip');
      }).hide();
      $('.flip-container').mouseenter(function(){
          $('#postcard-flip').show();
      }).mouseleave(function(){
          $('#postcard-flip').hide();
      });

  },

  initQuestDragDrop: function(listId,availableId,stashInputName){
      var list = document.getElementById(listId),
        available = document.getElementById(availableId);

      if(list && available){
          Sortable.create(list,{
              group:'sortable',
              sort:true,
              draggable: '.media',
              onAdd: function () {
                  clue.updateStash('#'+listId,stashInputName);
              },
              onUpdate: function() {
                  clue.updateStash('#'+listId,stashInputName);
              }
          });

          Sortable.create(available,{
              group:'sortable',
              sort:false,
              draggable: '.media',
              onAdd: function () {
                  clue.updateStash('#'+listId,stashInputName);
              }
          });
      }
  },

  initQuestionFormControls: function(){

      $('#questionType input').change(function(){
          if($(this).prop("checked")) {
              $('#questionImageRow, #enableQuestionText').removeClass('hidden');
          }
          else {
              $('#questionImageRow, #enableQuestionText').addClass('hidden');
          }
      }).trigger("change");
    
      $('body').on('click','.remove-answer',function(e){
        var url = $(this).data('url');
        if(url.length){
          $.ajax({
            url: url,
            type: 'post',
            data: {_token: $('meta[name="csrf-token"]').attr('content'), '_method': 'DELETE'},
          });
        }

        $(this).closest('.answer-wrapper').remove();
      });

      $('#addNewAnswer').click(function(e){
          var button = $(this);

          $.ajax({
              url: $(this).data('url'),
              type: 'get',
              success: function(html){
                  button.parent().before(html);
              }
          });
      })
  },

  initSeenAlert: function() {
    $('#alertModal').on('hidden.bs.modal', function (e) {
      $.ajax({
        url: $('#alertModalBody > p').data('clear-alert'),
        type: 'post',
        data: {_token: $('meta[name="csrf-token"]').attr('content')},
        dataType: 'json'
      });
    });
  },

  initShowHideQuestTypeSetup: function(){
      $('#type').on('change',function(){
          var type = $(this).val();
          $('.quest-type').css({'display':'none'});
          $('.'+type+'-setup').css({'display':'block'});
      }).change();
  },

  questionCorrect: function(target){
    var element = $(target).closest('.question-div');
    console.log(element);
    console.log(target);
    if(element.length){
      element.addClass('correct').removeClass('submitting check-status').html('<div class="row"><div class="col-xs-12 text-center"><strong>Complete</strong></div></div>');
      clue.getPageAlert();
      var tOffset = element.offset().top;
      $('html,body').animate({scrollTop:tOffset-20},'fast');
    }
  },

  questionIncorrect: function(target, message){
    message = (typeof message !== 'undefined') ?  message : 'Try Again!';
    var element = $(target).closest('.question-div');
    element.removeClass('submitting').addClass('incorrect');
    $(target+'-response').html(message);
    var tOffset = element.offset().top;
    $('html,body').animate({scrollTop:tOffset-20},'fast');
  },

  setEvidence: function(target){
    var element = $(target).closest('.question-div');
    element.removeClass('submitting');
    $(target+'-response').html('Evidence Set');
  },

  questionSubmit: function(target){
    $(target).closest('.question-div').addClass('submitting');
  },

  updateMinigameOrder: function(target) {
    var order = 1;
    var attempt = [];

    $(target).children('.minigame-image').each(function(k,v){
        $(v).children('span').html(order);
        attempt.push($(v).data('id'));
        order++;
    });

    $('input[name="attempt"]').val(attempt);
  },

  updateStash: function(listId, stash){
      var list = [];
      $(listId + ' .media').each(function(){
          list.push($(this).data('id'));
      });
      if($('.drop-count')){
          $('.drop-count').html(list.length);
      }
      $('[name="'+stash+'"]').val(list.join(','));
  },



  web: function(){
      $('.scrollnav').on("click",function(e){
          var t= $(this.hash);
          var t=t.length&&t||$('[name='+this.hash.slice(1)+']');
          if(t.length){
              var tOffset=t.offset().top;
              $('html,body').animate({scrollTop:tOffset-20},'slow');
              e.preventDefault();
          }
      });

      $('.charnav').on('click',function(e){
          var t = $(this).data('main-target');
          var hast = $(this).attr('href');
          if(t.length){
              var tOffset=$('#'+t).offset().top;
              $('html,body').animate({scrollTop:tOffset-20},'slow');
              $('.char-panel').removeClass('show');
              $(hast+'-panel').addClass('show');
              e.preventDefault();
          }
      });

      $(document).scroll(function(){
          if($(this).scrollTop() > 30){
              $('#homepage-nav').addClass('scrolled');
          } else {
              $('#homepage-nav').removeClass('scrolled');
          }
      });
      var swiper = new Swiper('.swiper-container', {
        loop: true,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        pagination: {
          el: '.swiper-pagination',
        },
        autoplay: {
          delay: 2000,
          disableOnInteraction: false,
        }
      });
  },
}
