<h1 align="center">Contao Theme Framework</h1>

<p align="center">A new standardized and database-less way to build frontend themes in Contao.</p>

<p align="center">
<img src="https://user-images.githubusercontent.com/1284725/132962831-dcef78a7-2604-4782-aaf5-928321683059.png" alt="" width="550">
</p>

## Features

- Automatically registers themes and layouts defined via a `theme.yml` manifest file: Almost no database to maintain - easier deployment!
- Disables all redundant fields from the tl_theme and tl_layout palettes - you define all settings via the manifest file (except for the module includes).
- Registers a themes' public folder as Asset package, supports file versioning via a `manifest.json`!
- Out-of-the-box support for Symfony Encore and its `entrypoints.json`!

This extension is stable and supported from Contao >=4.13 and will be integrated into the Contao Core at some time, see https://github.com/contao/contao/issues/2781.

## Installation

```bash
composer require richardhj/contao-theme-framework
```

## Quickstart

### 1. Create a theme folder

Create a folder for your theme under `/themes` with the following structure:

```text
|- files
|- themes
 |- my_theme
  |- assets           (Optional folder, we recommend placing your CSS/JS files there)
  |- public           (Distribution folder with your CSS/JS files, will be symlinked into the web/ folder)
  |- templates        (Overridden Contao templates, for frontend modules, etc.pp.)
  |- theme.[yml|yaml] (Theme manifest)
```

If you do not use a preprocessor, you place all your CSS/JS files into the public folder.

Alternatively, copy the boilerplate folder:

```bash
cp -r vendor/richardhj/contao-theme-framework/skeleton/theme themes/my_theme
```

This command will install an opinionated starter theme pack.

> :information_source: Except for the directory structure that is predetermined, you are free in the technology you use (Encore, Webpack, Gulp, SASS, plain CSS, …).

### 2. Write the theme manifest

Write your theme manifest:

```yml
# themes/my_theme/theme.yml

theme:
  name: My cool theme

layouts:
  # "_default" is a special key.
  # Will create a "default" layout, and all other layouts will merge these settings.
  # Using this key is optional.
  # The key:value structure maps the tl_layout structure.
  _default:
    name: Default layout
    template: fe_page
    rows: 3rw

  other_layout:
    name: Other layout
    template: fe_page_2

image_sizes:
  # See https://docs.contao.org/dev/framework/image-processing/image-sizes/#size-configuration
```

In a next version, the XML format for theme manifests will be available.

### 3. Install themes

To install your new theme, run the migrate command:

```bash
./vendor/bin/contao-console contao:migrate -n
```

> :information_source: The themes will only be updated on the migrate command when making changes on the theme manifest.
> It is best to add the migrate command to your deployment script (which is a good idea anyway).

To create the symlink for the public folder, run the following command (this only needs to be done once,
and this is automatically done on `composer install`, so you usually should not be required to do this):

```bash
./vendor/bin/contao-console contao:symlinks
```

### 4. Create frontend modules and assign them to layouts

Login to the Contao backend, where you will find the new theme. Create frontend modules (if necessary)
and assign them to the layouts accordingly.

Usage
-----

### Wait – How do I build a website if all fields are disabled from tl_layout?

Good question!

The layouts in Contao are highly redundant. You don't need to configure a viewport-attribute,
body-class, etc. via the layout. Instead, just put those changes directly into your fe_page template.

Instead of selecting CSS files and JS files via the layout (which is limited to files within /files),
directly include your files via the {{asset}} insert tag or twig function (or via Encore, see below).
Don't use the "custom `<head>`" or "custom `<script>`" settings in your layout. It's hard to maintain and
keep track of. Instead, put those things directly into the template.

Considering these matters of maintainability, it's easier not to configure any settings in the layout.
Assigning the modules to the layout sections is all you do in the layouts.

If your layouts use rows and cols, set the corresponding config in the `theme.yml`. For instance,
`rows: 3rw` enables the header and footer section.

_Using Twig templates (optional):_

In case your header and footer sections only contain static content, you do not have to configure
those sections in your layout. Just include those sections via Twig includes. For navigation menus,
you can use a Knp Menu (see below). For a user menu, you can use the [{{ app.user }} variable](https://symfony.com/doc/current/templates.html#the-app-global-variable).
You will be surprised how not using modules for the layout significantly enhances maintainability.

### Assets

Each theme's public folder (i.e., `/themes/my_theme/public`) is registered as `assets.packages`
component. [Learn more about the Asset component.](https://symfony.com/doc/current/components/asset.html)

You can reference any file inside the theme's public folder with the `{{asset}}` insert tag
or corresponding twig function. The theme name equals the folder name.

When you use a `manifest.json` inside the public folder, it will be handled by Symfony's Asset
component. Make sure to use `setManifestKeyPrefix('')` in your `webpack.config.js` file then.

Example:

```html
<!-- HTML5 -->
{{asset::images/logo.svg::my_theme}}

<!-- Twig -->
{{ asset('images/logo.svg', 'my_theme') }}
```

> :information_source: The simplest way to include a CSS file to the page is to modify the `fe_page.html5` template and include the `{{asset::css/custom.css::my_theme}}` insert tag. When you want to use Twig templates and Encore too, see :arrow_down:

### Encore

When using Encore, you can use the following Twig functions to inject
your CSS and JS files to the page template (defined via the `entrypoints.json` file):

```twig
{{ theme_link_tags('app') }}
{{ theme_script_tags('app') }}
```

> :information_source: The name "app" matches the name of the entry defined in the [webpack.config.js](https://github.com/richardhj/contao-theme-framework/blob/528dfb7085b0d35036ed771cb0563a6b9f3a74ac/skeleton/theme/assets/webpack.config.js#L7). You can have multiple entrypoints per theme.

You can find out more about Encore under https://symfony.com/doc/current/frontend.html.

### Custom layout sections

[Custom layout areas](https://docs.contao.org/manual/en/layout/theme-manager/manage-page-layouts/#custom-layout-areas) in the layout work as follows.

First, define the sections in the layout:

```yaml
# themes/my_theme/theme.yml
layouts:
  _default:
    name: Default layout
    sections:
      - title: Bereich A
        id: custom1
        position: manual
        template: block_section
      - title: Bereich B
        id: custom2
        position: manual
        template: block_section
```

Then, add the section(s) to the page layout (i.e., `fe_page`):

```twig
{# themes/my_theme/fe_page.html.twig #}

<div>
  {{ section.invoke('custom1') }}
</div>
<div>
  {{ section.invoke('custom2') }}
</div>
````


```php
<!-- themes/my_theme/fe_page.html5 -->

<div>
  <?php $this->section('custom1') ?>
</div>
<div>
  <?php $this->section('custom2') ?>
</div>
````

### Migrate to theme-framework

You can migrate your existing theme and layouts to the theme-framework.

Before running `contao:migrate`:

1. Add a new column named `alias` to the `tl_theme` table and set `'alias' = 'my_theme'`
(where `my_theme` matches the name of your theme folder).

2. Add a new column named `alias` to the `tl_layout` table and set `'alias' = '_default'`
(where `_default` matches the name of your layout defined via the manifest file).

3. All layout settings defined via the manifest file will be overridden in the
`contao:migrate` command. Existing settings won't be touched.

## Best Practices

### Do not rename the theme folder

Renaming the theme folder will create a new Contao theme in the database internally.
You'll need to re-assign the layouts to the pages.

### Use twig templates

You can use Twig or PHP templates by your preference. As already mentioned,
your templates belong to the `templates` folder.

For Twig templates, suffix your file with `.html.twig`, i.e., `fe_page.html.twig`.
For PHP templates, use the default naming, i.e., `fe_page.html5`.

Twig templates in the theme folder use the namespace `@Contao_Theme_<name>`, in a way that the
template `/themes/my_theme/templates/fe_page.html.twig` can be referenced as `@Contao_Theme_MyTheme/fe_page.html.twig`.

> :information_source: Note that the "theme slug" for the Twig namespace will be transformed to CamelCase. If your theme
> folder is named `my_theme`, the Twig namespace will be `@Contao_Theme_MyTheme`.

You can place twig templates also in global (non theme-related) folders -- whatever feels right for you.

| File                                           | Twig namespace and reference               | Prio (first wins) |
|:-----------------------------------------------|:-------------------------------------------|:------------------|
| `/themes/my_theme/templates/fe_page.html.twig` | `@Contao_Theme_MyTheme/fe_page.html.twig`  | 1                 |
| `/templates/fe_page.html.twig`                 | `@Contao_Global/fe_page.html.twig`         | 2                 |
| `/contao/templates/fe_page.html.twig`          | `@Contao_App/fe_page.html.twig`            | 3                 |

Read more about the usage of Twig templates in Contao under <https://docs.contao.org/dev/framework/templates/twig/>.

### Use Webpack Encore to compile your theme assets

The skeleton theme comes with a pre-defined `webpack.config.js` file. The configuration
will automatically process your asset files from the `assets` folder and generate the
bundled files into the `public` folder.

Webpack Encore will also provide an `entrypoints.json` in the public folder. This helps
to easily add the correct JS and CSS files to the current page (see above for usage).

### Use a KnpMenu for navigation modules

With a KnpMenu you are much more flexible in outputting a navigation wherever you need it on the page.

See https://github.com/richardhj/contao-knp-menu for more information.

### Git-Ignore the public folder

The distributed theme files inside the public folder usually are versioned and contain
duplicated information so that you do not want to check in those files to version control.
Instead, you want to build the theme (`yarn run prod`) before deploying.

> :information_source: The [.gitignore file](skeleton/theme/.gitignore) of the skeleton theme may become handy.

### Do not deploy the assets folder

The assets folder with the source files (if present) should be excluded from the deployment
because it most likely contains the node_modules folder next to the source folder.
In contrast, all other files, like the theme.yml manifest and public and templates folders
need to be uploaded when deploying.

### Use yarn workspaces to manage multiple themes

You can make use of [yarn workspaces](https://classic.yarnpkg.com/en/docs/workspaces/).
This will allow you to run the build command once when having multiple themes in use:

```json
// /package.json

{
  "private": true,
  "workspaces": ["themes/*/assets"],
  "scripts": {
    "prod": "yarn workspaces run prod",
  }
}
```

You then will be able to run `yarn run prod` from the root directory.

### Uninstalling

You wonder what happens to your themes when you uninstall the extension?

First, all your themes, layouts, and image size configurations stay in Contao. They won't get removed.

Second, templates under `/themes/foobar/templates` won't use the namespace `@Contao_Theme_Foobar` anymore but the namespace `@Contao_Theme__themes_foobar_templates`. This may break your website. For this to fix, move the templates folder to `/templates/Foobar` (from the project root). The Twig namespace stays intact.

Third, you lose the Encore and assets integration. This means that using the asset (`{{ asset() }}`) function or insert tag (`{{asset::*}}`) will fail. Further, the twig functions `theme_link_tags()` and `theme_script_tags()` will become unavailable. For this to fix, you have to include your CSS/JS files by directly referencing to them.

### Use Symfony UX

// TODO
