# HTMX + LAMP SaaS Development Playbook

## Introduction

This playbook provides a complete, battle-tested framework for building secure, maintainable SaaS applications using the **HTMX + LAMP stack**. It combines the simplicity principles from your current project with modern development practices.

### How to Use This Playbook
1. The file `.github/copilot-instructions.md` ensures these principles are automatically referenced by AI coding assistants on every task
2. Start with the **Project Kickoff Checklist** (section 12)
3. Follow the sections sequentially for new projects
4. Adapt technology choices while maintaining the core principles of simplicity, security, and documentation
5. Use the todo list structure and activity logging religiously

**Core Philosophy**: Simplicity above all. Small, incremental changes. Comprehensive documentation. Security-first development. The persistent instruction system in `.github/copilot-instructions.md` acts as a safeguard against context loss.

## 1. Project Planning & Analysis

### Initial Analysis Process
1. Thoroughly understand the problem and constraints
2. Read relevant codebase and documentation
3. Create a detailed plan in `tasks/todo.md`
4. Verify the plan with stakeholders before coding

### Todo List Best Practices
- Break work into small, verifiable tasks
- Include acceptance criteria
- Mark items complete with dates and summaries
- Add a **Review** section at the bottom of each todo file

### Simplicity Principle
- Every change should be as small as possible
- Minimize impact on existing code
- Prefer incremental migration over big rewrites
- Document *why* a change was made

### Process Documentation
- Append all actions to `docs/activity.md`
- Include original prompts and decisions
- Maintain a living knowledge base

## 2. Technology Stack Selection

### Core LAMP Infrastructure
- **OS**: Ubuntu 22.04 LTS Server
- **Web Server**: Apache 2.4+ (mod_rewrite, mod_ssl, mod_headers)
- **Database**: MariaDB 10.6+ with InnoDB + UTF8MB4
- **Language**: PHP 8.2+ with PDO, mbstring, openssl, etc.

### Modern Frontend
- **HTMX** for server-driven interactivity
- **Alpine.js** for lightweight client behavior
- **Bootstrap 5** for responsive UI components
- **Tailwind CSS** (optional) for utility-first styling

### Development & Operations
- **Version Control**: Git with semantic commits and tags
- **CI/CD**: GitHub Actions or GitLab CI for PHP testing and deployment
- **Monitoring**: Prometheus + Grafana or New Relic
- **Logging**: Centralized application and error logging

**Key Rule**: Choose tools with excellent documentation and long-term support.

## 3. Architecture & Security Design

### Database Design
- Proper foreign keys and indexes
- Multi-tenant ready schema design
- Audit fields (`created_at`, `updated_at`, `created_by`)
- Use views and stored procedures judiciously

### User Roles & Permissions
- **Global Admin**: Full system access
- **Group/Team Admin**: Team-scoped management
- **Standard User**: Personal and assigned resources only

### Security Architecture (Core)
- Password hashing with `password_hash(PASSWORD_BCRYPT)`
- Secure session configuration (HttpOnly, Secure, SameSite=Strict)
- CSRF protection on all mutating requests
- Prepared statements for all database access
- Comprehensive input validation and output escaping
- Rate limiting and account lockout policies

## 4. Development Process

### Version Control & Milestones
- Commit frequently with clear messages
- **Create permanent git tags after every major milestone** (`git tag -a v1.2.0 -m "Release notes"`)
- Push changes and tags to remote repository
- Maintain clean history with meaningful commits

### Documentation Practices
- Live `docs/activity.md` with every prompt and action
- Architecture decision records (ADRs) for significant choices
- API documentation and inline code comments

### Communication Guidelines
- High-level summaries of changes
- Regular stakeholder updates
- Document both successes and lessons learned

## 5. UI/UX & Frontend Principles

- Mobile-first responsive design with Bootstrap 5
- Progressive enhancement (HTML-first, then HTMX/Alpine)
- Unique `id` attributes on all major interactive elements
- Server-first approach: use HTMX for most dynamic behavior
- Clean partial templates and reusable components
- Consistent visual language and interaction patterns

## 6. Testing, Validation & Quality Assurance

### Testing Strategy
- Unit tests for business logic (PHPUnit)
- Integration tests for API endpoints
- Manual testing of HTMX interactions
- Security and performance testing

### Validation Checklist
- Syntax and static analysis
- Cross-browser and device testing
- Security vulnerability scanning
- Load and performance testing

### Code Quality
- PSR-12 coding standard
- Comprehensive error handling and logging
- Regular code reviews

## 7. Deployment & Infrastructure

### Server Setup
- Dedicated `html/` directory as web root
- Secure `.htaccess` rules protecting config files
- Proper file permissions (755 dirs, 644 files)
- SSL certificates with auto-renewal
- Environment-specific configuration

### CI/CD Pipeline
- Automated tests on pull requests
- Build and deployment automation
- Blue-green or rolling deployment strategy
- Automated rollback capability

## 8. Team Collaboration & Code Review

- Clear task assignment and tracking
- Mandatory code reviews for all changes
- Pair programming for complex features
- Knowledge sharing through documentation and demos
- Regular retrospectives

## 9. Maintenance, Monitoring & Evolution

### Monitoring & Observability
- Application performance monitoring
- Error tracking and alerting
- Database query performance analysis
- User behavior analytics

### Maintenance Practices
- Regular dependency updates
- Incremental migration strategies
- Performance optimization cycles
- Security patching schedule

### Evolution Strategy
- Feature flags for safe experimentation
- Backward compatibility during migrations
- Regular technical debt reduction sprints

## 10. Security Best Practices (Deep Dive)

### Authentication & Authorization
- Secure session management with regeneration
- Strong password policies and hashing
- Role-based access control with team isolation
- Remember-me tokens with secure cookies

### Data Protection
- Prepared statements everywhere
- Output escaping (`htmlspecialchars()`)
- File upload validation and malware scanning
- Secure error handling (never leak details)

### Infrastructure & HTMX Security
- CSRF tokens in HTMX headers
- `.htaccess` protection for sensitive files
- Environment variable secrets management
- Regular security audits and penetration testing

### Incident Response
- Documented breach response plan
- Comprehensive audit logging
- Monitoring for suspicious activity

## 11. Project Kickoff Checklist

### Phase 0: Setup
- [ ] Create project repository and clone
- [ ] Initialize `docs/`, `tasks/`, and `html/` structure
- [ ] Set up database and run schema scripts
- [ ] Create `playbook.md` copy and customize
- [ ] Configure git, CI/CD, and monitoring

### Phase 1: Foundation
- [ ] Implement secure authentication system
- [ ] Set up role-based access control
- [ ] Create base layout with header, sidebar, footer
- [ ] Establish activity logging and todo system
- [ ] Configure HTMX + Alpine.js + Bootstrap

### Phase 2: Core Modules
- [ ] Implement team management
- [ ] Build task management (list, kanban, calendar views)
- [ ] Add dashboard and reporting
- [ ] Implement notifications system

### Phase 3: Polish & Deploy
- [ ] Comprehensive testing and security review
- [ ] Performance optimization
- [ ] Documentation completion
- [ ] Production deployment with monitoring
- [ ] Create initial tagged release

## Conclusion

This playbook represents the evolution of your current project's successful patterns into a complete, professional development framework. 

**Key Mantra**: *Simple, Secure, Documented, Incremental.*

Follow this playbook consistently and you will build maintainable, secure, and scalable SaaS applications with the HTMX + LAMP stack. Update it as you discover new best practices.

---

**Last Updated**: April 2026
**Version**: 1.0