module.exports = {
  presets: [
    'babel-preset-gatsby'
  ],
  plugins: [
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
      '@babel/plugin-transform-template-literals', {
        loose: true
      }
    ]
  ]
}
