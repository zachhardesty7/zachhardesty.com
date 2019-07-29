import React from 'react'
import PropTypes from 'prop-types'
import styled from 'styled-components'
import { getBackgroundColor, getColor } from 'semantic-styled-ui'

const S = {}

S.Bullets = styled.ul`
  width: fit-content;
  margin: auto;

  li::marker {
    ${getColor('primary')};
    ${getBackgroundColor('primary')};
  }
`

const Experience = ({ data = [] }) => (
	<div id='experience'>
		<h3>Experience</h3>
		{data.map(job => (
			<div key={job.position.toLowerCase().replace(/ /g, '-')} className='card'>
				<div className='card-stacked'>
					<div className='content'>
						<h5 className='position'>{`${job.position}`}</h5>
						<h5 className='position'>{`${job.employer} (${job.location})`}</h5>
						<p className='date'>{job.date}</p>
						<S.Bullets>
							{job.bullets.map(bullet => <li key={bullet}>{bullet}</li>)}
						</S.Bullets>
					</div>
				</div>
			</div>
		))}
	</div>
)

Experience.propTypes = {
	data: PropTypes.arrayOf(PropTypes.shape({
		type: PropTypes.string,
		position: PropTypes.string,
		location: PropTypes.string,
		employer: PropTypes.string,
		bullets: PropTypes.array,
		date: PropTypes.string,
	})),
}

export default Experience
