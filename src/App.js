import React, { Component } from 'react';
import Scroll from 'react-scroll';
import './App.css';
let Link = Scroll.Link;

let data = [
  /*
  {
    type: '',
    title: '',
    description: '',
    image: require(''),
    link: '',
    github: '',
    skills: []
  },
  */
  {
    type: 'app',
    title: 'WooCommerce Widget Interface',
    description: 'Okay, so I have a super cool widget for our affiliates or vendors to use, but how to I generate the embed code? This project features a simple React.js app that allows a user to generate a snippet from their chosen settings. Express mode enables only affiliate tracking while locking the other values to their dynamic setting. Advanced enables customization of all attributes. Custom serves as a middle ground between the two. (Project is not currently publically accessible)',
    // image: require(''),
    link: '',
    github: 'https://github.com/zachhardesty7/woocommerce-widget-interface',
    skills: []
  },
  {
    type: 'app',
    title: 'WooCommerce Widget',
    description: 'Allowing affiliates or vendors to embed products from your WooCommerce store is nigh impossible. This project brings a widget that enables a super customizable dynamic iFrame widget that can be embedded in any website. It can track and report various things back to the host store upon completion of a sale. (Project is not currently publically accessible but a demo can be viewed at following link)',
    image: require('./img/mpl-widget.png'),
    link: 'http://mpl.zachhardesty.com/',
    github: 'https://github.com/zachhardesty7/woocommerce-widget',
    skills: []
  },
  {
    type: 'app',
    title: 'WooCommerce MPL Plugin',
    description: 'WordPress plugin to extend the functionality of WooCommerce. It includes a collection of tweaks that modify various layouts and styles, CRON jobs for checking active users in Litmos LMS, and Constant Contact integration. Developed for http://MarkPorterLive.com.',
    // image: require(''),
    link: 'https://markporterlive.com',
    github: 'https://github.com/zachhardesty7/woocommerce-mpl',
    skills: []
  },
  {
    type: 'app',
    title: 'UT Austin Web Tweaks',
    description: 'This extension to the Chrome browser adds additional features to UT\'s website. The primary goal is to making registering for classes through UT\'s outdated interface much easier. It provides numerous quick links and options to speed simplify the process and eliminate stress. A handful of other tweaks are also included and configurable through the options page. A full scheduling system will eventually be built in along with a system to automatically generate a schedule to your constraints from the classes you\'re interested in. (Project is not currently publically accessible)',
    // image: require(''),
    link: '',
    github: 'https://github.com/zachhardesty7/UT-Austin-Website-Tweaks',
    skills: ['javascript', 'css', 'materialize.css', 'chrome extension libraries']
  },
  {
    type: 'app',
    title: 'My Personal Portfolio Website',
    description: 'The website you\'re looking at right now! This website is built with React.js to allow easy updating by just pumping in new data. It also utilizes Radium.js for better styling in React and entirely custom CSS.',
    // image: require(''),
    link: 'http://zachhardesty.com',
    github: 'https://github.com/zachhardesty7/zachhardesty.com',
    skills: ['react.js', 'javascript', 'radium.js', 'css', 'css grid']
  },
  {
    type: 'website',
    title: 'Mark Porter Live',
    description: 'homepage for all things related to MPL, also includes eCommerce capabilities',
    image: require('./img/mpl.png'),
    link: 'https://markporterlive.com',
    github: '',
    skills: ['wordpress', 'html', 'css', 'divi', 'php']
  },
  {
    type: 'website',
    title: 'TSA Roadie',
    description: 'simple one page website to sell single product',
    image: require('./img/tsaroadie.png'),
    link: 'http://tsaroadie.com',
    github: '',
    skills: ['wordpress', 'html', 'css', 'divi']
  },
  {
    type: 'website',
    title: 'shehatesme',
    description: 'simple website proof of concept to easily sell products using shopify "buy button"',
    image: require('./img/shm.png'),
    link: 'http://shehatesme.zachhardesty.com',
    github: '',
    skills: ['wordpress', 'html', 'css', 'divi']
  },
  {
    type: 'website',
    title: 'Keynote Community',
    description: 'website with user system to serve as hub for Keynote Series',
    image: require('./img/keynote.png'),
    link: 'http://keynotecommunity.com',
    github: '',
    skills: ['wordpress', 'html', 'css', 'divi']
  },
]

class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      // home: true,
      disp: 'app'
    };
  }
  // handleScroll = (state) => {
  //   this.setState({home: state})
  // }
	// componentDidMount() {
  //   window.addEventListener('mousewheel', this.handleScroll);
	// }
	// handleScroll = (event) => {
  //   console.log(event);
  //   console.log(event.srcElement.scrollTop);
  //
  //   console.log(event.srcElement.scrollHeight);
  //
  //   console.log(event.srcElement.clientHeight);
  //
  //   console.log(event.srcElement.offsetHeight);
  //
	// 	let scrollTop = document.body.scrollTop;
	// 	!scrollTop && event.deltaY > 0 ? console.log('Success') : console.log('false');;
	// }
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
            <p><img className='portrait' alt='portrait' src={require('./img/portrait.jpg')} /></p>
            <p>Hello! I’m UTCS student striving to specialize in the forefront of web development but very open to other fields, including startups and machine learning-driven positions. From 2015 to present, I’ve worked for a small business owner and have been given many opportunities to learn and grow in web development. During my time with that company, my coworkers described me as an eager-minded intern that they depended on to bring innovation and a fresh attitude to projects. I am passionate about creating elegant and effective websites for my clients. Please visit my portfolio for more details about my past projects and feel free to contact me!</p>
            <p>I have seen multiple websites to completion from the ground up, including SEO, marketing, and content creation. I seek to communicate with clients to determine the best method for accomplishing their goals. I'm honest about my skills and will not hesitate to explain the difficulty of a situation I'm unfamiliar with.</p>
          </div>
          <div id='skills'>
            <h5>Skills</h5>
            <p>Proficient with: JavaScript (incl. ES6+), WordPress, jQuery, git, HTML, CSS</p>
            <p>Familiar with: React, PHP, MATLAB, Java</p>
          </div>
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
  // future dynamic react homepage
	// componentDidMount() {
	// 	window.addEventListener('mousewheel', this.handleScroll);
	// }
	// componentWillUnmount() {
	// 	window.removeEventListener('mousewheel', this.handleScroll);
	// }
	// handleScroll = (event) => {
  //   console.log(event);
	// 	let scrollTop = event.srcElement.scrollTop;
	// 	scrollTop ? this.props.onScroll(true) : this.props.onScroll(false);
	// }
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

class Card extends Component {
  render() {
    return (
      <div className='card'>
        {this.props.info.image &&
          <div className='thumbnail'>
            <img alt='thumbnail' src={this.props.info.image} />
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
