import React from 'react'
import { Link } from 'react-scroll'
import { Icon, IconGroup } from 'semantic-styled-ui'

import './title.scss'

const Title = () => (
  <div id='title'>
    <h2>zach hardesty</h2>
    <IconGroup label size='large' light justify='center'>
      <Icon name='Mail' link='mailto:hello@zachhardesty.com' />
      <Icon name='Github' link='https://github.com/zachhardesty7' />
      <Icon name='LinkedIn' link='https://www.linkedin.com/in/zachhardesty7' />
      <Icon name='briefcase' label='Resume' link='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' />
    </IconGroup>
    <Icon tag={Link} light link='#about' offset={-60} size='bigger' name='chevron down' className='chevron' />
  </div>
)

export default Title
