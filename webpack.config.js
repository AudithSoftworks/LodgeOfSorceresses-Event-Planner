const toolset = require('./storage/build/scripts/webpack/toolset.js');

const CleanWebpackPlugin = require('clean-webpack-plugin');
const DefinePlugin = require("webpack/lib/DefinePlugin");
const ManifestPlugin = require('webpack-manifest-plugin');

const path = require('path');
const merge = require('webpack-merge');
const dirname = path.resolve();
/** @var {String} process.env.NODE_ENV */
const devMode = process.env.NODE_ENV !== 'production';

const PATHS = {
    js: path.join(dirname, 'resources', 'js'),
    scss: path.join(dirname, 'resources', 'sass'),
    css: path.join(dirname, 'resources', 'stylesheets'),
    build: path.join(dirname, 'public', 'build')
};

let common = {
    mode: devMode ? 'development' : 'production',
    entry: [
        path.join(PATHS.js, 'index.js'),
    ],
    output: {
        path: PATHS.build,
        publicPath: '/build/',
        filename: '[name].[contenthash].js',
        chunkFilename: '[name].[contenthash].js' // This is used for require.ensure. The setup will work without but this is useful to set.
    },
    plugins: [
        new ManifestPlugin({
            fileName: 'mix-manifest.json',
            publicPath: '/'
        }),
        // ensure that we get a production build of any dependencies this is primarily for React, where this removes 179KB from the bundle
        new DefinePlugin({
            'process.env.NODE_ENV': devMode ? '"development"' : '"production"'
        })
    ],
};

let config;

// Detect how npm is run and branch based on that
config = devMode
    ? merge(
        common,
        {devtool: 'eval-source-map'},
        toolset.loadersAndPluginsForVariousTypes(),
        toolset.extractBundles()
    )
    : merge(
        common,
        {devtool: 'hidden-source-map'},
        toolset.loadersAndPluginsForVariousTypes(),
        toolset.extractBundles(),
        toolset.minify()
    );

/** @var {String} process.env.npm_lifecycle_event */
if (process.env.npm_lifecycle_event !== 'watch') {
    config.plugins.push(
        new CleanWebpackPlugin([PATHS.build], {
            root: process.cwd(), // Without `root` CleanWebpackPlugin won't point to our project and will fail to work.
            watch: true,
            beforeEmit: true,
            verbose: true
        })
    );
}

module.exports = config;
