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
            <p>First, allow me to tell you a little about myself. I am now enrolled in the University of Texas at Austin's computer science degree plan but my journey began much earlier. From a young age, understanding the “why” and the “how” of everything from common items to modern marvels fascinated me. Naturally, once I received my parents' old computer, I began tinkering and familiarizing myself. As the next logical step, I wanted to grasp the underlying fundamentals that power computer programs. Learning to program daunted me so I dove into exploring the basics of HTML, CSS, and JavaScript in my spare time.</p>
            <p>One day my father suggested my neighbor hire me as a technologist intern. I possessed no marketable experience, but I had the determination to learn on my side. I began by modernizing his WordPress website and later moved on to develop WordPress plugins and a React app. My coworkers described me as an eager-minded intern they depended on to bring innovation and a fresh attitude to new and old projects alike. Through freelance work, I have developed many websites from scratch to completion, including Search Engine Optimization (SEO), marketing, content creation, and branding. Check my resume or the list of projects below for more specific details!</p>
            <p>What does the future entail for me? Cryptocurrency, machine learning, and web app development pique my interests but I find it difficult to decide a particular path and stay available to all possibilities. Please do not to hesitate to contact me with any opportunities or inquiries! Connect with me on LinkedIn!</p>

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
