CHANGELOG
=========


2.0.0 (2021-09-16)
------------------

### Breaking changes

- Use `symfony/config` to process `theme.yml` manifests. The YAML syntax changed.  
**Upgrade path:** Move the `layouts` key one level up in the `theme.yml` syntax (there is one migration that handles this).
- Switched to native Twig support introduced in Contao 4.12.  
**Upgrade path:** Change references from `@foobar/template.html.twig` to `@Contao_Theme_Foobar/template.html.twig`.

### Fixes

- Use the configured web directory, other than hardcode it to `/web`
- Process image_sizes config of the manifest file and persist the config in tl_image_size 

1.0.0 (2021-03-24)
------------------

No changes

0.1.0 (2021-03-13)
------------------

Initial release
