module.exports = {
	presets: [
		[
			'@babel/preset-env',
			{
				loose: true,
				modules: false,
				useBuiltIns: 'usage',
				shippedProposals: true,
				corejs: 2,
			},
		],
		[
			'@babel/preset-react',
			{
				useBuiltIns: true,
				pragma: 'React.createElement',
			},
		],
	],
	plugins: [
		'babel-plugin-transform-semantic-ui-react-style-imports',
		[
			'transform-react-remove-prop-types', {
				removeImport: true,
			},
		],
		'@babel/plugin-proposal-optional-chaining',
		[
			'@quickbaseoss/babel-plugin-styled-components-css-namespace',
			{
				cssNamespace: '.root.root.root',
			},
		],
		'@babel/plugin-syntax-dynamic-import',
		[
			'@babel/plugin-transform-template-literals', {
				loose: true,
			},
		],
	],
}
