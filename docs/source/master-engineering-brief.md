# ZENO — MASTER PRODUCT, DESIGN, AND ENGINEERING EXTRACTION PROMPT

## Role and mission

You are working on **zeno (زينو)**, a high-fidelity interactive prototype for a Saudi proximity-based job marketplace.

Act simultaneously as:

- Principal Product Manager
- Lead Product Designer
- Senior UX Architect
- Senior Business Analyst
- Solution Architect
- Backend Architect
- Mobile Architect
- Web Architect
- API Designer
- Database Architect
- Security Architect
- QA Lead
- DevOps Architect
- Technical Writer

Your task is **not to redesign the product casually** and not to produce another superficial UI concept.

Your task is to inspect the complete existing prototype and convert it into a **production-ready product and engineering specification** that can be used to build the real platform across backend, APIs, mobile applications, public website, employer dashboard, and admin console.

The final result must allow separate engineering teams to implement the project without relying on undocumented assumptions or repeatedly reopening the prototype to understand basic behavior.

---

# 1. Existing source files

The current product is represented by the following interactive design files:

```text
Zeno.dc.html
Zeno-Landing.dc.html
Zeno-Jobs.dc.html
Zeno-Web.dc.html
Zeno-Pricing.dc.html
Zeno-About.dc.html
Zeno-Company.dc.html
Zeno-Contact.dc.html
Zeno-Terms.dc.html
assets/zeno-logo.png
assets/zeno-mark.png
```

You must inspect **every file, screen, view, section, state, interaction, event handler, mock object, form field, navigation control, modal, drawer, bottom sheet, toast, table, card, status, filter, list, badge, metric, label, icon, and piece of visible copy**.

Do not document only the obvious screens. Inspect the implementation itself to find:

- Hidden screens and role paths
- State variables
- Mock data structures
- Status values
- Conditional rendering
- Form readiness logic
- Client-side validations
- Button actions
- Navigation transitions
- Filters and sorting logic
- Confirmation dialogs
- Toast messages
- Empty states
- Role-specific behavior
- Reused components
- Inferred domain entities
- Dead controls and incomplete interactions
- Differences between mobile and web behavior

---

# 2. Product context

## 2.1 Product definition

**zeno (زينو)** is a Saudi Arabia-focused, proximity-based employment marketplace for:

- Blue-collar jobs
- Hourly jobs
- Part-time jobs
- Seasonal jobs
- Temporary jobs
- Full-time service jobs
- Hospitality
- Retail and cashier roles
- Delivery and logistics
- Cleaning
- Events
- Maintenance
- Security
- Crafts and technical work

The platform connects candidates with nearby employers and supports direct communication through in-app messaging and WhatsApp.

## 2.2 Main user roles

The prototype includes three principal roles:

1. Candidate / Job Seeker — المرشح / الباحث عن عمل
2. Employer — صاحب العمل
3. Admin — الإدارة

Signup also includes an account-type distinction:

- Individual — فرد
- Organization — منشأة

The exact business consequences of account type are not fully defined and must be treated as an open product decision rather than invented.

## 2.3 Product surfaces

The production platform is expected to contain:

1. Candidate mobile application
2. Employer mobile application experience
3. Public marketing website
4. Public jobs-discovery website
5. Employer web dashboard
6. Production-grade admin web console
7. Shared backend
8. Shared authentication and identity service
9. Shared database and media services
10. Versioned APIs used by mobile and web clients

---

# 3. Non-negotiable analysis rules

## 3.1 Separate facts from assumptions

Every meaningful statement must be classified as one of:

- **CONFIRMED** — explicitly visible or implemented in the source prototype
- **INFERRED** — logically required by the UI but not decided in the prototype
- **PROPOSED** — your recommended production decision
- **OPEN DECISION** — cannot be safely determined and requires product-owner approval

Never present inferred or proposed business rules as confirmed facts.

## 3.2 Do not invent hidden product requirements

You may propose missing logic, but you must:

1. State why it is needed.
2. Describe the recommended default.
3. Describe alternative options.
4. Explain the impact of each option.
5. Mark the final choice as pending approval.

## 3.3 Preserve the design identity

The current visual identity is authoritative unless there is a clear usability, accessibility, responsiveness, or technical issue.

Do not replace the brand, layout system, navigation architecture, typography, colors, or interaction patterns without explicitly documenting:

- The existing confirmed design
- The discovered problem
- The recommended adjustment
- The implementation impact

## 3.4 Production completeness

Do not stop at screen descriptions.

The specification must cover:

- Product behavior
- Business logic
- Permissions
- Data model
- API contracts
- State machines
- Security
- Privacy
- Saudi compliance considerations
- Performance
- Reliability
- Notifications
- Messaging
- Geolocation
- Search
- Moderation
- Billing readiness
- Analytics
- Observability
- Testing
- Deployment
- Migration from mock data

## 3.5 No generic filler

Avoid statements such as:

- “Use best practices.”
- “Ensure security.”
- “Make it scalable.”
- “Handle errors gracefully.”

Every requirement must explain exactly what needs to happen, who is allowed to do it, which data is involved, and how success and failure should be represented.

---

# 4. Confirmed visual design foundation

Preserve and document the following confirmed brand foundation.

## 4.1 Core palette

| Token | Value | Primary use |
|---|---:|---|
| Brand Amber | `#F7BE17` | Primary calls to action, active states, highlights |
| Amber Deep | `#F2A50E` | Gradient partner and emphasis |
| Charcoal | `#211F20` / `#2B2724` | Headers, navigation, dark surfaces, strong text |
| Charcoal Gradient | `#34302B` to `#26221F` | Dark hero and header backgrounds |
| App Paper | `#F6F5F1` | Mobile background |
| Web Paper | `#F1EFE9` | Employer dashboard background |
| Warm Paper | `#FAF8F3` | Marketing website background |
| Surface | `#FFFFFF` | Cards and input surfaces |
| Border | `#EFEDE6` / `#EDEAE2` / `#ECEAE3` | Dividers and borders |
| Text Strong | `#211F20` | Titles and key content |
| Text Body | `#5A554C` / `#56524A` | Body copy |
| Text Muted | `#8A857A` / `#9A958A` | Metadata and secondary content |

## 4.2 Semantic status colors

Document and normalize success, warning, error, information, WhatsApp, and verification colors into production design tokens.

## 4.3 Typography

- Main font: Tajawal
- Weights: 400, 500, 700, 800, 900
- Primary language: Arabic
- Global direction: RTL
- Use correct Arabic line-height and number formatting
- Define desktop, tablet, and mobile typography tokens

## 4.4 Icons

The prototype uses Iconsax. Audit all existing icon names and produce a validated icon registry.

Do not assume an icon exists because its name sounds correct.

## 4.5 Motion

Preserve the restrained motion language:

- Transform-based screen entrances
- Button press scaling
- Card lift on hover
- Toast entrance
- Modal and drawer transitions
- Map pulse
- Loading mark animation

Create reduced-motion behavior and accessibility alternatives.

---

# 5. Required final deliverable structure

Create a complete documentation folder using Markdown files.

Use the following exact structure unless a better file split is clearly justified:

```text
/docs
  00-README.md
  01-product-vision-and-scope.md
  02-source-audit-and-traceability.md
  03-roles-permissions-and-access-control.md
  04-information-architecture-and-navigation.md
  05-screen-inventory-and-ui-specification.md
  06-user-journeys-and-use-cases.md
  07-business-rules-and-state-machines.md
  08-validation-and-message-catalog.md
  09-design-system-and-responsive-rules.md
  10-domain-model-and-database-schema.md
  11-api-specification.md
  12-authentication-security-and-privacy.md
  13-search-geolocation-and-maps.md
  14-messaging-whatsapp-and-notifications.md
  15-employer-billing-and-monetization-readiness.md
  16-admin-moderation-and-operations.md
  17-analytics-events-and-reporting.md
  18-non-functional-requirements.md
  19-testing-and-quality-strategy.md
  20-devops-deployment-and-observability.md
  21-development-roadmap-and-sprints.md
  22-open-decisions-and-product-questions.md
  23-requirements-traceability-matrix.md
  24-engineering-handoff-checklist.md
/openapi
  zeno-openapi.yaml
/diagrams
  system-context.mmd
  container-architecture.mmd
  domain-erd.mmd
  auth-sequence.mmd
  application-sequence.mmd
  messaging-sequence.mmd
  job-lifecycle.mmd
  application-lifecycle.mmd
```

All Mermaid diagrams must be syntactically valid.

The OpenAPI file must be valid OpenAPI 3.1 YAML.

---

# 6. File-by-file source audit

Create a complete traceability audit for every source file.

For each file, document:

- Surface represented by the file
- Target users
- Screens/views contained in it
- State variables
- Mock data objects and their fields
- User actions
- Event handlers
- Navigation transitions
- Forms
- Validation currently enforced
- Status values
- Empty states
- Toasts and feedback messages
- Responsive limitations
- Dead or incomplete controls
- Business rules embedded in frontend logic
- Production backend capability implied by each interaction

Create a table linking:

```text
Source file → Screen → UI element → User action → Current prototype behavior → Required production behavior → API → Permission → Acceptance criteria
```

No source file may be omitted.

---

# 7. Product scope and module decomposition

Produce a definitive module map covering at least:

## Shared platform modules

- Identity and authentication
- OTP management
- User accounts
- Candidate profiles
- Employer organizations
- Employer verification
- Role and permission management
- Categories and reference data
- Jobs
- Applications
- Conversations
- Messages
- Notifications
- Geolocation and maps
- Search and filtering
- Saved jobs, only if approved or visibly required
- Reports and moderation
- Public company profiles
- Public jobs pages
- Contact requests
- Legal content
- Analytics
- Subscription and billing readiness
- Admin operations
- Audit logs
- Feature flags
- Content management

For each module define:

- Purpose
- Primary users
- Responsibilities
- Owned entities
- Public interfaces
- Dependencies
- Events emitted
- Events consumed
- Security boundaries
- Failure modes
- Out-of-scope items

---

# 8. Roles and permission matrix

Create a production-grade RBAC permission matrix.

At minimum cover:

- Guest
- Candidate
- Employer individual
- Employer organization owner
- Employer organization manager
- Employer recruiter
- Support agent
- Moderator
- Verification reviewer
- Finance/admin billing operator
- Platform administrator
- Super administrator

Some of these roles are proposed, not confirmed. Clearly mark them as proposed.

For every feature and API operation specify:

- Authentication required
- Role required
- Ownership rule
- Organization membership rule
- Verification requirement
- Subscription requirement, if applicable
- Allowed state transitions
- Audit requirement

Explain whether candidate and employer functionality may coexist under one identity or require separate accounts. Present alternatives and recommend one.

---

# 9. Screen-by-screen product specification

Document every existing screen across mobile, public web, employer dashboard, and admin.

For every screen provide:

1. Screen ID
2. Arabic name
3. English internal name
4. Surface
5. Recommended route
6. Intended role
7. Entry points
8. Exit paths
9. Purpose
10. Preconditions
11. Data dependencies
12. Layout anatomy
13. Every visible component
14. Every action
15. Permissions
16. Business rules
17. Validation
18. Loading state
19. Empty state
20. Success state
21. Error state
22. Offline state
23. Permission-denied state
24. Suspended-account state
25. Closed/deleted-content state
26. Analytics events
27. Accessibility requirements
28. Responsive behavior
29. API dependencies
30. Acceptance criteria

Do not group several screens into one vague paragraph.

Document all confirmed screens, including:

### Mobile authentication and onboarding

- Loading
- Onboarding carousel
- Role picker
- Phone entry
- OTP verification
- Candidate registration
- Employer registration

### Candidate mobile

- Browse/home
- Search
- Advanced filters
- Nearby/map
- Job details
- Application submitted
- My applications
- Conversation list
- Chat
- WhatsApp handoff
- Candidate profile
- Edit profile
- Notifications

### Employer mobile

- Employer home/my jobs
- Create job
- Edit job
- Pause confirmation
- Applicants
- Applicant detail
- Employer conversations
- Employer chat
- Employer account

### Employer dashboard

- Overview
- Jobs management
- Applicants
- Messages
- Create-job modal
- Edit-job experience
- Notifications
- Upgrade/subscription entry point

### Public website

- Landing
- Jobs listing
- Job detail drawer
- Pricing
- About
- Public employer/company profile
- Contact
- Terms and privacy

### Admin

- Current mobile prototype tabs
- Proposed production web admin screens required to operate the platform

---

# 10. User stories and acceptance criteria

For every feature, write user stories using:

```text
As a [role],
I want [capability],
So that [business value].
```

Then add acceptance criteria in Given/When/Then format.

Acceptance criteria must include:

- Happy path
- Validation failures
- Unauthorized access
- Wrong role
- Missing ownership
- Duplicate action
- Concurrent updates
- Expired entity
- Closed job
- Paused job
- Filled vacancies
- Suspended user
- Deleted content
- Network failure
- API timeout
- Retry behavior
- Idempotency where relevant

Do not write only one acceptance criterion per feature.

---

# 11. Business logic specification

Create explicit business rules for the entire platform.

At minimum address the following.

## 11.1 Authentication

- Saudi mobile normalization
- Country code storage
- OTP request
- OTP expiry
- Resend cooldown
- Attempt limits
- Rate limiting
- Device/session management
- Refresh tokens
- Logout one device
- Logout all devices
- Account suspension
- Phone-number change
- Account deletion

Mark exact thresholds as proposed until approved.

## 11.2 Candidate profiles

- Required and optional fields
- Age requirements
- Nationality handling
- City selection
- Experience years
- Skills format
- Profile completeness
- Public-profile visibility
- Profile links in WhatsApp
- Privacy restrictions

## 11.3 Employer organizations

- Individual versus organization behavior
- Organization ownership
- Team members
- Verification
- Commercial registration fields, if needed
- Public company profile
- Employer suspension

## 11.4 Jobs

Define the complete job lifecycle, including proposed states such as:

```text
draft
pending_review
active
paused
closed
expired
filled
rejected
removed
archived
```

Map confirmed prototype values to production states.

Define:

- Creation rules
- Required fields
- Salary structure
- Salary units
- Work types
- Gender and nationality restrictions
- Legal/compliance review
- Publishing
- Editing
- Pausing
- Resuming
- Expiration
- Filling vacancies
- Closing
- Moderation removal
- Duplicate detection
- Visibility in search
- Visibility on public web
- Notification of affected applicants after edits

## 11.5 Applications

Define the complete application state machine.

Start from confirmed states:

```text
submitted
new
review
accepted
rejected
```

Recommend a normalized production model, potentially including:

```text
submitted
viewed
shortlisted
accepted
rejected
withdrawn
expired
hired
cancelled
```

Do not silently add states. Explain each proposed state and its value.

Define:

- One application per candidate per job
- Idempotent application creation
- Request-number generation
- Employer decision permissions
- Candidate withdrawal
- Employer reversal rules
- Job closure impact
- Vacancy concurrency
- Candidate notification
- Conversation creation timing
- Data retention

## 11.6 Search and proximity

Define:

- Arabic text normalization
- Keyword matching
- Category filters
- Work-type filters
- Gender and nationality filters
- City and district filters
- Distance calculation
- Radius presets
- Default sort
- Nearest sort
- Newest sort
- Salary sort
- Pagination
- Map clustering
- Location-denied fallback
- Job-location privacy

## 11.7 Messaging

Define:

- When a conversation can be created
- Who can message whom
- Whether messaging is blocked before application
- Read receipts
- Delivery states
- Message editing/deletion
- Attachments
- Spam controls
- Blocking and reporting
- Retention
- Realtime transport
- Push notification behavior

## 11.8 WhatsApp

Define:

- Consent
- Employer phone exposure
- Candidate phone exposure
- Deep-link template
- Public profile link access
- Tracking the handoff event
- Fallback when WhatsApp is unavailable
- Privacy and abuse considerations

## 11.9 Notifications

Define triggers for:

- Application submitted
- Application viewed
- Accepted
- Rejected
- New message
- Matching job
- Job edited
- Job paused or closed
- Verification approved/rejected
- Subscription expiry
- Report resolution

Specify channel eligibility:

- In-app
- Push
- SMS
- Email

## 11.10 Moderation

Define:

- Candidate reports
- Employer reports
- Job reports
- Message reports
- Automated flags
- Review queue
- Moderator decisions
- Appeal
- Takedown
- Suspension
- Audit trail

---

# 12. State machines

Create Mermaid state diagrams for:

1. User account lifecycle
2. Employer verification lifecycle
3. Job lifecycle
4. Application lifecycle
5. Conversation lifecycle
6. Report/moderation lifecycle
7. Subscription lifecycle, if monetization is retained

For every transition specify:

- Actor
- Preconditions
- Command/API
- Side effects
- Notification emitted
- Audit entry
- Invalid transition response

---

# 13. Form and validation catalog

Create a complete validation matrix for every field in every form.

For each field include:

- Form name
- Field key
- Arabic label
- English internal name
- Data type
- Required or optional
- Minimum
- Maximum
- Allowed values
- Normalization
- Client validation
- Server validation
- Security validation
- Arabic error message
- Error code
- Accessibility announcement

Cover at least:

- Phone
- OTP
- Candidate profile
- Employer profile
- Job create/edit
- Search filters
- Chat message
- Contact form
- Report form
- Verification submission
- Admin actions
- Billing fields, if applicable

Do not invent exact length limits without marking them as proposed.

---

# 14. User-facing message catalog

Create one authoritative message catalog for:

- Success messages
- Validation errors
- Authentication errors
- Permission errors
- Rate-limit errors
- Not-found errors
- Conflict errors
- Offline messages
- Timeout messages
- Empty-state titles and descriptions
- Confirmation-dialog copy
- Destructive-action warnings
- Moderation notices
- Account suspension notices
- Job-state notices
- Application-state notices

Every message must include:

```text
message_key
Arabic copy
English developer meaning
Trigger condition
Surface
Severity
Recommended duration or persistence
```

Arabic copy must sound natural for Saudi users and must not feel machine-translated.

---

# 15. Design system and responsive specification

Convert the current visual design into a production design system.

Include:

- Primitive color tokens
- Semantic color tokens
- Typography scale
- Spacing scale
- Radius scale
- Shadow scale
- Z-index system
- Motion tokens
- Icon registry
- Grid system
- Breakpoints
- Container widths
- Safe-area handling
- RTL rules
- Touch-target sizes
- Focus states
- Keyboard navigation
- Reduced motion
- Color contrast
- Screen reader labels

Document every reusable component with:

- Purpose
- Anatomy
- Variants
- Sizes
- States
- Props
- Events
- Validation behavior
- Accessibility requirements
- Mobile behavior
- Desktop behavior

Components must include all confirmed prototype components and any essential missing production states.

Provide a recommended breakpoint system for:

```text
small mobile
large mobile
small tablet
large tablet
small desktop
large desktop
```

Clearly distinguish proposed breakpoints from confirmed layout sizes.

---

# 16. Domain model and database design

Design a normalized production data model.

At minimum evaluate and define:

- users
- user_sessions
- otp_challenges
- candidate_profiles
- organizations
- organization_members
- employer_profiles
- employer_verification_requests
- employer_verification_documents
- categories
- cities
- districts
- jobs
- job_locations
- job_status_history
- job_views
- saved_jobs, only if approved
- applications
- application_status_history
- conversations
- conversation_participants
- messages
- message_receipts
- notifications
- notification_preferences
- device_tokens
- reports
- moderation_actions
- contact_requests
- subscription_plans
- subscriptions
- invoices
- payments
- audit_logs
- feature_flags
- legal_documents
- legal_acceptances

For every table provide:

- Purpose
- Columns
- Data types
- Nullability
- Defaults
- Primary key
- Foreign keys
- Unique constraints
- Check constraints
- Indexes
- Soft-delete policy
- Retention policy
- Sensitive-data classification

Include an ER diagram.

Explicitly solve:

- One application per candidate per job
- Request-number uniqueness
- Multiple employer team members
- Realtime conversations
- Notification unread counts
- Geospatial radius search
- Status history
- Auditability
- Idempotency
- Public slugs

Recommend whether PostgreSQL with PostGIS should be used and explain why.

---

# 17. API specification

Produce a full OpenAPI 3.1 document.

Use a versioned base path such as:

```text
/api/v1
```

The exact structure is proposed and must be documented.

Cover at least:

## Authentication

- Request OTP
- Verify OTP
- Refresh session
- Logout
- Logout all sessions
- Get current user
- Change phone
- Delete account

## Candidate profile

- Get profile
- Update profile
- Get public profile, if approved

## Employer and organization

- Create/update employer profile
- Organization members
- Verification submission
- Verification status
- Public company profile

## Jobs

- Create
- Update
- Publish
- Pause
- Resume
- Close
- Delete/archive
- List employer jobs
- Public list
- Nearby list
- Search
- Detail
- View tracking

## Applications

- Apply
- Get my applications
- Get employer applications
- Detail
- Change status
- Withdraw

## Conversations and messages

- List conversations
- Open/create conversation
- List messages
- Send message
- Mark read
- Realtime connection contract

## Notifications

- List
- Mark one read
- Mark all read
- Preferences
- Register device token

## Reports and moderation

- Create report
- Admin queue
- Resolve report
- Suspend account
- Remove job

## Admin

- Dashboard metrics
- Users
- Employers
- Verification queue
- Jobs
- Applications
- Reports
- Audit logs
- Reference data

## Public content

- Categories
- Cities/districts
- Pricing plans
- Legal documents
- Contact requests

For every endpoint include:

- Method and path
- Purpose
- Authentication
- Permissions
- Request headers
- Path/query parameters
- Request body
- Response body
- Pagination
- Sorting
- Filtering
- Success codes
- Error codes
- Idempotency
- Rate limits
- Audit behavior
- Example requests and responses

Use a consistent error envelope.

Recommend an error model such as:

```json
{
  "error": {
    "code": "APPLICATION_ALREADY_EXISTS",
    "message": "لقد سبق لك التقديم على هذه الوظيفة",
    "details": {},
    "trace_id": "..."
  }
}
```

---

# 18. Architecture recommendation

Recommend a production architecture suitable for initial launch and future scaling.

Do not default to microservices without justification.

Compare:

1. Modular monolith
2. Service-oriented architecture
3. Microservices

Recommend the best starting architecture for zeno and explain:

- Cost
- Team size
- Complexity
- Deployment
- Transaction consistency
- Future extraction paths

Provide:

- System-context diagram
- Container diagram
- Backend module boundaries
- API gateway/BFF decision
- WebSocket/realtime architecture
- Background-job processing
- Search architecture
- Geospatial architecture
- File storage/CDN
- Push notification integration
- SMS/OTP provider abstraction
- WhatsApp deep-link behavior
- Admin architecture

Recommend a technology stack, but separate requirements from stack choice.

A possible stack to evaluate:

- Backend: Laravel or another suitable framework
- Database: PostgreSQL + PostGIS
- Cache/queues: Redis
- Object storage: S3-compatible storage
- Search: PostgreSQL full-text initially, with future OpenSearch/Elasticsearch path
- Mobile: Flutter or native/cross-platform alternative
- Public web/dashboard: modern SSR-capable frontend
- Realtime: WebSockets
- API docs: OpenAPI
- CI/CD: containerized deployments

Explain why each choice is appropriate for this product.

---

# 19. Security, privacy, and compliance

Produce a concrete security specification covering:

- OTP abuse prevention
- Rate limiting
- Session rotation
- Refresh token security
- Device revocation
- RBAC
- Object-level authorization
- Organization-level authorization
- Admin MFA
- Encryption at rest and in transit
- Sensitive-field masking
- Phone-number privacy
- Secure audit logs
- File-upload scanning
- Message abuse prevention
- Report handling
- API validation
- SQL injection prevention
- XSS and CSRF
- CORS
- Secrets management
- Backup security
- Incident response

Evaluate Saudi PDPL considerations, including:

- Consent
- Purpose limitation
- Data minimization
- Retention
- Deletion requests
- User access requests
- Data residency
- Third-party processors
- Location data
- Communications data
- Public profile exposure

Legal text must be marked for legal-counsel review.

---

# 20. Search, maps, and location

Define production behavior for:

- Requesting location permission
- Denied permission
- Approximate location
- Chosen city without GPS
- Employer location selection
- Geocoding
- Reverse geocoding
- Distance calculation
- PostGIS radius queries
- Map pins
- Pin clustering
- Pagination across map and list
- District privacy
- Location rounding
- Background location prohibition unless genuinely needed

Recommend a map provider and include alternatives, pricing/lock-in considerations, and Saudi coverage considerations.

---

# 21. Messaging, realtime, WhatsApp, and notifications

Design the full communication architecture.

Include:

- Conversation eligibility
- Conversation creation
- Message persistence
- WebSocket channels
- Presence, only if justified
- Read receipts
- Delivery receipts
- Unread counts
- Retry and deduplication
- Offline queueing
- Push notifications
- Message moderation
- Blocking and reporting
- Retention
- Attachment policy
- WhatsApp handoff tracking

Provide sequence diagrams for:

- Sending a message
- Receiving a realtime message
- Marking messages read
- Opening WhatsApp from an application

---

# 22. Employer pricing and monetization readiness

The prototype contains pricing tiers and an upgrade entry point, but commercial rules are not confirmed.

Document:

- What is confirmed visually
- What remains undecided
- Recommended monetization models
- Plan entitlements
- Job-posting limits
- Featured listings
- Verification relationship
- Trial periods
- Billing cycle
- VAT invoices
- Saudi payment gateway options
- Subscription status model
- Payment failures
- Grace periods
- Downgrades
- Cancellation
- Refunds

Do not treat prototype prices as production prices.

---

# 23. Admin and moderation console

The existing admin prototype is incomplete.

Define the production admin console required to run the platform.

At minimum include:

- Admin authentication with MFA
- Dashboard
- User management
- Candidate management
- Employer management
- Employer verification queue
- Organization management
- Job management
- Application visibility
- Reports/moderation queue
- Message metadata review under controlled policy
- Categories
- Cities and districts
- Pricing plans
- Subscriptions and invoices
- Contact requests
- Legal documents
- Feature flags
- Notification campaigns
- Audit logs
- System health summary

For every admin action define:

- Required permission
- Confirmation requirement
- Reason field
- Audit log
- Reversible or irreversible behavior
- User notification
- Data retention impact

---

# 24. Analytics and event tracking

Define a complete event taxonomy.

For every event include:

- Event name
- Actor
- Surface
- Trigger
- Properties
- Personally identifiable data restrictions
- Business metric supported

Cover at least:

- App launched
- Onboarding viewed/skipped/completed
- Role selected
- OTP requested/verified/failed
- Registration completed
- Job impression
- Job detail viewed
- Search performed
- Filter applied
- Radius changed
- Apply clicked
- Application submitted
- Application status changed
- Chat opened
- Message sent
- WhatsApp opened
- Job created
- Job published
- Job paused
- Applicant accepted/rejected
- Employer upgrade clicked
- Contact form submitted
- Report created

Define exact metric formulas for:

- Active jobs
- New applications
- Total views
- Acceptance rate
- Average time to hire
- Job conversion rate
- Candidate activation
- Employer activation
- Retention

Mark placeholder marketing metrics as non-production.

---

# 25. Non-functional requirements

Define measurable requirements for:

## Performance

- API latency percentiles
- Search response time
- Map query time
- Message delivery time
- Mobile startup time
- Page performance targets
- Image optimization
- Pagination
- List virtualization

## Availability and resilience

- Availability target
- Retry policy
- Circuit breaking
- Queue retries
- Dead-letter queues
- Backup frequency
- Restore objectives
- Disaster recovery

## Scalability

Provide initial, medium, and large scale assumptions for:

- Users
- Active jobs
- Applications per day
- Messages per day
- Concurrent realtime connections
- Notifications
- Search traffic

## Accessibility

- WCAG target
- Contrast
- Keyboard support
- Screen-reader support
- Focus handling
- Touch targets
- Reduced motion

## Localization

- Arabic RTL
- Saudi phone formatting
- SAR formatting
- Gregorian/Hijri decision
- English readiness
- Translation key structure

## Maintainability

- Module boundaries
- Code conventions
- API versioning
- Deprecation policy
- Documentation requirements

---

# 26. Testing strategy

Create a complete quality strategy covering:

- Unit tests
- Domain tests
- API tests
- Authorization tests
- Database constraint tests
- Integration tests
- Contract tests
- Web component tests
- Mobile widget/component tests
- End-to-end tests
- Realtime messaging tests
- Geolocation tests
- Load tests
- Security tests
- Accessibility tests
- Visual regression tests
- Arabic RTL tests
- Device and browser matrix

Create a feature-to-test matrix.

Include critical end-to-end scenarios such as:

1. Candidate registration and first application
2. Duplicate application prevention
3. Employer creates and publishes a job
4. Employer pauses a job
5. Candidate cannot apply to a paused/closed/filled job
6. Employer accepts an applicant
7. Candidate receives notification
8. Candidate and employer exchange messages
9. Unauthorized user cannot access employer/admin data
10. Vacancy race condition
11. OTP rate limiting
12. Location permission denied
13. Admin removes a reported job

---

# 27. DevOps, deployment, and observability

Define:

- Development environments
- Staging
- Production
- Environment variables
- Secret management
- Database migrations
- Seed/reference data
- Feature flags
- CI pipeline
- Automated tests
- Container builds
- Deployment strategy
- Rollback
- Mobile release process
- Web deployment
- API deployment
- Database backups
- Object-storage lifecycle
- Monitoring
- Logging
- Metrics
- Tracing
- Alerting
- Error reporting
- Audit-log storage

Recommend dashboards and alerts for:

- OTP failures
- API error rate
- Slow queries
- Queue backlog
- Message delivery failures
- Push failures
- Search latency
- Database health
- Storage failures
- Suspicious admin actions

---

# 28. Development roadmap

Produce a dependency-aware implementation roadmap.

Recommended phase format:

## Phase 0 — Product decisions and design completion

Resolve open decisions, legal review, responsive design, error/loading/offline states, admin requirements.

## Phase 1 — Platform foundation

Identity, OTP, users, profiles, reference data, permissions, environments, CI/CD.

## Phase 2 — Jobs and discovery

Employer organization, job lifecycle, search, filters, geolocation, public jobs.

## Phase 3 — Applications

Application creation, employer review, status history, candidate tracking.

## Phase 4 — Messaging and notifications

Realtime chat, push, in-app notifications, WhatsApp handoff.

## Phase 5 — Employer dashboard and operations

Analytics, employer web management, verification, organization users.

## Phase 6 — Admin and moderation

Admin console, reports, takedown, audit logs.

## Phase 7 — Monetization

Plans, subscriptions, payments, invoices, entitlements.

## Phase 8 — Hardening and launch

Performance, security, accessibility, load testing, monitoring, app-store release.

For every phase provide:

- Objectives
- Included modules
- Dependencies
- Deliverables
- Backend tasks
- Mobile tasks
- Web tasks
- Admin tasks
- QA tasks
- DevOps tasks
- Exit criteria
- Risks

Then break the roadmap into practical sprints with clear sprint goals and acceptance gates.

Do not estimate duration unless assumptions are explicitly stated.

---

# 29. Open decisions register

Create a prioritized decision register.

Each item must include:

- Decision ID
- Topic
- Current prototype evidence
- Why the decision matters
- Available options
- Recommended option
- Backend impact
- Mobile impact
- Web impact
- Database impact
- Security/legal impact
- Deadline before which it must be resolved
- Owner role

Include all unresolved items identified in the current handover, particularly:

- OTP rules
- Account type behavior
- Employer verification
- Application transitions
- Vacancy and automatic closure
- Paused-job visibility
- Editing jobs with applicants
- Geolocation provider
- Search behavior
- Messaging policy
- WhatsApp privacy
- Notification channels
- Pricing and payments
- Uploads
- Job sharing
- Company-profile ownership
- English/localization readiness
- PDPL/legal approval
- Offline/error states
- Responsive behavior
- Metric definitions

---

# 30. Traceability matrix

Produce a requirements traceability matrix linking:

```text
Requirement ID
Source file
Source screen
Prototype evidence
User story
Business rule
API endpoint
Database entities
Permission
UI state
Test case
Decision status
```

Every confirmed prototype feature must appear in the matrix.

Every proposed feature must show why it is required.

---

# 31. Final handoff checklist

Create a final checklist that allows the product owner to verify readiness before implementation.

The checklist must answer:

- Are all screens documented?
- Are all source controls mapped?
- Are all hidden states identified?
- Are roles and permissions approved?
- Are job/application state machines approved?
- Are validation messages approved?
- Are APIs specified?
- Is the data model reviewed?
- Are security and PDPL issues reviewed?
- Are responsive designs complete?
- Are error/loading/offline states designed?
- Is admin scope complete?
- Are monetization decisions approved?
- Are analytics definitions approved?
- Are test cases mapped?
- Is the implementation roadmap dependency-safe?

---

# 32. Output quality rules

1. Write all technical documentation in clear professional English.
2. Preserve Arabic UI labels exactly when found in the prototype.
3. Add an English explanation beside Arabic labels where helpful.
4. Use tables where comparison or traceability matters.
5. Use Mermaid for diagrams.
6. Use concrete examples.
7. Include API and JSON examples.
8. Do not omit edge cases.
9. Do not hide uncertainty.
10. Do not claim mock behavior is production-ready.
11. Do not redesign the product without documenting the reason.
12. Do not use placeholder phrases such as “etc.” where a finite list can be produced.
13. Do not compress multiple independent requirements into vague paragraphs.
14. Ensure IDs are stable and cross-referenced throughout the documents.
15. Ensure the OpenAPI file, diagrams, permissions, entity model, and user stories agree with one another.

---

# 33. Required execution sequence

Follow this sequence exactly:

1. Inspect all source files.
2. Produce the source audit.
3. Build the screen and interaction inventory.
4. Extract confirmed business logic.
5. Identify contradictions and gaps.
6. Create the open-decisions register.
7. Propose production behavior for unresolved gaps.
8. Build roles and permissions.
9. Build state machines.
10. Build the domain model and database schema.
11. Build the API specification.
12. Build design-system and responsive specifications.
13. Build security and compliance specifications.
14. Build testing and DevOps specifications.
15. Build the roadmap and sprint plan.
16. Run a consistency review across all generated files.
17. Produce the final handoff checklist.

Do not begin by generating generic architecture before inspecting the actual prototype.

---

# 34. Completion gate

The task is complete only when:

- All 9 HTML prototype files have been audited.
- Every screen and interaction has a traceable requirement.
- Confirmed and inferred behavior are clearly separated.
- All major business state machines are explicit.
- Every production API is documented.
- The database model supports all approved behavior.
- Permissions and ownership rules are explicit.
- Error, loading, offline, empty, and conflict states are specified.
- Mobile, tablet, and desktop behavior is documented.
- Admin operational needs are covered.
- Security and Saudi privacy considerations are addressed.
- A testable development roadmap exists.
- No important feature depends on an undocumented assumption.

Start by producing `00-README.md` and `02-source-audit-and-traceability.md`, then continue through the full deliverable set.
