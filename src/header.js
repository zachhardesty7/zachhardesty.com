import React from 'react'
import { Link } from 'react-scroll'

import './header.scss'

const calcDistance = (scrollDistanceInPx) => {
  const min = 600
  const max = 2500
  const duration = Math.min(Math.max(Math.abs(scrollDistanceInPx) * 2, min), max)

  return duration
}

const Header = () => (
  <nav>
    <div>
      <Link activeClass='active' className='link' to='about' offset={-60} spy smooth duration={calcDistance}>about</Link>
      <Link activeClass='active' className='link' to='projects' offset={-60} spy smooth duration={calcDistance}>projects</Link>
    </div>
    <Link activeClass='active' className='logo' to='title' spy smooth duration={calcDistance}>
      <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 934 934'>
        <title>
              ZH Logo V4
        </title>
        <path className='line' d='M189.51 244.81l299.37-.75-299.46 299.46L745 544M487.53 371.47v343.59M694.34 371.34v343.85' />
        <circle className='outline' cx='467' cy='467' r='437' />
      </svg>
    </Link>
    <div>
      <Link activeClass='active' className='link' to='experience' offset={-60} spy smooth duration={calcDistance}>experience</Link>
      <Link activeClass='active' className='link' to='contact' offset={-60} spy smooth duration={calcDistance}>contact</Link>
    </div>
  </nav>
)

export default Header
