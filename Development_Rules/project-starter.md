# Project Starter Template

This document describes the **Development_Rules** template that should be copied into every new software project.

## Files to Copy

Copy the entire `Development_Rules/` folder into every new project. This ensures consistent development principles across all your work.

### Core Files (Always Copy)

1. **`Development_Rules/copilot-instructions.md`**
   - **Purpose**: Persistent workspace instruction for AI coding assistants
   - **Critical**: This file tells AI tools to always consult the playbooks
   - **Location**: Copy to `.github/copilot-instructions.md` in new projects (or keep in Development_Rules/)

2. **`Development_Rules/playbook_new.md`**
   - Primary playbook optimized for HTMX + LAMP SaaS development
   - Contains the Project Kickoff Checklist and specific methodology

3. **`Development_Rules/playbook.md`**
   - General software development playbook
   - Foundation that can be adapted for any technology stack

4. **`Development_Rules/CLAUDE.md`** (or rename to `CURSOR.md`, `COPILOT.md`, etc.)
   - AI-specific guidance for the coding assistant
   - Update the content to match your preferred AI tool

## Recommended Project Structure

```bash
new-project/
├── Development_Rules/              # ← Copy this entire folder
│   ├── copilot-instructions.md
│   ├── playbook_new.md
│   ├── playbook.md
│   └── CLAUDE.md
├── .github/
│   └── copilot-instructions.md     # Symlink or copy from Development_Rules
├── docs/
│   ├── activity.md
│   └── planning.md
├── tasks/
│   └── todo.md
├── src/ or html/                   # Your main code directory
├── README.md
└── [other project files]
```

## Setup Steps for New Projects

1. **Copy the entire `Development_Rules` folder** into your new project
2. **Copy `Development_Rules/copilot-instructions.md` to `.github/copilot-instructions.md`**  
   *(This is the critical step that activates the persistent memory system - see detailed explanation below)*
3. **Update the playbooks** with project-specific information
4. **Create initial todo list** in `tasks/todo.md`
5. **Initialize activity log** in `docs/activity.md`
6. **Follow the Project Kickoff Checklist** in `playbook_new.md`

## Detailed Explanation of Step 2: Activating Persistent Memory

### What does "Copy `copilot-instructions.md` to `.github/copilot-instructions.md`" mean?

This is the **most important technical step**. Here's what happens:

1. **Special Location**: VS Code, GitHub Copilot, Cursor, and other AI coding tools automatically look for a file at `.github/copilot-instructions.md`

2. **Automatic Loading**: When this file exists with the proper YAML frontmatter (`applyTo: "**"`), the AI coding assistant **automatically includes it in its context** for every interaction in that workspace

3. **Persistent Memory**: This file contains instructions that tell the AI:
   - "Always read `Development_Rules/playbook_new.md` and `Development_Rules/playbook.md` before starting any task"
   - "Follow these specific development principles"
   - "Use the Reminder Protocol if context appears lost"

4. **Why it matters**: Without this file in the `.github/` location, the AI has no persistent memory between conversations. With it, the development principles become part of the workspace itself.

### Technical Details

- The file contains YAML frontmatter that VS Code/Copilot reads
- `applyTo: "**"` means "apply to all files in this workspace"
- The `description` field helps the AI understand when to load these instructions
- This is the standard, recommended way to create "workspace memory" for AI coding assistants

## How the System Works

- The `.github/copilot-instructions.md` file acts as **persistent memory** for the workspace
- AI coding assistants are instructed to read the playbooks before every task
- If context is lost, the "Reminder Protocol" in the instruction file activates
- All development follows the principles of **simplicity**, **security-first development**, **thorough documentation**, and **todo-driven planning**

## Customization

When starting a new project:
- Update the playbooks with technology-specific guidance
- Modify the kickoff checklist for your specific needs
- Keep the core principles intact

## Core Principles (Summary)

- **Simplicity**: Smallest possible changes
- **Security-First**: Input validation, CSRF, prepared statements, etc.
- **Documentation**: Update `docs/activity.md` for every action
- **Planning**: Always use `tasks/todo.md` with review sections
- **Version Control**: Create permanent git tags after milestones
- **Communication**: High-level explanations of changes

---

**Created**: April 13, 2026
**Purpose**: Standard template for consistent development practices across all projects
**Version**: 1.0

**Next Step**: Copy the `Development_Rules/` folder into your new projects and update the AI instruction file to point to the local playbooks.