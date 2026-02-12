# Next Steps (Execution Order)

1. Scaffold Laravel into repo root (`composer create-project laravel/laravel .`).
2. Add Filament panels (admin + organizer).
3. Add migrations for:
   - towns
   - locations (timezone)
   - categories
   - event_series (rrule, published_data, draft_data, status, premium fields)
   - featured_items
   - ads
4. Implement workflows:
   - Free submission + email verify
   - Organizer submission + Stripe checkout
   - Admin approval/rejection
   - Organizer edits => draft => re-approval while old published snapshot stays live
5. Public browse UI (list + filters + detail).
6. Recurrence expansion service (range-limited).
7. ICS endpoints.
