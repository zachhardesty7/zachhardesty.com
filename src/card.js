import React from 'react'
import PropTypes from 'prop-types'
import { Image, Transformation } from 'cloudinary-react'

import {
  Grid, Modal, Image as SUIImage
} from 'semantic-ui-react'

import './modal.scss'
import './card.scss'

const Card = ({ info }) => (
  <div className='card'>
    {info.image && (
      <Modal
        size='large'
        key={info.name}
        closeIcon
        trigger={(
          <div className='thumbnail link'>
            <div className='overlay' />
            <Image alt='thumbnail' cloudName='zachhardesty' publicId={info.image} format='jpg'>
              <Transformation crop='fill' gravity='north' width='750' height='750' />
            </Image>
          </div>
        )}
      >
        <Modal.Header>
          {info.title}
        </Modal.Header>
        <Modal.Content scrolling>
          <Grid columns={2} stackable>
            <Grid.Column computer={7} textAlign='left'>
              <SUIImage as={Image} className='profile-image' cloudName='zachhardesty' centered size='large' publicId={info.image} format='jpg' />
            </Grid.Column>
            <Grid.Column computer={9} textAlign='justified'>
              <Modal.Description>
                {/* {info.bio.content.map(paragraph => (
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
          <a href={info.link} target='blank_'>{info.title}</a>
        </h5>
        <p>{info.description}</p>
        <div className='skills'>
          <ul>{info.skills.map(skill => <li key={skill} className='skill'>{skill}</li>)}</ul>
        </div>
      </div>
      <div className='actions'>
        {info.link &&
        <a href={info.link} target='blank_'>View Project</a>
        }
        {info.github &&
        <a href={info.github} target='blank_'>View Source Code</a>
        }
      </div>
    </div>
  </div>
)

Card.propTypes = {
  info: PropTypes.shape({
    link: PropTypes.string,
    github: PropTypes.string,
    skills: PropTypes.array,
    image: PropTypes.string,
    title: PropTypes.string
  })
}

Card.defaultProps = {
  info: {
    link: '',
    github: '',
    skills: [],
    image: '',
    title: ''
  }
}

export default Card
