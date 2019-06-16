import React, { useState } from 'react'
import styled, { ThemeProvider, createGlobalStyle } from 'styled-components'
import { graphql } from 'gatsby'

// import { Link } from 'react-scroll'
// import Helmet from 'react-helmet'
import GImage from 'gatsby-image'

import 'semantic-ui-css/semantic.min.css'

import {
  Icon,
  IconGroup,
  ObjectFromEntries,
  getColor,
} from 'semantic-styled-ui'

import Title from '../title'
import Header from '../header'
import Portfolio from '../portfolio'
import Experience from '../experience'
import Footer from '../footer'

const defaultColors = {
  primary: '#0b3c5d',
  secondary: '#fafafa',
  accent: '#d9b310',
  dark: '#2c2c34',
  light: '#cccccc',
}

const GlobalStyle = createGlobalStyle`
  *, :after, :before {
      box-sizing: inherit;
  }

  html,
  body {
    height: 100%;
  }

  html {
    font-size: 14px;
    box-sizing: border-box;
    line-height: 1.5;
    font-family: "Roboto", sans-serif;
    font-weight: normal;
    color: rgba(0,0,0,0.87);
  }

  body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    min-width: 320px;
    background: #FFFFFF;
    font-size: 14px;
    line-height: 1.4285em;
    color: rgba(0, 0, 0, 0.87);
    font-family: "Roboto", 'Helvetica Neue', Arial, Helvetica, sans-serif;
    background-color: $light;
    
    ::selection {
      background-color: $accent;
      color: #ffffff;
    }
  }

  li,
  ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
  }

  a {
    text-decoration: none;
    transition: color .3s ease;  
  }

  a, .link, button {
    cursor: pointer;
  }

  h1, h2, h3, h4, h5, h6 {
    font-weight: 400;
    line-height: 1.1;
    margin: 0;
    padding: 0;
  }

  h1 {
    font-size: 4.2rem;
    line-height: 110%;
    margin: 2.1rem 0 1.68rem 0
  }

  h2 {
    font-size: 3.56rem;
    line-height: 110%;
    margin: 1.78rem 0 1.424rem 0
  }

  h3 {
    ${getColor('primary')};
    font-size: 2.92rem;
    line-height: 110%;
    margin: 1.46rem 0 1.168rem 0
  }

  h4 {
    font-size: 2.28rem;
    line-height: 110%;
    margin: 1.14rem 0 .912rem 0
  }

  h5 {
    font-size: 1.64rem;
    line-height: 110%;
    margin: .82rem 0 .656rem 0
  }

  h6 {
    font-size: 1rem;
    line-height: 110%;
    margin: .5rem 0 .4rem 0
  }

.App {
  display: grid;
  text-align: center;
  grid-column-gap: 5%;
  grid-template-columns: auto auto auto;
  grid-template-rows: auto;
}

#about, #projects, #experience {
  padding-top: 1rem;
  margin-top: 2rem;
}

#about > p {
  text-align: left;
}

#skills {
  padding-top: 2rem;
  div {
    text-align: left;
    p {
      margin-top: 0.3em;
    }
  }
}

.main {
  grid-row: 2 / 3;
  grid-column: 2 / 3;
}

.portrait {
  width: 15rem;
  border-radius: 100%;
  text-align: center;
}

@keyframes bounce {
  0%,
  100% {
    transform: translate(-50%, 0);
  }
  50% {
    transform: translate(-50%, 40%);
  }
}

.button {
  position: relative;
  cursor: pointer;
  display: inline-block;
  overflow: hidden;
  user-select: none;
  margin: 0 .25rem;
  z-index: 1;
  transition: .3s ease-out;
  color: #fff;
  background-color: $accent;
  letter-spacing: .5px;
  font-size: 1rem;
  outline: 0;
  border: none;
  border-radius: 2px;
  height: 36px;
  line-height: 36px;
  padding: 0 2rem;
  text-transform: uppercase;
  box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 1px 5px 0 rgba(0,0,0,0.12), 0 3px 1px -2px rgba(0,0,0,0.2);

  &:hover {
    background-color: lighten($accent, 20);
    box-shadow: 0 3px 3px 0 rgba(0,0,0,0.14), 0 1px 7px 0 rgba(0,0,0,0.12), 0 3px 1px -1px rgba(0,0,0,0.2);
  }

  &:focus {
    background-color: darken($accent, 20);
  }
}

.line,
.outline {
  fill: none;
  stroke: #fff;
  stroke-linecap: round;
  stroke-width: 60px;
}
.line {
  stroke-linejoin: round;
}
.outline {
  stroke-miterlimit: 10;
}

@media (min-width: 576px) {
  .App {
    grid-column-gap: 10%;
  }
}

@media (min-width: 768px) {
  .App {
    grid-column-gap: 15%;
  }
}

@media (min-width: 992px) {
  .App {
    grid-column-gap: 25%;
  }
}

@media (min-width: 1200px) {
  .App {
    grid-column-gap: 30%;
  }
}
`

const S = {}

S.Tabs = styled.div`
  ${getColor('primary')};
  padding: 1rem 0;
  align-content: center;
  align-items: center;

  .active {
    border-bottom: 5px solid gold;
  }

  button {
    background-color: transparent;
    border: none;
    ${getColor('primary')};
    transition: border .4s;
    font-size: 1.25rem;
    margin: 0 10px;
    padding: 10px 0;

    &:focus {
      outline: none;
    }

    &:hover:not(.active) {
      border-bottom: 2px solid gold;
      color: rgba(gold, 0.75);
    }
  }
`

const App = ({ data }) => {
  const [display, setDisplay] = useState('app')

  const apps = data.allAppsJson.edges.map(entry => entry.node)
  const experiences = data.allExperiencesJson.edges.map(entry => entry.node)
  const websites = data.allWebsitesJson.edges.map(entry => entry.node)
  const skills = ObjectFromEntries(
    data.allSkillsJson.edges.map(skillset => [skillset.node.type, skillset.node.values])
  )
  const images = ObjectFromEntries(
    data.allFile.edges.map(image => (
      [image.node.childImageSharp.fixed.originalName, image.node.childImageSharp.fixed]
    ))
  )
  const portrait = data.portrait.childImageSharp.fixed

  const handleClick = (e) => {
    setDisplay(e.target.dataset.display)
  }

  return (
    <ThemeProvider theme={defaultColors}>
      <div className='App root'>
        <GlobalStyle />
        <Title />
        <Header />
        <main className='main'>
          <div id='about'>
            <h3>About Me</h3>
            <div>
              <GImage className='portrait' fixed={portrait} />
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
              <p>{skills.proficient.join(', ')}</p>
            </div>
            <div>
              <b>Familiar with:</b>
              <p>{skills.familiar.join(', ')}</p>
            </div>
          </div>
          <Experience data={experiences} />
          <div id='projects'>
            <h3>Projects</h3>
            <S.Tabs>
              <button type='button' data-display='app' className={display === 'app' ? 'active' : undefined} onClick={handleClick}>Apps</button>
              <button type='button' data-display='website' className={display === 'website' ? 'active' : undefined} onClick={handleClick}>Websites</button>
            </S.Tabs>
            <Portfolio items={display === 'website' ? websites : apps} images={images} />
          </div>
        </main>
        <Footer />
      </div>
    </ThemeProvider>
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
    portrait: file(name: {eq: "portrait"}) {
      id
      childImageSharp {
        fixed(width: 250, height: 250, cropFocus: NORTH) {
          ...GatsbyImageSharpFixed_withWebp
          originalName
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
    allSkillsJson {
      edges {
        node {
          type
          values
        }
      }
    }
  }
`

export default App
