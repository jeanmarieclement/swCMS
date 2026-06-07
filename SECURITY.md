# Security Policy

## Supported Versions

| Version | Supported |
| ------- | --------- |
| 1.0.x   | Yes       |

## Reporting a Vulnerability

**Do NOT open a public GitHub issue for security vulnerabilities.**

Report vulnerabilities privately via:
- [GitHub Security Advisories](https://github.com/jeanmarieclement/swCMS/security/advisories/new)
- Email: jmclement64@gmail.com

Please include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact

**Response time**: within 7 days. You will receive an acknowledgment and a timeline for the fix.

## Known Limitations

- `unsafe-inline` and `unsafe-eval` are present in the CSP due to Smarty template engine and TinyMCE editor requirements. See `app/Config/security.php` for details.
- Password reset emails require SMTP configuration (not included by default). See `app/controllers/Frontend/AuthController.php`.
