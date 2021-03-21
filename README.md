# Contao Theme Framework

A new standardized and database-less way to build frontend themes in Contao.

## Provide a theme

### 1. Create a theme folder

Create a folder for your theme under `/themes` with the following structure: 

```text
|- files
|- themes
 |- my_theme
  |- assets     (Optional folder, we recommend to place your CSS/JS files there)
  |- public     (Distribution folder with your CSS/JS files, will be symlinked into the web/ folder)
  |- templates  (Overridden Contao templates, for frontend modules etc.pp.)
  |- theme.yml  (Theme manifest)
```

If you do not use a preprocessor, you place all your CSS/JS files into the public folder.

Alternatively, you can use the following command, where `my_theme` is the name of your theme:

```bash
./vendor/bin/contao-console contao:theme:create my_theme
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
    # Will create a "default" layout and all other layouts will merge these settings.
    # Using this key is optional.
    _default:
      name: Default layout
      template: fe_page
      rows: 3rw

    other_layout:
      title: Other layout
      template: fe_page_2

  image_sizes:
    # See https://docs.contao.org/dev/framework/image-processing/image-sizes/#size-configuration
```

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

### 4. Create frontend modules and assign to layouts

Login to the Contao backend where you will find the new theme. Create frontend modules (if necessary)
and assign them to the layouts accordingly.

## Best Practices

### Do not rename the theme folder

Renaming the theme folder will create a new Contao theme in the database internally.
You'll need to re-assign the layouts to the pages.

### Use twig templates

You can Twig or PHP templates by your preference. As already mentioned,
your templates belong to the `templates` folder.

For Twig templates, suffix your file with `.html.twig`, i.e., `fe_page.html.twig`.
For PHP templates, use the default naming, i.e., `fe_page.html5`.

For Twig templates, the bundle internally makes use of namespaced twig paths,
so that `fe_page.html.twig` templates from different themes do not conflict.

### Use Webpack Encore to compile your theme assets

The skeleton theme comes with a pre-defined `webpack.config.js` file. The configuration
will automatically process your asset files from the `assets` folder and generate the 
bundled files into the `public` folder.

Webpack Encore will also provide an `entrypoints.json` in the public folder. To inject
your CSS and JS files to the page template use the following twig functions:

```twig
{{ theme_link_tags('app') }}
{{ theme_script_tags('app') }}
```

You can find out more about Encore under https://symfony.com/doc/current/frontend.html.

### Use a KnpMenu for navigation modules

With a KnpMenu you are much more flexible in outputting a navigation whereve you need it on the page.

See https://github.com/richardhj/contao-knp-menu for more informaiton.

### Git-Ignore the public folder

The distributed theme files inside the public folder usually are versioned and contain
duplicated information so that you do not want to check in those files to version control.
Instead, you want to build the theme (`yarn run prod`) before deploying.

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
