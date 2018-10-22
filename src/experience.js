import React from 'react'
import PropTypes from 'prop-types'

const Experience = ({ data }) => (
  <div id='experience'>
    <h3>Experience</h3>
    {data.map((job) => {
      if (job.type === 'experience') {
        return (
          <div key={job.position.toLowerCase().replace(/ /g, '-')} className='card'>
            <div className='card-stacked'>
              <div className='content'>
                <h5 className='position'>{`${job.position} || ${job.employer} || ${job.location}`}</h5>
                <p className='date'>{job.date}</p>
                <div className='bullets'>
                  <ul>{job.bullets.map(bullet => <li key={bullet} className='bullet'>{bullet}</li>)}</ul>
                </div>
              </div>
            </div>
          </div>
        )
      } return false
    })}
  </div>
)

Experience.propTypes = {
  data: PropTypes.arrayOf(PropTypes.shape({
    type: PropTypes.string,
    position: PropTypes.string,
    location: PropTypes.string,
    employer: PropTypes.string,
    bullets: PropTypes.array,
    date: PropTypes.string
  }))
}

Experience.defaultProps = {
  data: []
}

export default Experience
