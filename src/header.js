import React, { Component } from 'react'
import Scroll from 'react-scroll'
let Link = Scroll.Link

export default class Header extends Component {
  calcDistance = (scrollDistanceInPx) => {
    let min = 600
    let max = 2500
    let duration = Math.min(Math.max(Math.abs(scrollDistanceInPx) * 2, min), max)

    return duration
  }
  render () {
    let offset = -60

    return (
      <nav>
        <div>
          <Link activeClass='active' className='link' to='about' offset={offset} spy smooth duration={this.calcDistance}>about</Link>
          <Link activeClass='active' className='link' to='projects' offset={offset} spy smooth duration={this.calcDistance}>projects</Link>
        </div>
        <Link activeClass='active' className='logo' to='title' spy smooth duration={this.calcDistance}>
          <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 934 934'>
            <title>
              ZH Logo V4
            </title>
            <path className='line' d='M189.51 244.81l299.37-.75-299.46 299.46L745 544M487.53 371.47v343.59M694.34 371.34v343.85' />
            <circle className='outline' cx='467' cy='467' r='437' />
          </svg>
        </Link>
        <div>
          <Link activeClass='active' className='link' to='experience' offset={offset} spy smooth duration={this.calcDistance}>experience</Link>
          <Link activeClass='active' className='link' to='contact' offset={offset} spy smooth duration={this.calcDistance}>contact</Link>
        </div>
      </nav>
    )
  }
}
