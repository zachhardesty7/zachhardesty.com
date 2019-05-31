import Typography from 'typography'
// import grandViewTheme from 'typography-theme-grand-view'

const typography = new Typography(null)

// Output CSS as string.
// typography.toString()

// Or insert styles directly into the <head> (works well for client-only
// JS web apps.
// typography.injectStyles()

// export helper functions
export const { scale, rhythm, options } = typography
export default typography

/* notes
import { TypographyStyle, GoogleFont } from 'react-typography'
// Best practice is to have a typography module
// where you define your theme.
import typography from 'utils/typography'

// Inside your React.js HTML component.
<html>
  <head>
    <TypographyStyle typography={typography} />
    <GoogleFont typography={typography} />
  </head>
  <body>
    // stuff
  </body>
</html>
*/
