CHANGELOG
=========


2.0.0 (2021-XX-XX)
------------------

- Use `symfony/config` to process `theme.yml` manifests.
The YAML syntax changed.
**Upgrade path:** The `layouts` key moved one level up in the `theme.yml` syntax
- Switched to native Twig support introduced in Contao 4.12.
**Upgrade path:** Change references from `@foobar/template.html.twig` to `@Contao_Theme_foobar/template.html.twig`.

1.0.0 (2021-03-24)
------------------

No changes

0.1.0 (2021-03-13)
------------------

Initial release
