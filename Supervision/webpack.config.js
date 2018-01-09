var webpack = require('webpack');

module.exports = {
    entry: './app.js',
    output: {path: __dirname, filename: 'bundle.js' },
    target: "node",
    node: {
        __filename: true,
        __dirname: true
    },
    module: {
        loaders: [
            {
                test: /.jsx?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                query: {
                    presets: ['es2015', 'react', 'stage-0']
                }
            },
            {
                test: /\.(?:png|jpg|svg)$/,
                loader: 'url-loader',
                query: {
                }
            }
        ]
    }
}
