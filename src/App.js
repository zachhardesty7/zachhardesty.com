import React, { Component } from 'react';
import Scroll from 'react-scroll';
import { Image, Transformation } from 'cloudinary-react';
import './App.css';
import data from './data.json'
let Link = Scroll.Link;

class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      disp: 'app',
    };
  }
  handleClick = (e) => {
    document.querySelectorAll('.tabs > a').forEach(tab => {
      tab.className = tab.className.split(' ')[0];
    });
    this.setState({disp: e.target.className});
    e.target.className = e.target.className + ' active';
  }
  render() {
    return (
      <div className='App'>
        <Title />
        <Header />
        <main className='main'>
          <div id='about'>
            <h3>About Me</h3>
            <div>
              <Image id='portrait' cloudName="zachhardesty" publicId="portrait_llepmw" format="jpg">
                <Transformation crop="fill" gravity="faces" width="210" height="210" radius='max'/>
              </Image>
            </div>
            <p>Welcome to my web portfolio!</p>
            <p>First, let me tell you a little bit about myself. I am currently enrolled in the University of Texas at Austin's computer science degree plan but my journey began much earlier. From a very young age I've always had a deep fascination in understanding the "why" and the "how" of everyday things, modern marvels, and everything in between. Naturally, once I recevied my parents old computer I began tinkering and familiarizing myself. It's safe to say that I've quite literally grown up with computers.</p>
            <p>As the next logical step, I wanted to learn the underlying fundamentals that make computer programs function. While some might consider me ambitious, learning programming seemed daunting. I took the first step of learning the basics of HTML, CSS, and JavaScript and occassionally studied in my free time.</p>
            <p>One day my father suggested that my neighbor hire me as a technologist intern. I had virtually no experience but I had had my blazing passion on my side. I initially began by redesigning and modernizing his WordPress website. My coworkers described me as an eager-minded intern that they depended on to bring innovation and a fresh attitude to projects, even when I lacked familiarity. I now freelance develop websites and I am passionate about creating elegant and effective websites for my clients and employees.</p>
            <p>I have seen multiple websites to completion from the ground up, including SEO, marketing, and content creation. I seek to communicate with clients to determine the method best suited for accomplishing their goals. I'm honest about my skills and will not hesitate to explain the difficulty of a situation I lack familiarity with.</p>
          </div>
          <div>
            <div>
              <p>Please Connect with Me!</p>
              <Icons color='black' text={true}/>
            </div>
          </div>
          <div id='skills'>
            <h5>Skills</h5>
            <p>Proficient with: React (incl. React Native), JavaScript (incl. ES6+), WordPress, HTML, CSS</p>
            <p>Familiar with: Bootstrap 4, CLI, REST, PHP, SCSS, Git, jQuery, Webpack, MATLAB, Java, Python</p>
          </div>
          <Experience />
          <div id='projects'>
            <h3>Projects</h3>
            <div className='tabs'>
              <a className='app active' onClick={this.handleClick}>Apps</a>
              <a className='website' onClick={this.handleClick}>Websites</a>
            </div>
            <Portfolio disp={this.state.disp}/>
          </div>
        </main>
        <Footer />
      </div>
    );
  }
}

class Title extends Component {
  render() {
    return (
      <div id='title'>
        <h2>zach hardesty</h2>
        <Icons/>
        <Link activeClass='active' className='chevron' offset={-60} to='about' spy={true} smooth={true} duration={800}><i className='fa fa-chevron-down fa-2x' aria-hidden='true'></i></Link>
      </div>
    )
  }
}

class Header extends Component {
  calcDistance = (scrollDistanceInPx) => {
    let min = 600,
    max = 2500,
    duration = Math.min( Math.max( Math.abs(scrollDistanceInPx) * 2, min ), max );
    return duration;
  }
  render() {
    let offset = -60;
    return (
      <nav>
        <div>
          <Link activeClass='active' className='link' to='about' offset={offset} spy={true} smooth={true} duration={this.calcDistance}>about</Link>
          <Link activeClass='active' className='link' to='projects' offset={offset} spy={true} smooth={true} duration={this.calcDistance}>projects</Link>
        </div>
        <Link activeClass='active' className='logo' to='title' spy={true} smooth={true} duration={this.calcDistance}>
          <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 934 934'>
            <title>
              ZH Logo V4
            </title>
            <path className='line' d='M189.51 244.81l299.37-.75-299.46 299.46L745 544M487.53 371.47v343.59M694.34 371.34v343.85'/>
            <circle className='outline' cx='467' cy='467' r='437'/>
          </svg>
        </Link>
        <div>
          <Link activeClass='active' className='link' to='experience' offset={offset} spy={true} smooth={true} duration={this.calcDistance}>experience</Link>
          <Link activeClass='active' className='link' to='contact' offset={offset} spy={true} smooth={true} duration={this.calcDistance}>contact</Link>
        </div>
      </nav>
    )
  }
}

class Footer extends Component {
  render() {
    return (
      <footer>
        <div id='contact'>
          <h4>Contact Me:</h4>
          <a href='mailto:hello@zachhardesty.com'>hello@zachhardesty.com</a>
          <Icons/>
        </div>
        <div id='bottom'>
          <p>website designed and developed by zach hardesty || copyright 2017</p>
        </div>
      </footer>
		);
	}
}

class Icons extends Component {
	render() {
		return (
			<div className='icons'>
        <div className='icon'>
          <a href='mailto:hello@zachhardesty.com' target='blank_'>
            <i className='fa fa-envelope fa-lg fa-fw' aria-hidden='true' aria-label='Email' title='Email' style={{color: this.props.color}}></i>
            {this.props.text &&
              <span className='icon-text' style={{color: this.props.color}}>Email</span>
            }
          </a>
        </div>
        <div className='icon'>
          <a href='https://github.com/zachhardesty7' target='blank_'>
            <i className='fa fa-github fa-lg fa-fw' aria-hidden='true' aria-label='Github' title='Github' style={{color: this.props.color}}></i>
            {this.props.text &&
              <span className='icon-text' style={{color: this.props.color}}>Github</span>
            }
          </a>
        </div>
				<div className='icon'>
          <a href='https://www.linkedin.com/in/zachhardesty7/' target='blank_'>
            <i className='fa fa-linkedin fa-lg fa-fw' aria-hidden='true' aria-label='LinkedIn' title='LinkedIn' style={{color: this.props.color}}></i>
            {this.props.text &&
              <span className='icon-text' style={{color: this.props.color}}>LinkedIn</span>
            }
          </a>
        </div>
        <div className='icon'>
          <a href='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' target='blank_'>
            <i className='fa fa-briefcase fa-lg fa-fw' aria-hidden='true' aria-label='Resume' title='Resume' style={{transform: 'translateY(7.5%)', color: this.props.color}}></i>
            {this.props.text &&
              <span className='icon-text' style={{color: this.props.color}}>Resume</span>
            }
          </a>
        </div>
			</div>
		);
	}
}

class Portfolio extends Component {
  render() {
    let projects = data.map(project => {
      if (project.type === this.props.disp) {
        return (
          <Card
            key={project.title.replace(/\s+/g, '-').toLowerCase()}
            info={project}
          />
        )
      } else { return false }
    })
    return (
      <div>
        {projects}
      </div>
    );
  }
}

class Experience extends Component {
  render() {
    let experience = data.map(job => {
      if (job.type === 'experience') {
        return (
          <div key={job.position.toLowerCase().replace(/ /g, '-')} className='card'>
            <div className='card-stacked'>
              <div className='content'>
                <h5 className='position'>{job.position + ' || ' + job.employer + ' || ' + job.location}</h5>
                <div className='bullets'>
                  <ul>{job.bullets.map(bullet => <li key={bullet} className='bullet'>{bullet}</li>)}</ul>
                </div>
              </div>
            </div>
          </div>
        )
      } else { return false }
    })
    return (
      <div id='experience'>
        <h3>Experience</h3>
        {experience}
      </div>
    );
  }
}

class Card extends Component {
  render() {
    return (
      <div className='card'>
        {this.props.info.image &&
          <div className='thumbnail'>
            <Image alt='thumbnail' cloudName="zachhardesty" publicId={this.props.info.image} format="jpg">
              <Transformation crop="fill" gravity='north' width="750" height="750" />
            </Image>
          </div>
        }
        <div className='card-stacked'>
          <div className='content'>
            <h5 className='title'>{this.props.info.title}</h5>
            <p>{this.props.info.description}</p>
            <div className='skills'>
              <ul>{this.props.info.skills.map(skill => <li key={skill} className='skill'>{skill}</li>)}</ul>
            </div>
          </div>
          <div className='actions'>
            {this.props.info.link &&
              <a href={this.props.info.link} target='blank_'>View Project</a>
            }
            {this.props.info.github &&
              <a href={this.props.info.github} target='blank_'>View Source Code</a>
            }
          </div>
        </div>
      </div>
    )
  }
}

export default App;
