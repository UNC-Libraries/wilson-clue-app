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
    let tOffset = $(dnaId).offset().top;
    $('html,body').animate({scrollTop:tOffset-20},'fast');
  },

  generateGoogleDrivePermalink: function(link){
    let src = link;
    if(link.indexOf('drive.google.com') !== -1){
      if(link.indexOf('view?') !== -1){
        let url_parts = link.split('/');
        let file_id = url_parts[url_parts.length - 2];
        src = 'https://drive.google.com/uc?id=' + file_id
      } else {
        src = link.replace('\/open\?','/uc?')
      }
    }
    return src;
  },

  getGlobalAlert: function() {
    let url = $('#alertModal').data('url');
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
    let url = $('#pageAlert').data('check-alert-url');
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
    let text = $('input[name="'+ which +'"]:checked').data('full-name');
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
          let target = $(this).data('target');
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
        let message = $(this).data('message');
        return confirm(message);
      });
  },

  initCaseFileForm: function(){
      $('.load-case-file-form').on('click',function(e){
          e.preventDefault();
          let url = $(this).data('url');
          let button = $(this);

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
      $('.datetime-picker').each(function(i) {
          let date = $(this).data('date-default-date');
          // We use FA 4 and default icons are FA 5, so set the FA 4 equivalents below
          $('.datetime-picker').tempusDominus({
              defaultDate: new Date(date),
              display: {
                  icons: {
                      time: 'fa fa-clock-o',
                      date: 'fa fa-calendar',
                      up: 'fa fa-arrow-up',
                      down: 'fa fa-arrow-down',
                      previous: 'fa fa-chevron-left',
                      next: 'fa fa-chevron-right',
                      today: 'fa fa-calendar-check-0',
                      clear: 'fa fa-trash',
                      close: 'fa fa-close'
                  }
              }
          });
      });
  },

  initDnaForm: function(){
    $('#dnaForm').submit(function(e) {
      e.preventDefault();
      let target = '#' + $(this).prop('id');
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
      let target = '#' + $(this).prop('id');
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
      let src = clue.generateGoogleDrivePermalink($('input[name="src"]').val());
      $('input[name="src"]').val(src);
      $($(this).data('target')).prop('src',src).removeClass('d-none');
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
    let list = document.getElementById('minigameContainer');

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
          let target = "#minigameImageModal-" + $(this).data('id');
          $(target).modal('show');
      });
    }

    // Ajax Form
    $('#minigameForm').submit(function(e){
      e.preventDefault();
      let target = '#' + $(this).prop('id');

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
      let target = '#' + $(this).prop('id');
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
      let list = document.getElementById(listId),
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
              $('#questionImageRow, #enableQuestionText').removeClass('d-none');
          }
          else {
              $('#questionImageRow, #enableQuestionText').addClass('d-none');
          }
      }).trigger("change");
    
      $('body').on('click','.remove-answer',function(e){
        let url = $(this).data('url');
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
          let button = $(this);

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
          let type = $(this).val();
          $('.quest-type').css({'display':'none'});
          $('.'+type+'-setup').css({'display':'block'});
      }).change();
  },

  questionCorrect: function(target){
    let element = $(target).closest('.question-div');
    console.log(element);
    console.log(target);
    if(element.length){
      element.addClass('correct').removeClass('submitting check-status').html('<div class="row"><div class="col-xs-12 text-center"><strong>Complete</strong></div></div>');
      clue.getPageAlert();
      let tOffset = element.offset().top;
      $('html,body').animate({scrollTop:tOffset-20},'fast');
    }
  },

  questionIncorrect: function(target, message){
    message = (typeof message !== 'undefined') ?  message : 'Try Again!';
    let element = $(target).closest('.question-div');
    element.removeClass('submitting').addClass('incorrect');
    $(target+'-response').html(message);
    let tOffset = element.offset().top;
    $('html,body').animate({scrollTop:tOffset-20},'fast');
  },

  setEvidence: function(target){
    let element = $(target).closest('.question-div');
    element.removeClass('submitting');
    $(target+'-response').html('Evidence Set');
  },

  questionSubmit: function(target){
    $(target).closest('.question-div').addClass('submitting');
  },

  updateMinigameOrder: function(target) {
    let order = 1;
    let attempt = [];

    $(target).children('.minigame-image').each(function(k,v){
        $(v).children('span').html(order);
        attempt.push($(v).data('id'));
        order++;
    });

    $('input[name="attempt"]').val(attempt);
  },

  updateStash: function(listId, stash){
      let list = [];
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
          let t= $(this.hash);
          t = t.length&&t || $('[name='+this.hash.slice(1)+']');
          if(t.length){
              let tOffset=t.offset().top;
              $('html,body').animate({scrollTop:tOffset-20},'slow');
              e.preventDefault();
          }
      });

      $('.charnav').on('click',function(e){
          let t = $(this).data('main-target');
          let hast = $(this).attr('href');
          if(t.length){
              let tOffset=$('#'+t).offset().top;
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
      let swiper = new Swiper('.swiper-container', {
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
