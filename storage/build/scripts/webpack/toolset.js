const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require('terser-webpack-plugin');

exports.extractBundles = function () {
    return {
        optimization: {
            splitChunks: {
                chunks: 'all'
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
                new TerserPlugin({
                    cache: true,
                    parallel: true,
                    sourceMap: true,
                    terserOptions: {
                        output: {
                            comments: false,
                        },
                        sourceMap: {
                            url: 'inline'
                        }
                    }
                }),
                new OptimizeCSSAssetsPlugin({})
            ]
        }
    };
};
