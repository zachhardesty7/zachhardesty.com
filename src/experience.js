import React, { Component } from 'react'
import data from './data.json'

export default class Experience extends Component {
  render () {
    let experience = data.map(job => {
      if (job.type === 'experience') {
        return (
          <div key={job.position.toLowerCase().replace(/ /g, '-')} className='card'>
            <div className='card-stacked'>
              <div className='content'>
                <h5 className='position'>{job.position + ' || ' + job.employer + ' || ' + job.location}</h5>
                <p className='date'>{job.date}</p>
                <div className='bullets'>
                  <ul>{job.bullets.map(bullet => <li key={bullet} className='bullet'>{bullet}</li>)}</ul>
                </div>
              </div>
            </div>
          </div>
        )
      } else { return false }
    })
    return (
      <div id='experience'>
        <h3>Experience</h3>
        {experience}
      </div>
    )
  }
}
