module.exports = {
  pathPrefix: '/',
  siteMetadata: {
    siteUrl: 'https://zachhardesty.com'
  },
  plugins: [
    `gatsby-plugin-sass`,
    {
      resolve: `gatsby-source-filesystem`,
      options: {
        name: `data`,
        path: `${__dirname}/src/data/`,
        ignore: [`**/\.*`], // ignore files starting with a dot
      },
    }
    // {
    //   resolve: 'gatsby-plugin-google-analytics',
    //   options: {
    //     trackingId: 'UA-133288229-1',
    //     respectDNT: true
    //   }
    // },
    // 'gatsby-plugin-sitemap',
    // {
    //   resolve: 'gatsby-plugin-webpack-bundle-analyzer',
    //   options: {
    //     // production: true
    //   }
    // },
    // 'gatsby-plugin-react-helmet',
    // {
    //   resolve: 'gatsby-plugin-favicon',
    //   options: {
    //     logo: './src/favicon.png',

    //     // WebApp Manifest Configuration
    //     appName: 'Gulf Corp', // Inferred with your package.json
    //     appDescription: 'Real Estate Development & Construction',
    //     developerName: 'Zach Hardesty',
    //     developerURL: 'https://zachhardesty.com',
    //     dir: 'auto',
    //     lang: 'en-US',
    //     background: '#fff',
    //     theme_color: '#fff',
    //     display: 'standalone',
    //     orientation: 'any',
    //     start_url: '/?homescreen=1',
    //     version: '1.0',

    //     icons: {
    //       android: true,
    //       appleIcon: true,
    //       appleStartup: true,
    //       coast: false,
    //       favicons: true,
    //       firefox: true,
    //       opengraph: false,
    //       twitter: false,
    //       windows: false
    //     }
    //   }
    // },
    // 'gatsby-plugin-offline',
    // {
    //   resolve: 'gatsby-plugin-styled-components',
    //   options: {
    //     pure: process.env.NODE_ENV === 'production',
    //     fileName: process.env.NODE_ENV !== 'production',
    //     displayName: process.env.NODE_ENV !== 'production'
    //   }
    // },
    // 'gatsby-plugin-netlify'
  ]
}
