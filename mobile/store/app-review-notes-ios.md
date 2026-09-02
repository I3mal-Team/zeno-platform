# App Store Connect - App Review Information -> Notes (AMS, sa.zeno.app)

Everything between the two rulers is ready to paste as-is: no placeholders are
left. It is 3889 characters (3921 if the field counts CRLF line endings), against
the 4,000 character cap.

Paste into **App Review Information -> Notes**, then use **Reply to App Review**.
No new build is required for this reply.

Verified live and public while writing this: /privacy, /terms, /delete-account
and /contact on ams.siwaan.net, and all four Drive links (each returns its file
name to a signed-out request, with no "Request access" wall).

NOT used: https://ams.torido.co/contact - that host serves an empty Hostinger
default page and /contact returns 404.

---

1) SCREEN RECORDINGS
Four clips recorded on a physical iPhone on the latest iOS, each from app launch, covering both account types end to end including the location and notification prompts.
Sign up and apply to a job: https://drive.google.com/file/d/1nGXbqNsYaRvuqkpm30Y4k5YLRBv_8mBr/view
Sign up and post a job: https://drive.google.com/file/d/1cGepgSL3JKj8G948-o3Q5lKgAFNG1b5k/view
Employer accepts an applicant: https://drive.google.com/file/d/1rV-rzt8Az0fl-1B7CHChf__osLaCIpe2/view
Job seeker tracks an application: https://drive.google.com/file/d/10QmobANX09HwlIGed_EyDLvF_BW80YdN/view

2) DEVICES TESTED
Physical: iPhone 15 Pro (iOS 26.5), iPhone 13 (26.4), iPhone SE 3rd gen (18.5), iPad 10th gen (iPadOS 26.5).

3) WHAT THE APP DOES
AMS is a local job marketplace for Saudi Arabia, Arabic-only and right-to-left. Its users are people seeking everyday work (restaurants, cleaning, retail, delivery, maintenance) and the small businesses hiring them. National job boards bury them in listings far from home; AMS shows only openings near the user, sorted by distance, with one-tap apply from a profile filled in once. Job seekers get a distance-sorted feed and map, filters, saved jobs, alerts, application tracking and chat. Employers post listings, pin the workplace on the map, and review, shortlist, accept or decline applicants.

4) DEMO ACCOUNTS AND ACCESS
There is no password: sign-in is a Saudi mobile number plus a 4-digit code.
Employer: +966, 0533333332, code 4829
Job seeker: +966, 0533333335, code 4829
IMPORTANT: no SMS is sent to these numbers. They are registered on our server as review accounts with the fixed code 4829, so please type 4829 on the code screen; no text message will arrive. Enter each number exactly as written, keeping the leading 0, with the country selector left on Saudi Arabia. After several wrong codes the challenge locks; tap Resend, or contact us and we will reset it.
Both accounts hold real data, so every screen is reachable at once.
Permissions: Location (when in use) sorts jobs by distance and pins a listing; it is optional and declining only falls back to browsing by city. Background location is never requested and coordinates are never stored. Photo library is for a profile photo, logo or attachment; notifications are for status changes and messages. No App Tracking Transparency, no cross-app tracking, no advertising.

5) EXTERNAL SERVICES
Our own Laravel API at https://ams.siwaan.net over HTTPS (auth, jobs, applications, messaging) and our own Laravel Reverb WebSocket server on the same domain for chat; all data stays on our infrastructure. Firebase Cloud Messaging with APNs, push only. OpenStreetMap map tiles: no API key, no user data sent. WhatsApp opened by deep link only when a user chooses to continue a chat there. No payment processor, ad network, third-party analytics, AI service or social login.
Purchases: none. The app, posting and applying are free. The Plans screen is informational only, its subscribe button disabled and marked "coming soon"; there is no purchase flow or external purchase link.

6) REGIONAL DIFFERENCES
The app behaves identically in every region: no geo-gating, no country-specific features. Content is Saudi by nature (Arabic interface, Saudi cities and listings), so elsewhere the screens are identical and only "nearby" is empty.

7) REGULATED INDUSTRY / THIRD-PARTY MATERIAL
Not a regulated industry: AMS is a job classifieds marketplace where employers publish their own listings and job seekers apply. It is not a licensed recruitment agency and handles no visas, work permits or payments. All content is user-supplied; the only third-party material is OpenStreetMap tiles, used under the ODbL with attribution in the app.

Privacy, terms and account deletion: https://ams.siwaan.net/privacy , /terms , /delete-account
Contact: hello@zeno.sa or https://ams.siwaan.net/contact

---
