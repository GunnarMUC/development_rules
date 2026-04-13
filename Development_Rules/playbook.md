# Software Development Playbook (General)

## Introduction

This playbook distills best practices and processes from successful software projects. It has been restructured for better flow and broader applicability while preserving the core principles of simplicity, documentation, and security-first development.

### How to Use This Playbook
1. The file `.github/copilot-instructions.md` ensures these principles are automatically referenced by AI coding assistants on every task (this acts as a persistent memory mechanism)
2. Start with the Project Kickoff Checklist at the end
3. Follow sections sequentially for new projects
4. Adapt the technology examples to your stack
5. Maintain the simplicity and documentation disciplines

**Core Philosophy**: Simplicity above all. Small changes. Thorough documentation. Security by default. The persistent instruction system prevents context loss across conversations.

## 1. Project Planning & Analysis

### Initial Analysis Process
1. **Understand the Problem**: Thoroughly analyze requirements and constraints
2. **Read Existing Codebase**: Review relevant files and understand current architecture
3. **Write Detailed Plans**: Document plans in structured todo lists
4. **Plan Verification**: Review plans with stakeholders before implementation

### Todo List Best Practices
- Break down tasks into small, manageable items
- Include acceptance criteria for each task
- Mark tasks as complete with timestamps
- Add review sections summarizing changes

### Simplicity Principle
- Make every change as simple as possible
- Avoid massive or complex modifications
- Impact as little code as possible
- Prefer incremental improvements over big rewrites

## 2. Technology Stack Selection

### Core Infrastructure
- **Operating System**: Ubuntu Server LTS for stability and support
- **Web Server**: Apache 2.4 with essential modules (mod_rewrite, mod_ssl, mod_headers)
- **Database**: MariaDB/MySQL with InnoDB and UTF8MB4
- **Backend**: PHP 8.1+ with required extensions (PDO, mbstring, openssl, etc.)

### Frontend Technologies
- **CSS Framework**: Bootstrap 5 for responsive design
- **JavaScript**: HTMX and Alpine.js for modern interactivity
- **Migration Strategy**: Move from jQuery to HTMX/Alpine.js incrementally

### Key Considerations
- Choose technologies with long-term support
- Prioritize simplicity and maintainability
- Ensure compatibility across components
- Plan for progressive enhancement

## 3. Architecture & Security Design

### Database Design Principles
- Use proper foreign key constraints and indexes
- Implement multi-tenant ready schemas with audit fields
- Design for concurrent access and scalability

### User Roles & Permissions
- **Global Admin**: Full system access
- **Group Admin**: Team management within assigned groups
- **Standard User**: Limited to personal and assigned tasks

### Code Organization
- MVC-inspired structure with clear service layer
- Separate concerns (models, views, controllers, services)
- Use configuration files for environment-specific settings
- PSR-12 coding standards and comprehensive documentation

## 4. Development Process

### Authentication & Authorization
- **Password Security**: Use password_hash() with PASSWORD_BCRYPT, enforce strong password policies
- **Session Management**: Configure secure session settings (HttpOnly, Secure, SameSite, 1-hour timeout)
- **CSRF Protection**: Generate and verify CSRF tokens on all state-changing operations
- **Role-Based Access**: Implement granular permissions (Global Admin > Group Admin > User)
- **Session Regeneration**: Regenerate session IDs after login to prevent fixation attacks

### Data Protection
- **SQL Injection Prevention**: Always use prepared statements with PDO or MySQLi
- **XSS Prevention**: Escape all user output with htmlspecialchars()
- **Input Validation**: Validate and sanitize all user inputs on server-side
- **File Upload Security**: Restrict file types, sizes, and scan for malware
- **Error Handling**: Log errors internally, never expose sensitive information to users

### Infrastructure Security
- **HTTPS Enforcement**: Require SSL certificates and redirect all HTTP to HTTPS
- **Apache Configuration**: Use .htaccess to protect sensitive files and disable directory browsing
- **File Permissions**: Set appropriate permissions (755 for directories, 644 for files)
- **Environment Variables**: Store secrets in environment variables, not in code
- **Database Security**: Use dedicated database users with minimal privileges

### HTMX & Alpine.js Security
- **HTMX Headers**: Implement X-CSRF-Token headers for HTMX requests
- **Alpine.js Sanitization**: Sanitize user inputs in Alpine components
- **Request Validation**: Validate HTMX requests server-side
- **State Management**: Avoid storing sensitive data in client-side state

### Security Testing
- **Vulnerability Scanning**: Regular security audits and penetration testing
- **Dependency Updates**: Keep all libraries and frameworks updated
- **Access Logging**: Monitor and log all authentication attempts
- **Incident Response**: Have a plan for security breaches and data leaks

## 4. Development Process

### Version Control
- Commit successful changes regularly
- Use meaningful commit messages
- **Permanent Git Commits After Milestones**: Create tagged releases after completing major milestones
- Push changes to repository after validation
- Maintain clean git history
- Use git tags for version releases (e.g., v1.0.0, v1.1.0)
- Document release notes with each milestone commit

### Documentation Practices
- Document all processes and changes
- Maintain activity logs for troubleshooting
- Include user prompts and actions taken
- Create comprehensive planning documents

### Communication Guidelines
- Provide high-level explanations of changes
- Keep stakeholders informed of progress
- Document decisions and rationale
- Review and verify plans before execution

## 5. UI/UX Design Principles

### Responsive Design
- Bootstrap 5 foundation for consistency
- Mobile-first approach
- Progressive enhancement strategy
- Accessible design patterns

### Interactivity Patterns
- Server-first approach with HTMX
- Minimal client-side state with Alpine.js
- AJAX for seamless updates
- Proper loading states and error handling

### Component Design
- Unique IDs for all interactive elements
- Consistent naming conventions
- Reusable partial templates
- Clean separation of concerns

## 6. Testing & Validation

### Development Testing
- Test code changes immediately after implementation
- Validate functionality with minimal test cases
- Check for regressions in existing features
- Use automated tests where possible

### Debugging Process
- Systematic investigation of issues
- Add debug statements strategically
- Check console errors and server logs
- Isolate problems before fixing
- Document root causes and solutions

### Validation Checklist
- Code syntax validation
- Database connection testing
- UI responsiveness across devices
- Security vulnerability checks
- Performance optimization

## 7. Deployment & Infrastructure

### Server Configuration
- Apache with required modules
- PHP configuration optimization
- Database connection pooling
- SSL certificate setup
- Security headers implementation

### File Organization
- Dedicated deployment directory (e.g., html/)
- Separate configuration files
- Asset organization (CSS, JS, images)
- Proper file permissions

### Environment Management
- Development, staging, and production environments
- Environment-specific configurations
- Secure credential management
- Backup and recovery procedures

## 8. Team Collaboration & Code Review

### Workflow Management
- Clear task assignment and tracking via todo lists
- Mandatory peer code reviews for all significant changes
- Regular knowledge sharing sessions and documentation
- Retrospective meetings after major milestones

### Quality Assurance
- Code standards and static analysis
- Automated testing integration
- Documentation maintenance
- Continuous improvement processes

## 9. Maintenance, Monitoring & Evolution

### Migration Planning
- Incremental changes to minimize risk
- Maintain functionality during transitions
- Test thoroughly at each step
- Document migration processes

### Performance Optimization
- Database query optimization
- Asset minification and caching
- CDN usage for static resources
- Regular performance monitoring

### Monitoring & Observability
- Application performance monitoring
- Error tracking and alerting
- Database query analysis
- User behavior analytics

### Security Maintenance
- Regular security updates and dependency patching
- Vulnerability scanning and penetration testing
- Access log monitoring and audit trails
- Documented incident response plan

## 10. Security Best Practices (Consolidated)

### Authentication & Authorization
- **Password Security**: Use password_hash() with PASSWORD_BCRYPT, enforce strong password policies
- **Session Management**: Configure secure session settings (HttpOnly, Secure, SameSite, 1-hour timeout)
- **CSRF Protection**: Generate and verify CSRF tokens on all state-changing operations
- **Role-Based Access**: Implement granular permissions (Global Admin > Group Admin > User)
- **Session Regeneration**: Regenerate session IDs after login to prevent fixation attacks

### Data Protection
- **SQL Injection Prevention**: Always use prepared statements with PDO or MySQLi
- **XSS Prevention**: Escape all user output with htmlspecialchars()
- **Input Validation**: Validate and sanitize all user inputs on server-side
- **File Upload Security**: Restrict file types, sizes, and scan for malware
- **Error Handling**: Log errors internally, never expose sensitive information to users

### Infrastructure Security
- **HTTPS Enforcement**: Require SSL certificates and redirect all HTTP to HTTPS
- **Apache Configuration**: Use .htaccess to protect sensitive files and disable directory browsing
- **File Permissions**: Set appropriate permissions (755 for directories, 644 for files)
- **Environment Variables**: Store secrets in environment variables, not in code
- **Database Security**: Use dedicated database users with minimal privileges

### HTMX & Alpine.js Security
- **HTMX Headers**: Implement X-CSRF-Token headers for HTMX requests
- **Alpine.js Sanitization**: Sanitize user inputs in Alpine components
- **Request Validation**: Validate HTMX requests server-side
- **State Management**: Avoid storing sensitive data in client-side state

### Security Testing
- **Vulnerability Scanning**: Regular security audits and penetration testing
- **Dependency Updates**: Keep all libraries and frameworks updated
- **Access Logging**: Monitor and log all authentication attempts
- **Incident Response**: Have a plan for security breaches and data leaks

## 11. Project Kickoff Checklist

### Phase 0: Foundation Setup
- [ ] Initialize git repository and basic folder structure (`docs/`, `tasks/`, `src/`)
- [ ] Create customized copy of this playbook
- [ ] Set up secure database and configuration
- [ ] Establish activity logging and todo system
- [ ] Configure version control with proper .gitignore

### Phase 1: Core Architecture
- [ ] Implement secure authentication and session management
- [ ] Set up role-based access control
- [ ] Create base layout templates and navigation
- [ ] Establish logging, error handling, and monitoring
- [ ] Create initial tagged release (v0.1.0)

### Phase 2: Core Features
- [ ] Implement primary business modules
- [ ] Add team/collaboration capabilities
- [ ] Build dashboard and reporting features
- [ ] Implement notification system
- [ ] Conduct security and performance review

### Phase 3: Production Readiness
- [ ] Complete testing and documentation
- [ ] Set up CI/CD pipeline
- [ ] Configure monitoring and alerting
- [ ] Perform final security audit
- [ ] Deploy to production with rollback plan
- [ ] Create v1.0.0 tagged release

## Conclusion

This restructured playbook provides a solid foundation for successful software development projects. It emphasizes simplicity, security-first development, thorough documentation, and incremental progress. Use the checklist to kick off new projects and adapt the practices to your specific technology stack while maintaining the core principles of structured, high-quality development.

**Last Updated**: April 2026