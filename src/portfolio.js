import React from 'react'
import PropTypes from 'prop-types'
import data from './data.json'
import Card from './card'

const Portfolio = ({ display }) => (
  <div>
    {data.map((project) => {
      if (project.type === display) {
        return (
          <Card
            key={project.title.replace(/\s+/g, '-').toLowerCase()}
            info={project}
          />
        )
      } return false
    })}
  </div>
)

Portfolio.propTypes = {
  display: PropTypes.string
}

Portfolio.defaultProps = {
  display: ''
}

export default Portfolio
