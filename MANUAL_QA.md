# Reservix Manual QA Checklist

Use seeded staging data from `php artisan db:seed --force` before running this checklist.

## Pre-Flight

- Confirm `php artisan reservix:health-check` returns all `PASS`.
- Confirm `GET /up` returns HTTP 200.
- Confirm a queue worker is running for the configured `QUEUE_CONNECTION`.
- Confirm SMTP inbox access is available in Mailtrap or the chosen staging provider.

## Customer Booking

- Open each public booking page:
  - `/b/maison-kinks-braids/book`
  - `/b/northside-fade-club/book`
  - `/b/sparkle-move-services/book`
- Create a valid booking for each business.
- Confirm the API response is successful and the confirmation page loads.
- Confirm the new booking appears in the correct business dashboard under `/app/bookings`.
- Confirm the chosen service, staff member, date, and timeslot are saved correctly.
- Use the `Modifier` actions to revisit service, date, and slot without losing unrelated customer input.
- Change the date while availability is loading and confirm results from the previous date never replace the current date.
- Confirm the active step, completed steps, and appointment summary remain synchronized.

## Provider Dashboard

- Sign in as each business owner on `/app/login`.
- Confirm the dashboard only shows that tenant’s bookings, services, staff, and schedules.
- Create a booking from the provider dashboard.
- Edit an existing booking and save a valid change.
- Confirm invalid slot edits are rejected when they overlap another booking.

## Emails

- Confirm the customer receives a booking confirmation email.
- Confirm the business inbox receives a new booking notification email.
- Open the customer email and verify the confirmation link resolves correctly.
- Open the cancellation link from the customer email and verify it resolves correctly.
- Confirm sender name and address match staging `MAIL_FROM_*` values.
- Confirm emails land in Mailtrap or the configured staging inbox instead of real customer mailboxes.

## Cancellation

- Cancel a future confirmed booking through the cancellation link.
- Confirm the status becomes `canceled`.
- Confirm the cancellation page shows a successful result.
- Retry the same cancellation link and confirm the app rejects or safely handles the already-canceled booking.
- Test an expired cancellation link if seeded or manually prepared and confirm the app rejects it.
- If cancellation emails already exist outside this scope, confirm they are delivered. If they do not exist, record that as expected for the current MVP.

## Status Actions

- From the provider dashboard, mark an eligible booking as `no_show`.
- Confirm `no_show` cannot be applied before the appointment start time.
- Mark an eligible booking as `completed`.
- Confirm `completed` cannot be applied before the appointment end time.
- Cancel a confirmed booking from the dashboard and confirm it cannot transition to another status afterward.

## Double Booking Protection

- Book a slot for a staff member on a valid date and time.
- Attempt to create a second booking for the same staff member that overlaps the first booking.
- Confirm the overlapping booking is rejected on the public flow.
- Confirm the overlapping booking is rejected on the provider dashboard create flow.
- Confirm editing an existing booking into an occupied slot is rejected.

## Tenant Isolation

- While logged in as each owner, confirm records from the other two businesses never appear in `/app`.
- Attempt a booking request using a service from one business and staff from another.
- Confirm cross-tenant combinations are rejected.
- Confirm booking confirmation and cancellation pages only work for the correct business slug and booking.

## Inactive Services And Staff

- Mark a service inactive and confirm it is no longer bookable publicly.
- Mark a staff member inactive and confirm they are no longer bookable publicly.
- Confirm provider-side create or edit flows reject inactive catalog entries where validation applies.

## Mobile UI

- Test the public booking flow on a narrow mobile viewport.
- Confirm service selection, date selection, slot selection, and customer form fields remain usable without horizontal overflow.
- Confirm only the active booking step is expanded and completed steps can be reopened.
- Confirm date and slot buttons expose a visible selected state and remain keyboard accessible.
- Confirm confirmation and cancellation pages remain readable on mobile.
- Test `/app/login` and `/app/bookings` on mobile width for basic usability.

## Regression Notes To Capture

- Exact URL tested
- Tenant used
- Seed account used
- Browser and device width
- Whether the issue is reproducible
- Screenshot or Mailtrap message ID when relevant
