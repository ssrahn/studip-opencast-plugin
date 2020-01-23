const path = require('path'); // node.js uses CommonJS modules
const {
    VueLoaderPlugin
} = require('vue-loader');

module.exports = {
    entry: ['./vueapp/app.js', './stylesheets/main.scss'], // the entry point
    output: {
        filename: 'bundle.js', // the output filename
        path: path.resolve(__dirname, 'static') // fully qualified path
    },
    module: {
        rules: [{
            test: /\.vue$/,
            use: 'vue-loader'
        }, {
			test: /.scss$/,
			use: [
				{
					loader: 'file-loader',
					options: {
						name: 'styles.css',
						outputPath: ''
					}
				},
                {
					loader: 'extract-loader',
				},
				{
					loader: 'css-loader?-url'
				},
				{
					loader: 'sass-loader'
				}
			]
		}]
    },
    plugins: [
        new VueLoaderPlugin()
    ],
    resolve: {
        extensions: ['.vue', '.js'],
        alias: {
            '@': path.resolve(__dirname, 'vueapp')
        }
    }
};
