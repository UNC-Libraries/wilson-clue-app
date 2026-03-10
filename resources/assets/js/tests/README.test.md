# JavaScript Tests

This project uses **Vitest** for unit testing JavaScript modules.

## Running Tests

### Run all tests
```bash
npm run test:run
```

### Watch mode
```bash
npm run test
```

### UI dashboard
```bash
npm run test:ui
```

## Test Files

- `resources/assets/js/tests/app.test.js` - Router and controller loader tests
- `resources/assets/js/tests/clue.test.js` - Utility/module behavior tests

## Coverage Summary

### `clue.test.js` (52 tests)
- Admin flow helpers (`admin`, confirmations, alert loaders)
- URL helper (`generateGoogleDrivePermalink`)
- UI state handlers (`questionCorrect`, `questionIncorrect`, `setEvidence`, `questionSubmit`, `dnaCorrect`)
- Stash/minigame helpers (`updateStash`, `updateMinigameOrder`)
- Initialization methods for UI/admin flows (auto submit, drag/drop, forms, previews, etc.)
- Export shape validation for all public `clue` functions

### `app.test.js` (34 tests)
- `CLUE_LOAD` export shape and handlers
- `ROUTER.exec` and `ROUTER.init` behavior (including edge cases)
- Integration checks for controller/action routing flow

## Writing New Tests

```javascript
describe('Component Name', () => {
  it('does something specific', () => {
    expect(result).toBe(expected);
  });
});
```

## Configuration

Vitest config lives in `vitest.config.js`:
- Environment: `happy-dom`
- Globals: enabled
- Coverage tracks JavaScript under `resources/assets/js/tests/`
