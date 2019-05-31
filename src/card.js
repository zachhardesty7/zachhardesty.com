import React from 'react'
import PropTypes from 'prop-types'
import GImage from 'gatsby-image'

import { Grid, Modal } from 'semantic-ui-react'
import { getBackgroundColor, getColor } from 'semantic-styled-ui'

import styled from 'styled-components'

const S = {}

S.Card = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
  margin: .5rem 0 1rem 0;
  border-radius: 2px;
  transition: box-shadow .25s;
  box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14),
  0 1px 5px 0 rgba(0,0,0,0.12),
  0 3px 1px -2px rgba(0,0,0,0.2);
  background-color: #fff;
`

S.Thumbnail = styled.div`
  width: 100%;
  max-height: 200px;
  overflow: hidden;

  img {
    width: 100%;
    opacity: 1;
    transition: opacity .3s ease;
    
    &:hover {
      opacity: 0.75;
    }
  }
`

S.CardStacked = styled.div`
  position: relative;
`

S.Actions = styled.div`
  position: relative;
  background-color: inherit;
  border-top: 1px solid rgba(160,160,160,0.2);
  padding: 16px 24px;

  a {
    ${getColor('primary')};
    margin: 0 12px;
    text-transform: uppercase;

    &:hover {
      color: rgba(${getColor('primary')}, 0.75);
    }
  }
`

S.Content = styled.div`
  padding: 24px;
  border-radius: 0 0 2px 2px;
  flex-grow: 1;
  text-align: left;
`

S.Position = styled.div`
  ${getColor('primary')};
`

S.TitleLink = styled.a`
  ${getColor('primary')};
  
  &:hover {
    color: rgba(${getColor('primary')}, 0.75);
  }
`

S.Skill = styled.li`
  display: inline-block;
  padding: 5px 10px;
  margin: 5px 5px;
  ${getBackgroundColor('accent')};
  color: #fff;
  border-radius: 4px;
`

S.Date = styled.div`
  color: rgba(darkgrey, .8);
`

const Card = ({
  description,
  link,
  github,
  skills,
  image,
  title
}) => (
  <S.Card>
    {image && (
      <Modal
        size='large'
        key={title}
        closeIcon
        trigger={(
          <div className='thumbnail link'>
            <div className='overlay' />
            <GImage fixed={image} />
          </div>
        )}
      >
        <Modal.Header>
          {title}
        </Modal.Header>
        <Modal.Content scrolling>
          <Grid columns={2} stackable>
            <Grid.Column computer={7} textAlign='left'>
              <GImage fixed={image} />
            </Grid.Column>
            <Grid.Column computer={9} textAlign='justified'>
              <Modal.Description>
                {/* {bio.content.map(paragraph => (
                    <p key={paragraph.content[0].value.slice(0, 8)}>
                      {paragraph.content[0].value}
                    </p>
                  ))} */}
              </Modal.Description>
            </Grid.Column>
          </Grid>
        </Modal.Content>
      </Modal>
    )}
    <S.CardStacked>
      <S.Content>
        <h5 className='title'>
          {
            link
              ? <S.TitleLink href={link} target='blank_'>{title}</S.TitleLink>
              : title
          }
        </h5>
        <p>{description}</p>
        <div className='skills'>
          <ul>{skills.map(skill => <S.Skill key={skill}>{skill}</S.Skill>)}</ul>
        </div>
      </S.Content>
      <S.Actions>
        {link &&
          <a href={link} target='blank_'>View Project</a>
        }
        {github &&
          <a href={github} target='blank_'>View Source Code</a>
        }
      </S.Actions>
    </S.CardStacked>
  </S.Card>
)

Card.propTypes = {
  link: PropTypes.string,
  github: PropTypes.string,
  description: PropTypes.string,
  skills: PropTypes.node,
  image: PropTypes.string,
  title: PropTypes.string
}

Card.defaultProps = {
  link: '',
  github: '',
  description: '',
  skills: [],
  image: '',
  title: ''
}

export default React.memo(Card)
