import React from 'react'
import { Icon, IconGroup } from 'semantic-styled-ui'

import './footer.scss'

const Footer = () => (
  <footer>
    <div id='contact'>
      <h4>Contact</h4>
      <IconGroup light label size='large' padded='bottom' justify='center'>
        <Icon name='Mail' link='mailto:hello@zachhardesty.com' />
        <Icon name='Github' link='https://github.com/zachhardesty7' />
        <Icon name='LinkedIn' link='https://www.linkedin.com/in/zachhardesty7' />
        <Icon name='briefcase' label='Resume' link='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' />
      </IconGroup>
      <a href='mailto:hello@zachhardesty.com'>hello@zachhardesty.com</a>
    </div>
    <div id='bottom'>
      <p>website designed and developed by zach hardesty || copyright 2018</p>
    </div>
  </footer>
)

export default Footer
