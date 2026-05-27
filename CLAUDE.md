# Project Instructions

## 10up Skills

Before writing or modifying PHP, JS, or CSS code, **always invoke the relevant 10up skill** from `.claude/skills/` to ensure code follows 10up conventions. Match the task to the skill:

- **PHP plugin code** (post types, meta, modules): `/10up-plugin-development` and `/10up-wp-framework`
- **Block development** (block.json, edit.js, save.js): `/10up-block-development`
- **Block themes** (templates, parts, theme.json): `/10up-block-themes`
- **Patterns**: `/10up-block-patterns`
- **Block extensions** (extending core blocks): `/10up-block-extensions`
- **InnerBlocks**: `/10up-inner-blocks`
- **CSS**: `/10up-css`
- **Build tooling** (webpack, 10up-toolkit): `/10up-toolkit`
- **Testing**: `/10up-testing`
- **Interactivity API**: `/10up-interactivity-api`
- **Commit messages**: `/10up-commit-messages`

If unsure which skill applies, start with `/10up-router`.

## Code Standards

- Follow WordPress Coding Standards (PHPCS). All docblocks must include a short description before any tags.
- Use tabs for indentation in PHP files.
- Run `composer run lint` to check for PHPCS violations before considering PHP work complete.
