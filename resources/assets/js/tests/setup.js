import { beforeEach, afterEach, vi } from 'vitest';
import '../jquery_wrapper';

beforeEach(() => {
  // Prevent real network calls in unit tests that trigger clue AJAX helpers.
  if (globalThis.$ && typeof globalThis.$.ajax === 'function') {
    vi.spyOn(globalThis.$, 'ajax').mockImplementation((options = {}) => {
      if (typeof options.success === 'function') {
        options.success({}, 'success', { status: 200 });
      }

      return {
        abort: vi.fn(),
      };
    });
  }
});

afterEach(() => {
  vi.restoreAllMocks();
});

