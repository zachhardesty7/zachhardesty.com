import React from 'react'
import { Link } from 'react-scroll'
import { Icon, IconGroup } from 'semantic-styled-ui'

import './title.scss'

const Title = () => (
  <div id='title'>
    <h2>zach hardesty</h2>
    <IconGroup size='large' light compact justify='center'>
      <Icon name='mail' link='mailto:hello@zachhardesty.com' />
      <Icon name='github' link='https://github.com/zachhardesty7' />
      <Icon name='linkedin' link='https://www.linkedin.com/in/zachhardesty7' />
      <Icon name='briefcase' link='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' />
    </IconGroup>
    <Icon tag={Link} to='about' size='bigger' name='chevron down' className='chevron' offset={-60} />
  </div>
)

export default Title
