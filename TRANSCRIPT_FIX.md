# Transcript & Route Error Fixes

## Issues Fixed

### 1. Route Helper Error on Disconnect ✅

**Problem**: When clicking "End" button, got error: `route is not defined`

**Root Cause**: The `route()` helper from Ziggy is available globally in templates via the ZiggyVue plugin, but in `<script setup>` context within event handlers, it wasn't accessible.

**Solution**: 
- Removed the `route()` import from `ziggy-js` 
- Changed to direct URL paths: `router.visit('/feedback')` and `router.visit('/')`
- This is simpler and avoids the context issue entirely

**Files Modified**:
- `resources/js/pages/Interview.vue`

---

### 2. Transcript Display Missing ✅

**Problem**: Agent's text and user's text were not shown on screen during the interview.

**Root Cause**: 
- Only the latest agent line was displayed in the center of the screen
- No transcript history was being tracked
- No UI component existed to show the conversation history

**Solution**:
1. **Created TranscriptPanel Component** (`resources/js/components/TranscriptPanel.vue`)
   - Sliding panel from the right side
   - Shows all messages with timestamps
   - User messages styled differently from agent messages
   - Smooth transitions and animations

2. **Updated InterviewRoomContent Component**
   - Added `transcriptMessages` array to track all messages
   - Added `showTranscript` toggle state
   - Watch agent metadata for new agent transcripts
   - Watch local participant metadata for user transcripts
   - Added `toggleTranscript()` function
   - Connected MessageSquare button to toggle the panel
   - Automatically adds messages to history when received

**Files Created**:
- `resources/js/components/TranscriptPanel.vue`

**Files Modified**:
- `resources/js/components/InterviewRoomContent.vue`

---

## How Transcripts Work Now

### Agent Transcripts
1. Agent sends metadata in format: `{"transcript": "text"}`
2. Frontend watches `agent?.metadata` 
3. When new transcript arrives:
   - Updates `latestAgentLine` (shown in center)
   - Adds to `transcriptMessages` array (shown in panel)
   - Logs to console for debugging

### User Transcripts
1. User speaks into microphone
2. LiveKit processes speech-to-text
3. User's local participant metadata should contain transcript
4. Frontend watches `localParticipant.value?.metadata`
5. When transcript arrives, adds to `transcriptMessages` array

### Viewing Transcripts
- Click the MessageSquare (💬) button in bottom controls
- Panel slides in from the right
- Shows all messages with speaker labels and timestamps
- Click X or MessageSquare again to close

---

## Testing Checklist

- [x] TypeScript compilation passes (`npm run types:check`)
- [x] ESLint passes (`npm run lint:check`)
- [x] Production build succeeds (`npm run build`)
- [x] PHP formatting passes (`vendor/bin/pint`)
- [ ] End button works without errors
- [ ] Transcript panel opens/closes
- [ ] Agent messages appear in transcript
- [ ] User messages appear in transcript (requires backend metadata)

---

## Known Limitations

1. **User Transcripts**: The frontend is ready to receive user transcripts, but the backend agent needs to send them via metadata. Currently, the agent logs show it receives user transcripts, but they may not be sent back to the frontend in the participant metadata.

2. **Agent Detection**: The frontend still shows "Agent state: disconnected" even though the backend logs prove the agent is working. This suggests the agent participant isn't being properly detected by the LiveKit Vue composables.

---

## Next Steps (If Needed)

If user transcripts still don't appear:
1. Check if the Python agent sends user transcripts back via participant metadata
2. Verify the metadata format matches: `{"transcript": "text"}`
3. Check LiveKit room participant list to ensure agent is visible
4. May need to update `microservices/agent.py` to publish transcripts correctly
