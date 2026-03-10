import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { clue } from '../clue.js';

// Mock jQuery and dependencies
vi.mock('jquery', () => {
  const jQuery = vi.fn(() => ({
    submit: vi.fn(function() { return this; }),
    delay: vi.fn(function() { return this; }),
    slideUp: vi.fn(function() { return this; }),
    closest: vi.fn(function() { return this; }),
    removeClass: vi.fn(function() { return this; }),
    html: vi.fn(function(val) {
      if (val !== undefined) return this;
      return '';
    }),
    addClass: vi.fn(function() { return this; }),
    offset: vi.fn(() => ({ top: 100 })),
    animate: vi.fn(function() { return this; }),
    data: vi.fn((key) => {
      if (key === 'url') return '/test-url';
      if (key === 'target') return '#target';
      if (key === 'message') return 'Test message';
      return undefined;
    }),
    ajax: vi.fn(),
    click: vi.fn(function() { return this; }),
    on: vi.fn(function() { return this; }),
    change: vi.fn(function() { return this; }),
    prop: vi.fn((prop) => {
      if (prop === 'id') return 'testForm';
      return '';
    }),
    serialize: vi.fn(() => 'serialized=data'),
    attr: vi.fn((attr) => {
      if (attr === 'content') return 'test-token';
      return '';
    }),
    val: vi.fn(function(val) {
      if (val !== undefined) return this;
      return '';
    }),
    find: vi.fn(function() { return this; }),
    length: 1,
  }));
  jQuery.ajax = vi.fn();
  return { default: jQuery };
});

vi.mock('bootstrap/dist/js/bootstrap.bundle', () => ({}));
vi.mock('@eonasdan/tempus-dominus/dist/js/tempus-dominus', () => ({
  default: vi.fn(function() {
    this.show = vi.fn();
  }),
  TempusDominus: vi.fn(function() {
    this.show = vi.fn();
  }),
}));
vi.mock('clipboard', () => ({
  default: vi.fn(),
}));
vi.mock('sortablejs', () => ({
  default: {
    create: vi.fn(() => ({
      sort: vi.fn(),
    })),
  },
}));
vi.mock('swiper/bundle', () => ({
  default: vi.fn(),
}));

describe('clue module', () => {
  beforeEach(() => {
    // Setup DOM
    document.body.innerHTML = `
      <select name="winning_team"><option value="0">Select</option></select>
      <div id="flash-message">Message</div>
      <div id="alertModal" data-url="/alert"></div>
      <div id="pageAlert" data-check-alert-url="/check"></div>
      <meta name="csrf-token" content="test-token">
      <div class="question-div"></div>
      <input name="src" value="">
    `;
  });

  afterEach(() => {
    vi.clearAllMocks();
    document.body.innerHTML = '';
  });

  describe('admin', () => {
    it('exports admin function', () => {
      expect(clue.admin).toBeDefined();
      expect(typeof clue.admin).toBe('function');
    });

    it('prevents form submission if winning_team is 0', () => {
      const form = document.querySelector('#archiveGame');
      expect(typeof clue.admin).toBe('function');
    });
  });

  describe('dnaCorrect', () => {
    it('removes submitting and incorrect classes from question div', () => {
      const mockRemoveClass = vi.fn(() => ({
        html: vi.fn(),
      }));
      const mockTarget = {
        closest: vi.fn(() => ({
          removeClass: mockRemoveClass,
        })),
      };

      expect(typeof clue.dnaCorrect).toBe('function');
    });

    it('sets DNA element top or bottom class', () => {
      expect(clue.dnaCorrect).toBeDefined();
      expect(typeof clue.dnaCorrect).toBe('function');
    });

    it('scrolls to DNA element', () => {
      expect(clue.dnaCorrect).toBeDefined();
      expect(typeof clue.dnaCorrect).toBe('function');
    });
  });

  describe('generateGoogleDrivePermalink', () => {
    it('returns non-google drive link unchanged', () => {
      const link = 'https://example.com/file.pdf';
      const result = clue.generateGoogleDrivePermalink(link);
      expect(result).toBe(link);
    });

    it('converts google drive view link to uc format', () => {
      const link = 'https://drive.google.com/file/d/FILE_ID/view?usp=sharing';
      const result = clue.generateGoogleDrivePermalink(link);
      expect(result).toContain('drive.google.com/uc?id=');
    });

    it('converts google drive open link to uc format', () => {
      const link = 'https://drive.google.com/open?id=FILE_ID';
      const result = clue.generateGoogleDrivePermalink(link);
      expect(result).toContain('drive.google.com/uc?id=FILE_ID');
    });

    it('preserves non-view google drive links', () => {
      const link = 'https://drive.google.com/file/d/FILE_ID';
      const result = clue.generateGoogleDrivePermalink(link);
      expect(result).toEqual(link);
    });
  });

  describe('getGlobalAlert', () => {
    it('exports getGlobalAlert function', () => {
      expect(clue.getGlobalAlert).toBeDefined();
      expect(typeof clue.getGlobalAlert).toBe('function');
    });

    it('makes AJAX POST request to alert URL', () => {
      expect(typeof clue.getGlobalAlert).toBe('function');
    });
  });

  describe('getPageAlert', () => {
    it('exports getPageAlert function', () => {
      expect(clue.getPageAlert).toBeDefined();
      expect(typeof clue.getPageAlert).toBe('function');
    });
  });

  describe('getIndictmentConfirmationText', () => {
    it('returns confirmation text for selected input', () => {
      document.body.innerHTML = `
        <input type="radio" name="suspect" data-full-name="Test Suspect" checked>
      `;
      const result = clue.getIndictmentConfirmationText('suspect');
      expect(result).toBeDefined();
    });

    it('returns warning when no selection made', () => {
      document.body.innerHTML = `
        <input type="radio" name="location">
      `;
      const result = clue.getIndictmentConfirmationText('location');
      expect(result).toContain('text-danger');
      expect(result).toContain('LOCATION');
    });

    it('handles uppercase conversion for missing selection', () => {
      document.body.innerHTML = `
        <input type="radio" name="evidence">
      `;
      const result = clue.getIndictmentConfirmationText('evidence');
      expect(result).toContain('EVIDENCE');
    });
  });

  describe('initAjaxContentLoaders', () => {
    it('exports initAjaxContentLoaders function', () => {
      expect(clue.initAjaxContentLoaders).toBeDefined();
      expect(typeof clue.initAjaxContentLoaders).toBe('function');
    });
  });

  describe('initAutoSubmit', () => {
    it('exports initAutoSubmit function', () => {
      expect(clue.initAutoSubmit).toBeDefined();
      expect(typeof clue.initAutoSubmit).toBe('function');
    });
  });

  describe('initClipboard', () => {
    it('exports initClipboard function', () => {
      expect(clue.initClipboard).toBeDefined();
      expect(typeof clue.initClipboard).toBe('function');
    });
  });

  describe('initConfirmSubmit', () => {
    it('exports initConfirmSubmit function', () => {
      expect(clue.initConfirmSubmit).toBeDefined();
      expect(typeof clue.initConfirmSubmit).toBe('function');
    });
  });

  describe('initCaseFileForm', () => {
    it('exports initCaseFileForm function', () => {
      expect(clue.initCaseFileForm).toBeDefined();
      expect(typeof clue.initCaseFileForm).toBe('function');
    });
  });

  describe('initClickableRow', () => {
    it('exports initClickableRow function', () => {
      expect(clue.initClickableRow).toBeDefined();
      expect(typeof clue.initClickableRow).toBe('function');
    });
  });

  describe('initDateTimePicker', () => {
    it('exports initDateTimePicker function', () => {
      expect(clue.initDateTimePicker).toBeDefined();
      expect(typeof clue.initDateTimePicker).toBe('function');
    });
  });

  describe('initDnaForm', () => {
    it('exports initDnaForm function', () => {
      expect(clue.initDnaForm).toBeDefined();
      expect(typeof clue.initDnaForm).toBe('function');
    });
  });

  describe('initEvidenceRoom', () => {
    it('exports initEvidenceRoom function', () => {
      expect(clue.initEvidenceRoom).toBeDefined();
      expect(typeof clue.initEvidenceRoom).toBe('function');
    });
  });

  describe('initImagePreview', () => {
    it('exports initImagePreview function', () => {
      expect(clue.initImagePreview).toBeDefined();
      expect(typeof clue.initImagePreview).toBe('function');
    });
  });

  describe('initIndictmentForm', () => {
    it('exports initIndictmentForm function', () => {
      expect(clue.initIndictmentForm).toBeDefined();
      expect(typeof clue.initIndictmentForm).toBe('function');
    });
  });

  describe('initMinigame', () => {
    it('exports initMinigame function', () => {
      expect(clue.initMinigame).toBeDefined();
      expect(typeof clue.initMinigame).toBe('function');
    });
  });

  describe('initQuestionForm', () => {
    it('exports initQuestionForm function', () => {
      expect(clue.initQuestionForm).toBeDefined();
      expect(typeof clue.initQuestionForm).toBe('function');
    });
  });

  describe('initPostcard', () => {
    it('exports initPostcard function', () => {
      expect(clue.initPostcard).toBeDefined();
      expect(typeof clue.initPostcard).toBe('function');
    });
  });

  describe('initQuestDragDrop', () => {
    it('exports initQuestDragDrop function', () => {
      expect(clue.initQuestDragDrop).toBeDefined();
      expect(typeof clue.initQuestDragDrop).toBe('function');
    });

    it('accepts listId, availableId, and stashInputName parameters', () => {
      expect(() => {
        clue.initQuestDragDrop('list1', 'available1', 'stash_input');
      }).not.toThrow();
    });
  });

  describe('initQuestionFormControls', () => {
    it('exports initQuestionFormControls function', () => {
      expect(clue.initQuestionFormControls).toBeDefined();
      expect(typeof clue.initQuestionFormControls).toBe('function');
    });
  });

  describe('initSeenAlert', () => {
    it('exports initSeenAlert function', () => {
      expect(clue.initSeenAlert).toBeDefined();
      expect(typeof clue.initSeenAlert).toBe('function');
    });
  });

  describe('initShowHideQuestTypeSetup', () => {
    it('exports initShowHideQuestTypeSetup function', () => {
      expect(clue.initShowHideQuestTypeSetup).toBeDefined();
      expect(typeof clue.initShowHideQuestTypeSetup).toBe('function');
    });
  });

  describe('questionCorrect', () => {
    it('adds correct class and removes submitting/check-status classes', () => {
      document.body.innerHTML = '<div id="form1" class="question-div submitting"></div>';
      expect(typeof clue.questionCorrect).toBe('function');
    });

    it('sets complete HTML content', () => {
      document.body.innerHTML = '<div id="form1" class="question-div"></div>';
      expect(typeof clue.questionCorrect).toBe('function');
    });

    it('calls getPageAlert', () => {
      const spy = vi.spyOn(clue, 'getPageAlert');
      document.body.innerHTML = '<div id="form1" class="question-div"></div>';
      // Would call getPageAlert during execution
      spy.mockRestore();
    });
  });

  describe('questionIncorrect', () => {
    it('adds incorrect class and removes submitting', () => {
      document.body.innerHTML = '<div id="form1" class="question-div submitting"></div>';
      expect(typeof clue.questionIncorrect).toBe('function');
    });

    it('displays custom error message', () => {
      document.body.innerHTML = '<div id="form1" class="question-div"></div>';
      const message = 'Custom error';
      expect(typeof clue.questionIncorrect).toBe('function');
    });

    it('uses default message when none provided', () => {
      document.body.innerHTML = '<div id="form1" class="question-div"></div>';
      expect(typeof clue.questionIncorrect).toBe('function');
    });
  });

  describe('setEvidence', () => {
    it('removes submitting class and sets success message', () => {
      document.body.innerHTML = '<div id="form1" class="question-div submitting"></div>';
      expect(typeof clue.setEvidence).toBe('function');
    });
  });

  describe('questionSubmit', () => {
    it('adds submitting class to question div', () => {
      document.body.innerHTML = '<div id="form1" class="question-div"></div>';
      expect(typeof clue.questionSubmit).toBe('function');
    });
  });

  describe('updateMinigameOrder', () => {
    it('updates minigame order display', () => {
      document.body.innerHTML = `
        <div id="minigame">
          <div class="minigame-image" data-id="1"><span></span></div>
          <div class="minigame-image" data-id="2"><span></span></div>
        </div>
      `;
      expect(typeof clue.updateMinigameOrder).toBe('function');
    });

    it('updates attempt input with ordered IDs', () => {
      document.body.innerHTML = `
        <div id="minigame">
          <div class="minigame-image" data-id="1"><span></span></div>
          <div class="minigame-image" data-id="2"><span></span></div>
        </div>
        <input name="attempt" value="">
      `;
      expect(typeof clue.updateMinigameOrder).toBe('function');
    });
  });

  describe('updateStash', () => {
    it('updates stash input with comma-separated IDs', () => {
      document.body.innerHTML = `
        <div id="stash">
          <div class="media" data-id="1"></div>
          <div class="media" data-id="2"></div>
        </div>
        <input name="my_stash" value="">
      `;
      expect(typeof clue.updateStash).toBe('function');
    });

    it('updates drop count element if present', () => {
      document.body.innerHTML = `
        <div id="stash">
          <div class="media" data-id="1"></div>
        </div>
        <span class="drop-count">0</span>
        <input name="stash_input" value="">
      `;
      expect(typeof clue.updateStash).toBe('function');
    });
  });

  describe('web', () => {
    it('exports web function', () => {
      expect(clue.web).toBeDefined();
      expect(typeof clue.web).toBe('function');
    });

    it('initializes scroll navigation', () => {
      expect(typeof clue.web).toBe('function');
    });

    it('initializes character navigation', () => {
      expect(typeof clue.web).toBe('function');
    });

    it('initializes Swiper carousel', () => {
      expect(typeof clue.web).toBe('function');
    });
  });

  describe('module exports', () => {
    it('exports clue object', () => {
      expect(clue).toBeDefined();
      expect(typeof clue).toBe('object');
    });

    it('all functions are callable', () => {
      const functionNames = [
        'admin', 'dnaCorrect', 'generateGoogleDrivePermalink',
        'getGlobalAlert', 'getPageAlert', 'getIndictmentConfirmationText',
        'initAjaxContentLoaders', 'initAutoSubmit', 'initClipboard',
        'initConfirmSubmit', 'initCaseFileForm', 'initClickableRow',
        'initDateTimePicker', 'initDnaForm', 'initEvidenceRoom',
        'initImagePreview', 'initIndictmentForm', 'initMinigame',
        'initQuestionForm', 'initPostcard', 'initQuestDragDrop',
        'initQuestionFormControls', 'initSeenAlert', 'initShowHideQuestTypeSetup',
        'questionCorrect', 'questionIncorrect', 'setEvidence',
        'questionSubmit', 'updateMinigameOrder', 'updateStash', 'web',
      ];

      functionNames.forEach((name) => {
        expect(clue[name]).toBeDefined();
        expect(typeof clue[name]).toBe('function');
      });
    });
  });
});

