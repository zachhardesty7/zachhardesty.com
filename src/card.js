import React from 'react'
import PropTypes from 'prop-types'
import GImage from 'gatsby-image'

import { Grid, Modal } from 'semantic-ui-react'

import './card.scss'

const Card = ({
  description,
  link,
  github,
  skills,
  image,
  title
}) => (
  <div className='card'>
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
    <div className='card-stacked'>
      <div className='content'>
        <h5 className='title'>
          <a href={link} target='blank_'>{title}</a>
        </h5>
        <p>{description}</p>
        <div className='skills'>
          <ul>{skills.map(skill => <li key={skill} className='skill'>{skill}</li>)}</ul>
        </div>
      </div>
      <div className='actions'>
        {link &&
          <a href={link} target='blank_'>View Project</a>
        }
        {github &&
          <a href={github} target='blank_'>View Source Code</a>
        }
      </div>
    </div>
  </div>
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
