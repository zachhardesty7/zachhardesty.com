import React, { useState } from 'react'
import { Image, Transformation } from 'cloudinary-react'
// import styled, { ThemeProvider, createGlobalStyle } from 'styled-components'
import { graphql } from 'gatsby'

// import { Link } from 'react-scroll'
// import Helmet from 'react-helmet'
// import GImage from 'gatsby-image'

import 'semantic-ui-css/semantic.min.css'

// user-defined
import {
  // Footer,
  // Hero,
  Icon,
  IconGroup
  // Navigation,
  // asTag,
  // getBackgroundColor,
  // getColor
} from 'semantic-styled-ui'

import Title from '../title'
import Header from '../header'
import Portfolio from '../portfolio'
import Experience from '../experience'
import Footer from '../footer'
import './App.scss'

import '../projects.scss'

// const brandColors = {
//   blue: '#3b5998',
//   orange: '#ca6914',
//   teal: '#749ad3',
//   white: '#ffffff'
// }

// const defaultColors = {
//   ...brandColors,
//   primary: brandColors.blue,
//   secondary: brandColors.teal,
//   accent: brandColors.orange
// }

const App = ({ data }) => {
  const [display, setDisplay] = useState('app')

  const apps = data.allAppsJson.edges.map(entry => entry.node)
  const experiences = data.allExperiencesJson.edges.map(entry => entry.node)
  const websites = data.allWebsitesJson.edges.map(entry => entry.node)
  const images = {}
  data.allFile.edges.map(entry => entry.node).forEach((image) => {
    images[image.childImageSharp.fixed.originalName] = image.childImageSharp.fixed
  })

  const handleClick = (e) => {
    setDisplay(e.target.dataset.display)
  }

  return (
    <div className='App root'>
      <Title />
      <Header />
      <main className='main'>
        <div id='about'>
          <h3>About Me</h3>
          <div>
            <Image id='portrait' cloudName='zachhardesty' publicId='portrait' format='jpg'>
              <Transformation crop='fill' gravity='faces' width='210' height='210' radius='max' />
            </Image>
          </div>

          <h4>Welcome to my web portfolio!</h4>
          <p>
            {`First, allow me to tell you my story. I am now enrolled in the University of Texas at Austin's computer science degree plan but my journey began much earlier. From a young age, understanding the “why” and the “how” of everything from common items to modern marvels fascinated me. Naturally, once I received my parents' old computer I began tinkering and familiarizing myself. Learning to program daunted me but I hesitantly began exploring the basics of HTML, CSS, and JavaScript in my spare time. After getting a job with my neighbor, I started tinkering around in WordPress. There, I uncovered my fascination with web development and the rest is history.`}
          </p>
          <p>
            {`What does the future entail for me? Cryptocurrency, machine learning, and web and mobile app development all pique my interests. That being said, I find it difficult to commit to a particular path this early and stay open to all possibilities. Please do not to hesitate to contact me with opportunities or inquiries and connect with me on LinkedIn!`}
          </p>

        </div>
        <IconGroup label padded='top' color='rgba(0,0,0,.7)' colorHover='rgba(0,0,0,1)' size='big' justify='center'>
          <Icon name='Mail' link='mailto:hello@zachhardesty.com' />
          <Icon name='Github' link='https://github.com/zachhardesty7' />
          <Icon name='LinkedIn' link='https://www.linkedin.com/in/zachhardesty7' />
          <Icon name='briefcase' label='Resume' link='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' />
        </IconGroup>
        <div id='skills'>
          <h5>Skills</h5>
          <div>
            <b>Proficient with:</b>
            <p> React, JavaScript (incl. ES6+), WordPress, HTML, CSS, SCSS, Git, REST, GraphQL</p>
          </div>
          <div>
            <b>Familiar with:</b>
            <p> Python, Java, Webpack, Bootstrap, Semantic UI, CLI, C/C++, PHP, MongoDB</p>
          </div>
        </div>
        <Experience data={experiences} />
        <div id='projects'>
          <h3>Projects</h3>
          <div className='tabs'>
            <button type='button' data-display='app' className={display === 'app' ? 'active' : undefined} onClick={handleClick}>Apps</button>
            <button type='button' data-display='website' className={display === 'website' ? 'active' : undefined} onClick={handleClick}>Websites</button>
          </div>
          <Portfolio items={display === 'website' ? websites : apps} images={images} />
        </div>
      </main>
      <Footer />
    </div>
  )
}

export const q = graphql`
  {
    allFile(filter: {extension: {eq: "png"}}) {
      edges {
        node {
          id
          childImageSharp {
            fixed(width: 700, height: 700, cropFocus: NORTH) {
              ...GatsbyImageSharpFixed_withWebp
              originalName
            }
          }
        }
      }
    }
    allAppsJson {
      edges {
        node {
          id
          title
          description
          image
          link
          github
          skills
        }
      }
    }
    allWebsitesJson {
      edges {
        node {
          id
          title
          description
          image
          link
          github
          skills
        }
      }
    }
    allExperiencesJson {
      edges {
        node {
          id
          employer
          position
          location
          date
          bullets
        }
      }
    }
  }
`

export default App
