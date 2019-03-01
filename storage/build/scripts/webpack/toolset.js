const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const UglifyJsWebpackPlugin = require('uglifyjs-webpack-plugin');

exports.extractBundles = function () {
    return {
        optimization: {
            splitChunks: {
                cacheGroups: {
                    styles: {
                        name: 'styles',
                        test: /\.css$/,
                        chunks: 'all',
                        enforce: true
                    }
                }
            }
        },
    };
};

exports.loadersAndPluginsForVariousTypes = function () {
    return {
        module: {
            rules: [
                {
                    test: /\.(sa|sc|c)ss$/,
                    use: [
                        'style-loader',
                        'css-loader',
                        'postcss-loader',
                        'sass-loader'
                    ]
                },
                {
                    test: /\.(gif|jpg|png)$/,
                    use: process.env.NODE_ENV === 'production' ? 'file-loader?name=[path][name].[hash].[ext]' : 'file-loader?name=[path][name].[ext]'
                },
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: [
                        {
                            loader: 'babel-loader'
                        }
                    ],
                }
            ]
        }
    };
};

exports.minify = function () {
    return {
        optimization: {
            minimizer: [
                new UglifyJsWebpackPlugin({
                    cache: true,
                    parallel: true,
                    sourceMap: true
                }),
                new OptimizeCSSAssetsPlugin({})
            ]
        }
    };
};
