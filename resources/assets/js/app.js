// See: http://www.paulirish.com/2009/markup-based-unobtrusive-comprehensive-dom-ready-execution/
import './jquery_wrapper';
import { clue } from './clue';

export const CLUE_LOAD = {
  common: {
    init() {
      clue.initAutoSubmit();
      clue.initClickableRow();
      clue.initConfirmSubmit();
      clue.initImagePreview();
    },
  },

  /*****
   * Web
   */
  web: {
    init() {
      clue.web();
    },
    enlistForm() {
      clue.initPostcard();
    },
  },

  /*****
   * User Interface
   */
  ui: {
    init() {
      clue.initSeenAlert();
      clue.getGlobalAlert();
    },
    index() {},
    dna() {
      clue.initDnaForm();
    },
    quest() {
      clue.getPageAlert();
      clue.initQuestionForm();
      clue.initMinigame();
    },
    indictment() {
      clue.initIndictmentForm();
    },
    evidence() {
      clue.initEvidenceRoom();
    },
  },

  /*****
   * Admin
   */
  game: {
    init() {},
    create() {
      clue.initDateTimePicker();
    },
    show() {
      clue.initAjaxContentLoaders();
    },
    edit() {
      clue.initDateTimePicker();
    },
    editEvidence() {
      clue.initQuestDragDrop('evidenceList', 'availableEvidence', 'evidence_list');
      clue.initCaseFileForm();
    },
    teams() {
      clue.initClipboard();
    },
  },

  question: {
    init() {
      clue.initQuestionFormControls();
    },
  },

  quest: {
    init() {},
    edit() {
      clue.initQuestDragDrop('questionList', 'availableQuestions', 'question_list');
      clue.initQuestDragDrop('minigameImageList', 'availableMinigameImages', 'minigame_image_list');
      clue.initShowHideQuestTypeSetup();
    },
  },
};

export const ROUTER = {
  exec(controller, actionType = 'init') {
    if (controller !== '' && typeof CLUE_LOAD?.[controller]?.[actionType] === 'function') {
      CLUE_LOAD[controller][actionType]();
    }
  },

  init() {
    const controller = document.body.dataset.controller ?? null;
    const action = document.body.dataset.action ?? null;

    ROUTER.exec('common');
    ROUTER.exec(controller);
    ROUTER.exec(controller, action);
  },
};

$(document).ready(ROUTER.init);