import React, { Component } from 'react'
import Scroll from 'react-scroll'
import Icons from './icons.js'
let Link = Scroll.Link

export default class Title extends Component {
  render () {
    return (
      <div id='title'>
        <h2>zach hardesty</h2>
        <Icons text />
        <Link activeClass='active' className='chevron' offset={-60} to='about' spy smooth duration={800}><i className='fa fa-chevron-down fa-2x' aria-hidden='true' /></Link>
      </div>
    )
  }
}
