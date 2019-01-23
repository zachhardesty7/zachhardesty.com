import React, { useState } from 'react'
import { Image, Transformation } from 'cloudinary-react'

import Title from './title'
import Header from './header'
import Icons from './icons'
import Portfolio from './portfolio'
import Experience from './experience'
import Footer from './footer'
import './App.scss'

import data from './data.json'

const App = () => {
  const [display, setDisplay] = useState('app')

  const handleClick = (e) => {
    setDisplay(e.target.dataset.display)
  }

  return (
    <div className='App'>
      <Title />
      <Header />
      <main className='main'>
        <div id='about'>
          <h3>About Me</h3>
          <div>
            {/* cSpell: disable-next-line */}
            <Image id='portrait' cloudName='zachhardesty' publicId='portrait_llepmw' format='jpg'>
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
        <Icons color='black' text />
        <div id='skills'>
          <h5>Skills</h5>
          <p>
            <b>Proficient with:</b>
            <p> React, JavaScript (incl. ES6+), WordPress, HTML, CSS, SCSS, Git, REST, GraphQL</p>
          </p>
          <p>
            <b>Familiar with:</b>
            <p> Python, Java, Webpack, Bootstrap, Semantic UI, CLI, C/C++, PHP, MongoDB</p>
          </p>
        </div>
        <Experience data={data} />
        <div id='projects'>
          <h3>Projects</h3>
          <div className='tabs'>
            <button type='button' data-display='app' className={display === 'app' ? 'active' : undefined} onClick={handleClick}>Apps</button>
            <button type='button' data-display='website' className={display === 'website' ? 'active' : undefined} onClick={handleClick}>Websites</button>
          </div>
          <Portfolio display={display} />
        </div>
      </main>
      <Footer />
    </div>
  )
}

export default App
