import React from 'react'
import styled from 'styled-components'
import { Link } from 'react-scroll'
import {
	Icon, IconGroup, getBackgroundColor, getColor,
} from 'semantic-styled-ui'

const S = {}

S.Title = styled.div`
  grid-row: 1 / 2;
  grid-column: 1 / 4;
  display: flex;
  justify-content: center;
  flex-direction: column;
  height: 100vh;
  ${getBackgroundColor('primary')};
  ${getColor('white')};
`

S.Header = styled.h1`
  text-shadow: 0px 2px 3px darkgrey;
  font-family: 'Roboto Condensed', sans-serif;
`

S.Chevron = styled(Icon)`
  position: absolute;
  bottom: 5%;
  left: 50%;
  animation: bounce 4s ease-in-out infinite;
  transition: opacity .3s ease;
  
  &:hover {
    cursor: pointer;
  }
`

const Title = () => (
	<S.Title id='title'>
		<S.Header>zach hardesty</S.Header>
		<IconGroup label size='large' light justify='center'>
			<Icon name='Mail' link='mailto:hello@zachhardesty.com' />
			<Icon name='Github' link='https://github.com/zachhardesty7' />
			<Icon name='LinkedIn' link='https://www.linkedin.com/in/zachhardesty7' />
			<Icon name='briefcase' label='Resume' link='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' />
		</IconGroup>
		<S.Chevron forwardedAs={Link} light link='#about' offset={-60} size='bigger' name='chevron down' />
	</S.Title>
)

export default Title
