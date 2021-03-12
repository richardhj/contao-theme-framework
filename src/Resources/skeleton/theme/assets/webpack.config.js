const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('../public')
    .setPublicPath('/themes/my_theme')
    .setManifestKeyPrefix('')
    .addEntry('app', './js/app.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enablePostCssLoader()
    .addLoader({
        test: /\.(gif|png|jpe?g|svg)$/i,
        use: ['image-webpack-loader']
    })
    .copyFiles({
        from: './images',
        to: 'images/[path][name].[ext]',
    })
;

module.exports = Encore.getWebpackConfig();
