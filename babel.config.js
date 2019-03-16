module.exports = {
  presets: [
    [
      '@babel/preset-env',
      {
        loose: true,
        modules: false,
        useBuiltIns: 'usage',
        shippedProposals: true,
        targets: {
          browsers: [
            '>0.25%',
            'not dead'
          ]
        }
      }
    ],
    [
      '@babel/preset-react',
      {
        useBuiltIns: true,
        pragma: 'React.createElement'
      }
    ]
  ],
  plugins: [
    // TODO: breaks when using npm linked version of semantic-styled-ui
    'babel-plugin-transform-semantic-ui-react-style-imports',
    [
      'transform-react-remove-prop-types', {
        removeImport: true
      }
    ],
    '@babel/plugin-proposal-optional-chaining',
    [
      '@quickbaseoss/babel-plugin-styled-components-css-namespace',
      {
        cssNamespace: '.root.root.root'
      }
    ],
    [
      '@babel/plugin-proposal-class-properties',
      {
        loose: true
      }
    ],
    '@babel/plugin-syntax-dynamic-import',
    [
      '@babel/plugin-transform-runtime',
      {
        helpers: true,
        regenerator: true
      }
    ]
  ]
}
