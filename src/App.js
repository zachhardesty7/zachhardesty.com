import React, { Component } from 'react';
import Scroll from 'react-scroll';
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
            <div><img id='portrait' alt='portrait' src={require('./img/portrait.jpg')} /></div>
            <p>Hello! I’m UTCS student striving to specialize in the forefront of web development but very open to other fields, including startups and machine learning-driven positions. From 2015 to present, I’ve worked for a small business owner and have been given many opportunities to learn and grow in web development. During my time with that company, my coworkers described me as an eager-minded intern that they depended on to bring innovation and a fresh attitude to projects. I am passionate about creating elegant and effective websites for my clients. Please visit my portfolio for more details about my past projects and feel free to contact me!</p>
            <p>I have seen multiple websites to completion from the ground up, including SEO, marketing, and content creation. I seek to communicate with clients to determine the best method for accomplishing their goals. I'm honest about my skills and will not hesitate to explain the difficulty of a situation I'm unfamiliar with.</p>
          </div>
          <div id='skills'>
            <h5>Skills</h5>
            <p>Proficient with: JavaScript (incl. ES6+), WordPress, HTML, CSS</p>
            <p>Familiar with: React, PHP, Bootstrap 4, CLI, SCSS, Git, jQuery, Webpack, MATLAB, Python, Java</p>
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
					<h4>contact me:</h4>
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
				<a href='mailto:hello@zachhardesty.com'>
					<i className='fa fa-envelope fa-lg fa-fw' aria-hidden='true' aria-label='Email'></i>
				</a>
				<a href='https://github.com/zachhardesty7'>
					<i className='fa fa-github fa-lg fa-fw' aria-hidden='true' aria-label='Github'></i>
				</a>
				<a href='https://www.linkedin.com/in/zachhardesty7/'>
					<i className='fa fa-linkedin fa-lg fa-fw' aria-hidden='true' aria-label='LinkedIn'></i>
				</a>
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
          <div className='card'>
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
            <img alt='thumbnail' src={require(`${this.props.info.image}`)} />
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
              <a href={this.props.info.link}>View Project</a>
            }
            {this.props.info.github &&
              <a href={this.props.info.github}>View Source Code</a>
            }
          </div>
        </div>
      </div>
    )
  }
}

export default App;
