import React, { Component } from 'react'
import { Image, Transformation } from 'cloudinary-react'

import Title from './title.js'
import Header from './header.js'
import Icons from './icons.js'
import Portfolio from './portfolio.js'
import Experience from './experience.js'
import Footer from './footer.js'
import './App.css'

class App extends Component {
  constructor (props) {
    super(props)
    this.state = {
      disp: 'app'
    }
  }
  handleClick = (e) => {
    document.querySelectorAll('.tabs > a').forEach(tab => {
      tab.className = tab.className.split(' ')[0]
    })
    this.setState({disp: e.target.className})
    e.target.className = e.target.className + ' active'
  }
  render () {
    return (
      <div className='App'>
        <Title />
        <Header />
        <main className='main'>
          <div id='about'>
            <h3>About Me</h3>
            <div>
              <Image id='portrait' cloudName='zachhardesty' publicId='portrait_llepmw' format='jpg'>
                <Transformation crop='fill' gravity='faces' width='210' height='210' radius='max' />
              </Image>
            </div>
            <p>Welcome to my web portfolio!</p>
            <p>First, let me tell you a little bit about myself. I am currently enrolled in the University of Texas at Austin's computer science degree plan but my journey began much earlier. From a very young age I've always had a deep fascination in understanding the "why" and the "how" of everything from common items to modern marvels. Naturally, once I received my parents' old computer I began tinkering and quickly familiarizing myself.</p>
            <p>As the next logical step, I wanted to learn the underlying fundamentals that make computer programs function. The task of learning programming seemed daunting so as my first step I began learning the basics of HTML, CSS, and JavaScript in my spare time.</p>
            <p>One day my father suggested that my neighbor hire me as a technologist intern. I had virtually no marketable experience but I had a willingness to learn on my side. I initially began by redesigning and modernizing his WordPress website. My coworkers described me as an eager-minded intern that they depended on to bring innovation and a fresh attitude to new and old projects alike. I later moved on to develop plugins to extend the functionality of WordPress and a simple React app. Through freelance work I have seen multiple websites to completion from the ground up, including SEO, marketing, content creation, and branding. Check my resume or list of projects below for more specific details on various projects!</p>
            <p>What does the future entail for me? Cryptocurrency, machine learning, web app development, and website development and design all pique my interests. I find it difficult to choose a specific path and therefore I remain open to all opportunities. Please do not to hesitate to contact me with any opportunities or inquiries and please connect with me!</p>
          </div>
          <Icons color='black' text />
          <div id='skills'>
            <h5>Skills</h5>
            <p><b>Proficient with:</b> React, JavaScript (incl. ES6+), WordPress, HTML, CSS</p>
            <p><b>Familiar with:</b> Python, Java, MongoDB, Bootstrap 4, CLI, REST, PHP, SCSS, Git, jQuery, Webpack</p>
          </div>
          <Experience />
          <div id='projects'>
            <h3>Projects</h3>
            <div className='tabs'>
              <a className='app active' onClick={this.handleClick}>Apps</a>
              <a className='website' onClick={this.handleClick}>Websites</a>
            </div>
            <Portfolio disp={this.state.disp} />
          </div>
        </main>
        <Footer />
      </div>
    )
  }
}

export default App
