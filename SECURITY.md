# Security Policy

## Reporting a Vulnerability

Do NOT report security issues publicly. Email the maintainer.

## ⚠️ Important Note

This repository was found to contain a hardcoded API key in git history
during a security audit on 2026-07-08. The key has been flagged for
revocation. Do NOT commit API keys, tokens, or credentials to this repository.

Use environment variables (getenv / $_ENV) for all secrets.
