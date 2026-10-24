# Preview of "Long Live Men" (Work-in-Progress Site)

Getting your friend's uploaded website (long-live-men-wip.zip) running so you can open it and look around in a live preview.
The site stays as your friend built it, with only the minimum fixes needed to make it load.

## Who it's for
- You: you want to see what your friend has built so far.
- Your friend: they can see their work-in-progress running live, without any redesign.

## Core features and experience
- The site's own pages, layout, text, images and styling are kept as your friend made them.
- Only problems that stop the site from opening or a page from loading get fixed (for example missing setup, broken references, or errors on startup).
- Anything that needs outside services (payments, logins, email, API keys) is switched to placeholder or sample data, so pages show content but nothing real gets sent, charged or signed into.
- Unfinished sections stay unfinished. No new features, redesigns or polish get added.
- A short list of what was changed to get it running, and what is still unfinished or placeholder, so your friend knows.

## User flow
1. Open the preview link.
2. Land on the site's home page as your friend designed it.
3. Click through the menus and pages the same way a visitor would.
4. Where a page relies on an outside service, sample content shows up in its place.

## UI/UX feel
- Exactly your friend's design. No visual changes except where something is broken badly enough that the page can't display.

## Implementation phases
- **Phase 1 (now): Running preview.** Unpack the site, get it running, fix only what blocks it from opening, put placeholder data in for outside services, and report what was changed and what is still placeholder.
- **Phase 2 (later, if wanted): Fix rough parts.** Tidy broken layouts, missing images and half-built pages your friend wants finished.
- **Phase 3 (later, if wanted): Real services.** Connect real payments, logins or other services once account keys are provided.

## Assumptions
- The zip holds a complete website project (not just design files). If it turns out to be only images or mockups, the preview shows whatever can be displayed and the report explains what's missing.
- The site may be moved onto this platform's setup so it can run here. Its look and content stay the same.
- Any real keys, passwords or private data found in the files are not used. Placeholders take their place.
- Any sample/placeholder content that is added is clearly fake and easy to replace later.
- Only the preview is set up. Nothing gets published or put live publicly.
