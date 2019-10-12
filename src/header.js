import React from 'react'
import { Link } from 'react-scroll'
import styled from 'styled-components'
import { Navigation, getBackgroundColor } from 'semantic-styled-ui'

const S = {}

S.Nav = styled.nav`
  position: fixed;
  display: flex;
  width: 100%;
  align-items: center;
  z-index: 9;
  ${getBackgroundColor('primary')};

  & > div:first-child {
    display: flex;
		flex: 1 1 auto;
    justify-content: flex-end;
  }

  & > div:last-child {
    display: flex;
		flex: 1 1 auto;
    justify-content: flex-start;
  }
`

S.NavItem = styled(Navigation.Item)`
  color: #fff;
  text-shadow: 0px 2px 4px black;
  padding: 5px;

  &:hover {
    color: rgba(255, 255, 255, 0.75);
  }
`

S.LogoCon = styled.div`
	padding: 0 0.3em;
`

S.Logo = styled.svg`
  &&& {
		&:hover {
			path, circle {
    		stroke: rgba(255, 255, 255, 0.75);
			}
  	}

		path, circle {
			transition: stroke .3s ease;
		}
  }
`

const pages = ['about', 'projects', 'experience', 'contact']

const Header = () => (
	<S.Nav>
		<div>
			{pages.slice(0, pages.length / 2).map((page, i) => (
				<S.NavItem
					forwardedAs={Link}
					key={page}
					to={page}
					offset={-60}
					tabIndex='0'
					link='#'
				>
					{page}
				</S.NavItem>
			))}
		</div>
		<Navigation.Logo logoSize='small' as={Link} to='title' link='#'>
			<S.LogoCon>
				<S.Logo xmlns='http://www.w3.org/2000/svg' viewBox='0 0 934 934'>
					<path strokeLinejoin='round' d='M189.5 244.8l299.4-.7-299.5 299.4 555.6.5M487.5 371.5v343.6m206.8-343.8v343.9' fill='none' stroke='#fff' strokeLinecap='round' strokeWidth='60' />
					<circle cx='467' cy='467' r='437' fill='none' stroke='#fff' strokeLinecap='round' strokeLinejoin='round' strokeMiterlimit='10' strokeWidth='60' />
				</S.Logo>
			</S.LogoCon>
		</Navigation.Logo>
		<div>
			{pages.slice(pages.length / 2).map((page, i) => (
				<S.NavItem
					forwardedAs={Link}
					key={page}
					to={page}
					offset={-60}
					tabIndex='0'
					link='#'
				>
					{page}
				</S.NavItem>
			))}
		</div>
	</S.Nav>
)

export default Header
