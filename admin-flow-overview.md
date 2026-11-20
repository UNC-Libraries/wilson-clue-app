# Administrator workflow overview

This is a high-level summary of how an administrator takes a Clue game from initial setup through successful completion. It intentionally omits code locations and implementation details.

## 1. Sign in

1. Open the administrator login page.
2. Sign in with administrator credentials.
3. On success, open the admin dashboard.
4. If authentication fails, correct the credentials and try again.

## 2. Create the game

1. Create a new game.
2. Provide:
   - Game name
   - Start date and time
   - End date and time
   - Maximum number of teams
   - Whether the game is limited to students
3. Save the game.
4. The system creates the initial set of suspect quests for the game.

If required values are missing or dates are invalid, correct the form before continuing.

## 3. Prepare the game content

Configure the game before registration opens:

- Locations and map information
- Suspects
- Questions and accepted answers
- Question images, where needed
- Minigame images and their chronological order
- Evidence items and images
- Evidence-room location
- Geographic-investigation location
- Case-file items and explanatory text
- The correct suspect, portal location, and evidence solution
- Quest types, locations, suspects, questions, and minigame content

Review the game warnings and resolve missing or conflicting configuration, especially:

- Missing solution values
- Missing evidence or investigation locations
- Duplicate suspect assignments
- Duplicate quest locations
- Quests without usable questions or minigame content

The system may display warnings without preventing activation, so administrators should treat unresolved warnings as blockers.

## 4. Activate the game and open registration

1. Activate the prepared game.
2. Open registration.
3. Confirm that only the intended game is active.
4. Confirm the registration deadline and game capacity.

Activating a new game deactivates another active game. If there is no active game or registration is closed, players cannot register normally.

## 5. Manage teams and players

Players can create their own teams and invite eligible teammates. Administrators can also add teams and players directly.

For each team:

1. Confirm the team name and members.
2. Verify player eligibility and student status where applicable.
3. Ensure the team reaches the required minimum of four players.
4. Keep teams within the maximum player limit.
5. Move qualifying teams from the waitlist into registered status.
6. Keep full teams registered and move incomplete teams back to the waitlist when necessary.
7. Resolve duplicate-player, prior-participation, or invalid-Onyen issues.

If all team capacity is used, additional qualifying teams remain waitlisted until a spot becomes available or an administrator promotes them.

## 6. Check in players

Before the game begins:

1. Open the game check-in screen.
2. Check players in by PID or by selecting the player.
3. Resolve unknown-player or duplicate-check-in messages.
4. Confirm that registered teams have the expected players checked in.

Check-in supports attendance tracking and progress messaging. It does not replace team registration or game configuration.

## 7. Operate the live game

When the game is in progress, administrators can:

- Monitor team and quest progress.
- View which parts of the game players are currently visiting.
- Publish alerts and instructions to players.
- Review team answers that require judgement.
- Mark eligible answers correct and mark reviewed incorrect answers as judged.
- Correct team membership or waitlist issues as they arise.

The game must be within its configured start and end times for normal player activity. An administrator can use the test-game facility before the event to test the player experience.

## 8. Handle quest and answer progress

Players complete question quests by answering enough questions correctly. They complete minigames by arranging images in the correct chronological order. Administrators review answers that need manual judgement.

If players submit incorrect answers, they can retry where the game rules allow. If a quest is missing content or has incorrect configuration, correct the game setup before continuing the event.

## 9. Review indictments

Players submit an indictment consisting of:

- The suspected ghost
- The portal location
- The evidence item

The submission is accepted only when all three values match the configured solution. Players can continue earning points after a correct indictment until the game ends.

Administrators should confirm that the solution was configured correctly before the game begins, because it controls whether player indictments are considered correct.

## 10. End the game and calculate scores

When the end time passes:

1. Confirm that the game has ended.
2. Review completed quests, correct answers, DNA discoveries where applicable, and indictments.
3. Run the score view.
4. Verify that correctly indicted teams rank ahead of incorrectly indicted teams.
5. Add legitimate bonus points when appropriate.
6. Recheck the resulting standings.

Scoring combines question progress, DNA progress where enabled, indictment correctness, timing bonuses, and any administrator-awarded bonus points.

## 11. Archive and publish the results

1. Select the winning team and enter any post-game information, acknowledgements, or accolades.
2. Deactivate the completed game.
3. Mark or preserve the game as archived.
4. Review the public archive page to confirm that teams, rankings, and results display correctly.

The public archive separates teams with correct indictments from those with incorrect indictments and orders them by score.

## Successful completion checklist

- [ ] Administrator authenticated.
- [ ] Game created with valid dates and capacity.
- [ ] Suspects, locations, quests, questions, answers, images, evidence, and case files configured.
- [ ] Solution configured and verified.
- [ ] All readiness warnings reviewed and resolved.
- [ ] Game activated and registration opened.
- [ ] Eligible teams registered with enough players.
- [ ] Players checked in.
- [ ] Live progress monitored and alerts sent as needed.
- [ ] Answers requiring judgement reviewed.
- [ ] Indictments submitted and verified.
- [ ] Game ended normally.
- [ ] Scores calculated and bonuses reviewed.
- [ ] Winning team recorded.
- [ ] Game deactivated and archived.
- [ ] Public results verified.

## Main alternate paths

| Situation | Administrator action |
|---|---|
| Invalid game setup | Correct the form or configuration before activation |
| Missing solution or location | Complete the game configuration and recheck warnings |
| Registration closed | Reopen registration if appropriate, or direct players to the next game |
| Game at capacity | Keep additional qualifying teams on the waitlist or promote selectively |
| Team below minimum size | Add an eligible player or leave the team waitlisted |
| Invalid or duplicate player | Replace/remove the player and verify eligibility |
| Player not found at check-in | Verify the PID or registration record |
| Answer needs review | Judge the answer and update the team’s progress |
| Incorrect indictment | Leave the team in the incorrect-indictment standings |
| Game ended unexpectedly | Review timing/configuration, then use the administrative results workflow |
| Data needs recovery | Use the admin trash/restore workflow where appropriate |
