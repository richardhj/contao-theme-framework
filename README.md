# Contao Theme Framework

A new standardized and database-less way to build frontend themes in Contao.

Proposal for https://github.com/contao/contao/issues/2781

## Features

- Automatically registers themes and layouts defined via a `theme.yml` manifest file: Almost no database do maintain - easier deployment!
- Disables all redundant fields from the tl_theme and tl_layout palettes - you define all settings via the manifest file (except for the module includes).
- Registers a themes' public folder as Asset package, supports file versioning via a `manifest.json`!
- Out-of-the-box support for Symfony Encore and its `entrypoints.json`!

## Provide a theme

### 1. Create a theme folder

Create a folder for your theme under `/themes` with the following structure:

```text
|- files
|- themes
 |- my_theme
  |- assets          (Optional folder, we recommend placing your CSS/JS files there)
  |- public          (Distribution folder with your CSS/JS files, will be symlinked into the web/ folder)
  |- templates       (Overridden Contao templates, for frontend modules, etc.pp.)
  |- theme.[yml|xml] (Theme manifest)
```

If you do not use a preprocessor, you place all your CSS/JS files into the public folder.

Alternatively, copy the boilerplate folder:

```bash
cp -r vendor/richardhj/contao-theme-framework/src/Resources/skeleton/theme themes/my_theme
```

This command will install an opinionated starter theme pack.

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

You can write the mainfest in YAML or XML format, as you prefer.

### 3. Install themes

To install your new theme, run the migrate command:

```bash
./vendor/bin/contao-console contao:migrate -n
```

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
body-class, etc. via the layout. Just put those changes directly into your fe_page template.

Instead of selecting CSS files and JS files via the layout (which is limited to files within /files),
directly include your files via the {{asset}} insert tag or twig function (or via Encore, see below).
Don't use the "custom `<head>`" or "custom `<script>`" settings in your layout. Its hard to maintain and
keep track of. Put those things directly into the template.

Considering these matters of maintainability, it's easier to not configure any settings in the layout.
Assigning the modules to the layout sections is all you do in the layouts.

If your layouts uses rows and cols, set the corresponding config in the `theme.yml`. For instance,
`rows: 3rw` enables the header and footer section.

_Using Twig templates (optional):_

In case your header and footer sections only contain static content, you do not have to configure
those sections in your layout. Just include those sections via Twig includes. For navigation menus,
you can use a Knp Menu (see below). For a user menu, you can use the [{{ app.user }} variable](https://symfony.com/doc/current/templates.html#the-app-global-variable).
You will be surprised, how not using modules for the layout significantly enhances maintainability.

### Assets

Each theme's public folder is registered as `assets.packages` component.
[Learn more about the Asset component.](https://symfony.com/doc/current/components/asset.html)

You can reference any file inside the theme's public folder with the `{{asset}}` insert tag
or corresponding twig function. The theme name corresponds to the package name.

A `manifest.json` inside the public folder will be respected.
Make sure to use `setManifestKeyPrefix('')` in your `webpack.config.js` file then.

Example:

```html
<!-- HTML5 -->
{{asset::images/logo.svg::my_theme}}

<!-- Twig -->
{{ asset('images/logo.svg', 'my_theme') }}
```

### Encore

When using Encore, you can use the following Twig functions to inject
your CSS and JS files to the page template (defined via the `entrypoints.json` file):

```twig
{{ theme_link_tags('app') }}
{{ theme_script_tags('app') }}
```

You can find out more about Encore under https://symfony.com/doc/current/frontend.html.

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

For Twig templates, the bundle internally uses namespaced twig paths
so that `fe_page.html.twig` templates from different themes do not conflict.

Twig templates in the theme folder use the namespace `@Contao_Theme_<name>`, in a way that the
template `/themes/foobar/templates/fe_page.html.twig` can be referenced as `@Contao_Theme_foobar/fe_page.html.twig`.

You can place twig templates also in global (non theme-related) folders -- whatever feels right for you.

| File                                         | Twig namespace and reference              | Prio (first wins) |
|:---------------------------------------------|:------------------------------------------|:------------------|
| `/themes/foobar/templates/fe_page.html.twig` | `@Contao_Theme_foobar/fe_page.html.twig`  | 1                 |
| `/templates/fe_page.html.twig`               | `@Contao_Global/fe_page.html.twig`        | 2                 |
| `/contao/templates/fe_page.html.twig`        | `@Contao_App/fe_page.html.twig`           | 3                 |

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

### Do not deploy the assets folder

The assets folder with the source files (if present) should be excluded from the deployment
because it most likely contains the node_modules folder next to the source folder.
In contrast, all other files, like the theme.yml manifest and public and templates folders
need to be uploaded when deploying.

### Use yarn webspaces to manage multiple themes

You can make use of [yarn workspaces](https://classic.yarnpkg.com/en/docs/workspaces/).
This will allow you to run the build command once when having multiple themes in use:

```json
// /package.json

{
  "private": true,
  "workspaces": ["themes/*/assets"],
  "scripts": {
    "prod": "yarn workspaces run prod",
  },
}
```

You then will be able to run `yarn run prod` from the root directory.

### Use Symfony UX

// TODO
