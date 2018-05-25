import React, { Component } from 'react'
import Icons from './icons.js'

export default class Footer extends Component {
  render () {
    return (
      <footer>
        <div id='contact'>
          <h4>Contact</h4>
          <a href='mailto:hello@zachhardesty.com'>hello@zachhardesty.com</a>
          <Icons text />
        </div>
        <div id='bottom'>
          <p>website designed and developed by zach hardesty || copyright 2018</p>
        </div>
      </footer>
    )
  }
}
