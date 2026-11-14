# Notes for Mistral sessions

WordPress plugin repo (`opeptides/`): a peptide encyclopaedia, Convipen 3 ml cartridge
calculator, per-user stock &amp; cycle diary, and a liability banner on every page.
See README.md for the shortcodes and the calculator maths.

## Working conventions

- Work happens on a `vibe/<slug>` branch and is delivered through a pull request;
  CI (`.github/workflows/php-lint.yml`) lints the plugin PHP on 8.2/8.3/8.4.
- Front-end assets are cache-busted by file mtime, so no version bump is needed
  when editing `assets/opeptides.css` or `assets/opeptides.js`.
- The site this deploys to is behind Elementor with page caching: after a plugin
  file change, the deploy also needs `wp cache flush --allow-root --path=<wp>`
  and a hard refresh before the change is visible.

## Francis's preferences

- **Commands for the terminal must be single-line strings**, chained with `&&`,
  never multi-line blocks. Multi-line pastes fragment bash history, so up-arrow
  recall then yields one orphaned line at a time and the terminal becomes
  unusable for recall. One logical operation = one line, e.g.:

  ```bash
  cd /tmp/peptides-r-us && git pull && rm -rf /var/www/html/wordpress/wp-content/plugins/opeptides && cp -r opeptides /var/www/html/wordpress/wp-content/plugins/ && chown -R www-data:www-data /var/www/html/wordpress/wp-content/plugins/opeptides
  ```
