// webpack.config.js
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    // 1. Entry point: where Webpack starts bundling
    entry: './resources/js/app.js',

    // 2. Output: where the final compiled files will go
    output: {
        path: path.resolve(__dirname, 'public/assets'),
        filename: 'app.js',
    },

    // 3. Modules & Loaders: how to handle different file types
    module: {
        rules: [
            {
                // Rule for .scss or .css files
                test: /\.(scss|css)$/,
                use: [
                    MiniCssExtractPlugin.loader, // 3. Extracts CSS into a separate file
                    'css-loader',                // 2. Translates CSS into CommonJS
                    'sass-loader',               // 1. Compiles Sass to CSS
                ],
            },
        ],
    },

    // 4. Plugins: additional build steps
    plugins: [
        // This plugin extracts CSS into a separate file specified in the 'use' rule
        new MiniCssExtractPlugin({
            filename: 'app.css',
        }),
    ],

    // Development mode setting
    mode: 'development'
};