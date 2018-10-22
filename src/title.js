import React from 'react'
import { Link } from 'react-scroll'
import Icons from './icons'

const Title = () => (
  <div id='title'>
    <h2>zach hardesty</h2>
    <Icons text />
    <Link activeClass='active' className='chevron' offset={-60} to='about' spy smooth duration={800}><i className='fa fa-chevron-down fa-2x' aria-hidden='true' /></Link>
  </div>
)

export default Title
