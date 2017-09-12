import React, { Component } from 'react';
import Radium from 'radium';
import logo from './logo.svg';
import './App.css';

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
    type: 'project',
    title: 'WooCommerce Widget Interface',
    description: 'Okay, so I have a super cool widget for our affiliates or vendors to use, but how to I generate the embed code? This project features a simple React.js app that allows a user to generate a snippet from their chosen settings. Express mode enables only affiliate tracking while locking the other values to their dynamic setting. Advanced enables customization of all attributes. Custom serves as a middle ground between the two. (Project is not currently publically accessible)',
    // image: require(''),
    link: '',
    github: 'https://github.com/zachhardesty7/woocommerce-widget-interface',
    skills: []
  },
  {
    type: 'project',
    title: 'WooCommerce Widget',
    description: 'Allowing affiliates or vendors to embed products from your WooCommerce store is nigh impossible. This project brings a widget that enables a super customizable dynamic iFrame widget that can be embedded in any website. It can track and report various things back to the host store upon completion of a sale. (Project is not currently publically accessible but a demo can be viewed at following link)',
    image: require('./img/mpl-widget.png'),
    link: 'http://mpl.zachhardesty.com/',
    github: 'https://github.com/zachhardesty7/woocommerce-widget',
    skills: []
  },
  {
    type: 'project',
    title: 'WooCommerce MPL Plugin',
    description: 'WordPress plugin to extend the functionality of WooCommerce. It includes a collection of tweaks that modify various layouts and styles, CRON jobs for checking active users in Litmos LMS, and Constant Contact integration. Developed for http://MarkPorterLive.com.',
    // image: require(''),
    link: 'https://markporterlive.com',
    github: 'https://github.com/zachhardesty7/woocommerce-mpl',
    skills: []
  },
  {
    type: 'project',
    title: 'UT Austin Web Tweaks',
    description: 'This extension to the Chrome browser adds additional features to UT\'s website. The primary goal is to making registering for classes through UT\'s outdated interface much easier. It provides numerous quick links and options to speed simplify the process and eliminate stress. A handful of other tweaks are also included and configurable through the options page. A full scheduling system will eventually be built in along with a system to automatically generate a schedule to your constraints from the classes you\'re interested in. (Project is not currently publically accessible)',
    // image: require(''),
    link: '',
    github: 'https://github.com/zachhardesty7/UT-Austin-Website-Tweaks',
    skills: ['javascript', 'css', 'materialize.css', 'chrome extension libraries']
  },
  {
    type: 'project',
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
  // constructor(props) {
  //   super(props);
  //   this.state = {
  //     home: true,
  //   };
  // }
  // handleScroll = (state) => {
  //   this.setState({home: state})
  // }
	componentDidMount() {
    window.addEventListener('mousewheel', this.handleScroll);
	}
	handleScroll = (event) => {
    console.log(event);
    console.log(event.srcElement.scrollTop);

    console.log(event.srcElement.scrollHeight);

    console.log(event.srcElement.clientHeight);

    console.log(event.srcElement.offsetHeight);

		let scrollTop = document.body.scrollTop;
		!scrollTop && event.deltaY > 0 ? console.log("Success") : console.log("false");;
	}
  render() {
    let websites = data.map(website => {
      if (website.type === 'website') {
        return (
          <Card
            key={website.title.replace(/\s+/g, '-').toLowerCase()}
            info={website}
          />
        )
      } else { return false }
    })
    return (
      <div
        className='App'
        style={{
          display: 'grid',
          textAlign: 'center',
          gridTemplateColumns: '15% auto 15%',
          gridTemplateRows: 'auto ',
          // height: '100vh'
        }}
      >
        <Title
          // display={this.state.home ? 'flex' : 'none'}
          // onScroll={this.handleScroll}
        />
        <Header />
        <main style={{
          gridRow: '2 / 3',
          gridColumn: '2 / 3',
          // display: this.state.home ? 'none' : '',
        }}>
          <p id='about'>
            Hello! I’m UTCS student striving to specialize in the forefront of web development. From 2015 to present, I’ve worked for a small business owner and have been given many opportunities to learn and grow in web development. During my time with that company, my coworkers described me as an eager-minded intern that they depended on to bring innovation and a fresh attitude to projects. I am passionate about creating elegant and effective websites for my clients. Please visit my portfolio for more details about my past projects and feel free to contact me!

            I have seen multiple websites to completion from the ground up, including SEO, marketing, and content creation. I seek to communicate with clients to determine the best method for accomplishing their goals. I'm honest about my skills and will not hesitate to explain the difficulty of a situation I'm unfamiliar with.
          </p>
          <div id='projects'>
            {websites}
          </div>
          <p>
            Proficient with: JavaScript (incl. ES6+), WordPress, jQuery, git, HTML, CSS
            Familiar with: React, PHP, MATLAB, Java
          </p>
          <p id='contact'>
            contact me:
            <a href='mailto:hello@zachhardesty.com'>hello@zachhardesty.com</a>
          </p>
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
			<div style={{
				gridRow: '1 / 2',
        gridColumn: '2 / 3',
				// display: this.props.display,
				display: 'flex',
				justifyContent: 'center',
				flexDirection: 'column',
        height: '100vh',
			}}>
				<h1>zach hardesty</h1>
				<h2>front-end web developer</h2>
				<Icons/>
			</div>
		)
	}
}

class Header extends Component {
  render() {
    var styles = {
      nav: {
        // gridRow: '1 / 2',
        // gridColumn: '2 / 3',
        // display: 'flex',
        position: 'fixed',
        display: 'grid',
        gridTemplateColumns: '1fr auto 1fr',
        alignContent: 'center',
        width: '100%',
        top: '0',
        alignItems: 'center',
        zIndex: '9',
      },
      link: {
        textDecoration: 'none',
        color: '#e0479e',
        padding: '5px 5px',
        ':hover': {
          backgroundColor: '#9ebc9e',
          padding: '50px',
        },
      },
      left: {
        justifySelf: 'end',
      },
      right: {
        justifySelf: 'start',
      },
      logo: {
        padding: '0 5px'
      },
    }
    return (
      <nav style={styles.nav}>
        <div style={styles.left}>
          <a style={styles.link} className='link' href='#about'>about</a>
          <a style={styles.link} className='link' href='#projects'>projects</a>
        </div>
        <div>
          <a style={styles.logo} href='#title'><object type='image/svg+xml' data={logo} width='40px'>zh logo</object></a>
        </div>
        <div style={styles.right}>
          <a style={styles.link} className='link' href='#experience'>experience</a>
          <a style={styles.link} className='link' href='#contact'>contact</a>
        </div>
      </nav>
    )
  }
}
Header = Radium(Header);

class Footer extends Component {
  render() {
    return (
      <div style={{
        gridArea: '3 / 2 / 3 / 2',
        alignSelf: 'end',
      }}>
        <p>website designed and developed by zach hardesty || copyright 2017</p>
        <Icons />
      </div>
    );
  }
}

class Icons extends Component {
  render() {
    return (
        <div>
          <a href='mailto:hello@zachhardesty.com'><i className="fa fa-envelope fa-lg fa-fw" aria-hidden="true" aria-label="Email"></i></a>
          <a href='https://github.com/zachhardesty7'><i className="fa fa-github fa-lg fa-fw" aria-hidden="true" aria-label="Github"></i></a>
          <a href='https://www.linkedin.com/in/zachhardesty7/'><i className="fa fa-linkedin fa-lg fa-fw" aria-hidden="true" aria-label="LinkedIn"></i></a>
        </div>
    );
  }
}
Icons = Radium(Icons);


class Card extends Component {
  render() {
    return (
      <div className='card'>
        <h3>{this.props.info.title}</h3>
        <p>{this.props.info.description}</p>
        <div style={{
          height: '400px',
          overflow: 'hidden',
        }}>
          <img className='thumbnail' alt='thumbnail' style={{width: '75%'}} src={this.props.info.image} />
        </div>
        {this.props.info.link &&
          <p><a href={this.props.info.link}>LINK</a></p>
        }
        <ul className='skills'>{this.props.info.skills.map(skill => <li className='skill'>{skill}</li>)}</ul>
      </div>
    )
  }
}

export default Radium(App);
