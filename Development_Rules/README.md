# Development_Rules

This folder contains the core template files that should be copied to every new software project.

## Purpose

These files establish consistent development principles across all projects:
- Persistent AI instructions for coding assistants
- Standardized playbooks and methodologies
- Documentation templates and processes

## Files

- **`copilot-instructions.md`** - Persistent workspace instructions for AI coding tools (Copilot, Claude, Cursor, etc.). Copy this to `.github/copilot-instructions.md` in new projects.

- **`playbook_new.md`** - Primary playbook optimized for HTMX + LAMP SaaS development. Contains the Project Kickoff Checklist.

- **`playbook.md`** - General software development playbook with broader principles.

- **`project-starter.md`** - Master documentation explaining how to use this template system.

- **`CLAUDE.md`** - AI-specific guidance (rename to match your preferred AI tool).

## Usage

1. Copy this entire folder to any new project
2. Copy `copilot-instructions.md` to `.github/copilot-instructions.md`
3. Follow the instructions in `project-starter.md`
4. Update the playbooks with project-specific details

## Core Principles

All development should follow:
- **Simplicity**: Smallest possible changes
- **Security-First**: Input validation, CSRF protection, prepared statements
- **Documentation**: Update activity logs for every action
- **Planning**: Always use todo-driven development
- **Version Control**: Create permanent git tags after milestones

See `project-starter.md` for complete setup instructions.

---

**Last Updated**: April 13, 2026
**Version**: 1.0

This folder ensures consistent, high-quality development practices across all projects.