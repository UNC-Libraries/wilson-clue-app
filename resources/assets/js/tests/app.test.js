import { describe, it, expect, beforeEach, vi } from 'vitest';
import { CLUE_LOAD, ROUTER } from '../app.js';

describe('CLUE_LOAD', () => {
  describe('common initialization', () => {
    it('initializes common functionality', () => {
      const initAutoSubmit = vi.fn();
      const initClickableRow = vi.fn();
      const initConfirmSubmit = vi.fn();
      const initImagePreview = vi.fn();

      // Mock the clue module functions
      vi.stubGlobal('clue', {
        initAutoSubmit,
        initClickableRow,
        initConfirmSubmit,
        initImagePreview,
      });

      CLUE_LOAD.common.init();

      expect(CLUE_LOAD.common.init).toBeDefined();
      expect(typeof CLUE_LOAD.common.init).toBe('function');
    });

    it('exports common init function', () => {
      expect(CLUE_LOAD.common).toBeDefined();
      expect(CLUE_LOAD.common.init).toBeDefined();
      expect(typeof CLUE_LOAD.common.init).toBe('function');
    });
  });

  describe('web controller', () => {
    it('exports web init function', () => {
      expect(CLUE_LOAD.web).toBeDefined();
      expect(CLUE_LOAD.web.init).toBeDefined();
      expect(typeof CLUE_LOAD.web.init).toBe('function');
    });

    it('exports web enlistForm function', () => {
      expect(CLUE_LOAD.web.enlistForm).toBeDefined();
      expect(typeof CLUE_LOAD.web.enlistForm).toBe('function');
    });
  });

  describe('ui controller', () => {
    it('exports ui init function', () => {
      expect(CLUE_LOAD.ui).toBeDefined();
      expect(CLUE_LOAD.ui.init).toBeDefined();
      expect(typeof CLUE_LOAD.ui.init).toBe('function');
    });

    it('exports ui index function', () => {
      expect(CLUE_LOAD.ui.index).toBeDefined();
      expect(typeof CLUE_LOAD.ui.index).toBe('function');
    });

    it('exports ui dna function', () => {
      expect(CLUE_LOAD.ui.dna).toBeDefined();
      expect(typeof CLUE_LOAD.ui.dna).toBe('function');
    });

    it('exports ui quest function', () => {
      expect(CLUE_LOAD.ui.quest).toBeDefined();
      expect(typeof CLUE_LOAD.ui.quest).toBe('function');
    });

    it('exports ui indictment function', () => {
      expect(CLUE_LOAD.ui.indictment).toBeDefined();
      expect(typeof CLUE_LOAD.ui.indictment).toBe('function');
    });

    it('exports ui evidence function', () => {
      expect(CLUE_LOAD.ui.evidence).toBeDefined();
      expect(typeof CLUE_LOAD.ui.evidence).toBe('function');
    });
  });

  describe('game controller', () => {
    it('exports game init function', () => {
      expect(CLUE_LOAD.game).toBeDefined();
      expect(CLUE_LOAD.game.init).toBeDefined();
      expect(typeof CLUE_LOAD.game.init).toBe('function');
    });

    it('exports game create function', () => {
      expect(CLUE_LOAD.game.create).toBeDefined();
      expect(typeof CLUE_LOAD.game.create).toBe('function');
    });

    it('exports game show function', () => {
      expect(CLUE_LOAD.game.show).toBeDefined();
      expect(typeof CLUE_LOAD.game.show).toBe('function');
    });

    it('exports game edit function', () => {
      expect(CLUE_LOAD.game.edit).toBeDefined();
      expect(typeof CLUE_LOAD.game.edit).toBe('function');
    });

    it('exports game editEvidence function', () => {
      expect(CLUE_LOAD.game.editEvidence).toBeDefined();
      expect(typeof CLUE_LOAD.game.editEvidence).toBe('function');
    });

    it('exports game teams function', () => {
      expect(CLUE_LOAD.game.teams).toBeDefined();
      expect(typeof CLUE_LOAD.game.teams).toBe('function');
    });
  });

  describe('question controller', () => {
    it('exports question init function', () => {
      expect(CLUE_LOAD.question).toBeDefined();
      expect(CLUE_LOAD.question.init).toBeDefined();
      expect(typeof CLUE_LOAD.question.init).toBe('function');
    });
  });

  describe('quest controller', () => {
    it('exports quest init function', () => {
      expect(CLUE_LOAD.quest).toBeDefined();
      expect(CLUE_LOAD.quest.init).toBeDefined();
      expect(typeof CLUE_LOAD.quest.init).toBe('function');
    });

    it('exports quest edit function', () => {
      expect(CLUE_LOAD.quest.edit).toBeDefined();
      expect(typeof CLUE_LOAD.quest.edit).toBe('function');
    });
  });
});

describe('ROUTER', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('exec function', () => {
    it('calls common and specified controller actions', () => {
      const mockAction = vi.fn();
      const testController = 'testController';
      const testAction = 'testAction';

      // Create a temporary controller for testing
      const originalLoad = CLUE_LOAD[testController];
      CLUE_LOAD[testController] = {
        [testAction]: mockAction,
      };

      ROUTER.exec(testController, testAction);

      expect(mockAction).toHaveBeenCalled();

      // Cleanup
      if (originalLoad) {
        CLUE_LOAD[testController] = originalLoad;
      } else {
        delete CLUE_LOAD[testController];
      }
    });

    it('uses default init action when action type is undefined', () => {
      const mockInit = vi.fn();
      const testController = 'testController2';

      // Create a temporary controller for testing
      const originalLoad = CLUE_LOAD[testController];
      CLUE_LOAD[testController] = {
        init: mockInit,
      };

      ROUTER.exec(testController);

      expect(mockInit).toHaveBeenCalled();

      // Cleanup
      if (originalLoad) {
        CLUE_LOAD[testController] = originalLoad;
      } else {
        delete CLUE_LOAD[testController];
      }
    });

    it('does nothing when controller does not exist', () => {
      expect(() => ROUTER.exec('nonexistent', 'init')).not.toThrow();
    });

    it('does nothing when action does not exist on controller', () => {
      expect(() => ROUTER.exec('common', 'nonexistentAction')).not.toThrow();
    });

    it('handles empty controller string gracefully', () => {
      expect(() => ROUTER.exec('', 'init')).not.toThrow();
    });
  });

  describe('init function', () => {
    it('reads data-controller and data-action from body element', () => {
      const body = document.body;
      body.setAttribute('data-controller', 'ui');
      body.setAttribute('data-action', 'quest');

      const mockExec = vi.spyOn(ROUTER, 'exec');

      ROUTER.init();

      // Should call exec three times: common, controller, and controller+action
      expect(mockExec).toHaveBeenCalledWith('common');
      expect(mockExec).toHaveBeenCalledWith('ui');
      expect(mockExec).toHaveBeenCalledWith('ui', 'quest');

      mockExec.mockRestore();
    });

    it('handles missing data-controller attribute', () => {
      const body = document.body;
      body.removeAttribute('data-controller');
      body.setAttribute('data-action', 'test');

      const mockExec = vi.spyOn(ROUTER, 'exec');

      ROUTER.init();

      expect(mockExec).toHaveBeenCalledWith('common');
      expect(mockExec).toHaveBeenCalledWith(null);

      mockExec.mockRestore();
    });

    it('handles missing data-action attribute', () => {
      const body = document.body;
      body.setAttribute('data-controller', 'web');
      body.removeAttribute('data-action');

      const mockExec = vi.spyOn(ROUTER, 'exec');

      ROUTER.init();

      expect(mockExec).toHaveBeenCalledWith('common');
      expect(mockExec).toHaveBeenCalledWith('web');
      expect(mockExec).toHaveBeenCalledWith('web', null);

      mockExec.mockRestore();
    });

    it('works with valid controller and action attributes', () => {
      const body = document.body;
      body.setAttribute('data-controller', 'game');
      body.setAttribute('data-action', 'create');

      const mockExec = vi.spyOn(ROUTER, 'exec');

      ROUTER.init();

      expect(mockExec).toHaveBeenCalledWith('common');
      expect(mockExec).toHaveBeenCalledWith('game');
      expect(mockExec).toHaveBeenCalledWith('game', 'create');

      mockExec.mockRestore();
    });
  });

  describe('router integration', () => {
    it('routes to common init', () => {
      const body = document.body;
      body.setAttribute('data-controller', 'web');
      body.setAttribute('data-action', 'index');

      expect(() => ROUTER.init()).not.toThrow();
    });

    it('executes all route handlers without errors', () => {
      const controllers = ['web', 'ui', 'game', 'question', 'quest'];

      controllers.forEach((controller) => {
        const body = document.body;
        body.setAttribute('data-controller', controller);
        body.setAttribute('data-action', 'init');

        expect(() => ROUTER.init()).not.toThrow();
      });
    });
  });
});

describe('module exports', () => {
  it('exports CLUE_LOAD object', () => {
    expect(CLUE_LOAD).toBeDefined();
    expect(typeof CLUE_LOAD).toBe('object');
  });

  it('exports ROUTER object', () => {
    expect(ROUTER).toBeDefined();
    expect(typeof ROUTER).toBe('object');
  });

  it('ROUTER has exec method', () => {
    expect(ROUTER.exec).toBeDefined();
    expect(typeof ROUTER.exec).toBe('function');
  });

  it('ROUTER has init method', () => {
    expect(ROUTER.init).toBeDefined();
    expect(typeof ROUTER.init).toBe('function');
  });
});

