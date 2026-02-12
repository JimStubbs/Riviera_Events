# Riviera Events

Standalone, embeddable events calendar platform for the Riviera Maya.

## Target

- Production domain: `calendar.puertoaventurasliving.com`

## Notes

This is intended to run as a **standalone Laravel app** on a subdomain and be embedded into WordPress/Beehiiv/etc via an iframe (MVP).

### Hosting requirements

- PHP 8.2+
- MySQL
- Ability to point the subdomain document root at Laravel's `/public` directory
- SMTP credentials for verification emails
- (Optional but recommended) Cron access for scheduled/queued jobs

## Docs

- See `docs/PRD-v2.md`
