import React from 'react'
import PropTypes from 'prop-types'
import { Image, Transformation } from 'cloudinary-react'

const Card = ({ info }) => (
  <div className='card'>
    {info.image && (
      <div className='thumbnail'>
        <div className='overlay' />
        <a href={info.link} target='blank_'>
          <Image alt='thumbnail' cloudName='zachhardesty' publicId={info.image} format='jpg'>
            <Transformation crop='fill' gravity='north' width='750' height='750' />
          </Image>
        </a>
      </div>
    )}
    <div className='card-stacked'>
      <div className='content'>
        <h5 className='title'>
          <a href={info.link} target='blank_'>
            {info.title}
          </a>
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
