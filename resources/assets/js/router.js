// See: http://www.paulirish.com/2009/markup-based-unobtrusive-comprehensive-dom-ready-execution/

CLUE = {
  common: {
    init: function() {
      clue.initAutoSubmit();
      clue.initClickableRow();
      clue.initConfirmSubmit();
      clue.initImagePreview();
    }
  },

  /*****
   * Web
   */
  web: {
    init: function(){
      clue.web();
    },
    enlistForm: function(){
      clue.initPostcard();
    }
  },

  /*****
   * User Interface
   */
  ui: {
    init: function() {
      clue.initSeenAlert();
      clue.getGlobalAlert();
    },
    index: function() {
    },
    dna: function() {
      clue.initDnaForm();
    },
    quest: function() {
      clue.getPageAlert();
      clue.initQuestionForm();
      clue.initMinigame();
    },
    indictment: function() {
      clue.initIndictmentForm();
    },
    evidence: function() {
      clue.initEvidenceRoom();
    }
  },

  /*****
   * Admin
   */

  game: {
    init: function() {
    },
    create: function() {
      clue.initDateTimePicker();
    },
    show: function() {
      clue.initAjaxContentLoaders();
    },
    edit: function() {
      clue.initDateTimePicker();
    },
    editEvidence: function() {
      clue.initQuestDragDrop('evidenceList','availableEvidence','evidence_list');
      clue.initCaseFileForm();
    },
    teams: function() {
      clue.initClipboard();
    }
  },

  question: {
    init: function() {
      clue.initQuestionFormControls();
    },
  },

  quest: {
    init: function() {
    },
    edit: function() {
      clue.initQuestDragDrop('questionList','availableQuestions','question_list');
      clue.initQuestDragDrop('minigameImageList','availableMinigameImages','minigame_image_list');
      clue.initShowHideQuestTypeSetup();
    }
  }
};

ROUTER = {
  exec: function(controller, action) {
    var ns = CLUE,
      action = (action === undefined) ? "init" : action;
    if (controller !== "" && ns[controller] && typeof ns[controller][action] == "function") {
      ns[controller][action]();
    }
  },

  init: function() {
    var body = document.body,
      controller = body.getAttribute("data-controller"),
      action = body.getAttribute("data-action");
    ROUTER.exec("common");
    ROUTER.exec(controller);
    ROUTER.exec(controller, action);
  }
};
$(document).ready(ROUTER.init);