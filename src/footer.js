import React from 'react'
import { Icon, IconGroup, getBackgroundColor } from 'semantic-styled-ui'
import styled from 'styled-components'

const S = {}

S.Footer = styled.footer`
  ${getBackgroundColor('primary')};
  /* background-color: linear-gradient(180deg, navy, navy); */
  grid-row: 3 / 3;
  grid-column: 1 / 4;
  font-size: .9rem;
  padding-top: 20px;
  color: #fff;
`

S.Contact = styled.div`
  margin-bottom: 10px;
  transition: transform 0.6s ease-in-out;
  
  > a {
    display: block;
    color: #fff;
  }
`

S.Bottom = styled.div`
  overflow: hidden;
  font-weight: 300;
  min-height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
`

const Footer = () => (
  <S.Footer>
    <S.Contact id='contact'>
      <IconGroup light label size='large' padded justify='center'>
        <Icon name='Mail' link='mailto:hello@zachhardesty.com' />
        <Icon name='Github' link='https://github.com/zachhardesty7' />
        <Icon name='LinkedIn' link='https://www.linkedin.com/in/zachhardesty7' />
        <Icon name='briefcase' label='Resume' link='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' />
      </IconGroup>
      <a href='mailto:hello@zachhardesty.com'>hello@zachhardesty.com</a>
    </S.Contact>
    <S.Bottom id='bottom'>
      <p>website designed and developed by zach hardesty || copyright 2018 - 2019</p>
    </S.Bottom>
  </S.Footer>
)

export default Footer
