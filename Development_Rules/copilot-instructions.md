---
description: "Always reference playbook_new.md and playbook.md for core development principles. This is a persistent workspace instruction that should be consulted on every coding task to maintain consistency with simplicity, security-first development, thorough documentation, todo-driven planning, and incremental changes."
applyTo: "**"
---

# Persistent Development Principles

You are working within a project that follows strict development principles defined in two key documents:

- **`Development_Rules/playbook_new.md`** — The primary, optimized playbook for HTMX + LAMP SaaS development
- **`Development_Rules/playbook.md`** — The general software development playbook

## Core Instructions

**Before starting any coding task:**
1. Read the relevant sections from `Development_Rules/playbook_new.md` and `Development_Rules/playbook.md`
2. Ensure all work follows the **Simplicity Principle** (smallest possible changes)
3. Follow the **Todo-Driven Development** process (update `tasks/todo.md`)
4. Maintain comprehensive **Activity Logging** in `docs/activity.md`
5. Apply **Security-First Development** practices from the security sections
6. Use the **Project Kickoff Checklist** and structured processes defined in the playbooks

## Key Principles to Always Enforce

- **Simplicity**: Every change should impact as little code as possible
- **Documentation**: Document all actions, decisions, and changes
- **Planning**: Create and maintain todo lists before implementation
- **Security**: Follow all security best practices (input validation, CSRF, prepared statements, etc.)
- **Incremental Development**: Prefer small, testable changes over large refactors
- **Communication**: Provide high-level explanations of changes
- **Version Control**: Create permanent git commits/tags after milestones

## Reminder Protocol

If context about these principles appears to be lost:
- Re-read `Development_Rules/playbook_new.md` (primary reference)
- Cross-reference with `Development_Rules/playbook.md`
- Re-align all work to match the documented philosophy and processes
- Update `docs/activity.md` with this realignment

This instruction file ensures continuity of development standards even when conversation context is limited. All coding tasks must align with the principles defined in the playbook files.