import React, { Component } from 'react'
import data from './data.json'
import Card from './card.js'

export default class Portfolio extends Component {
  render () {
    let projects = data.map(project => {
      if (project.type === this.props.disp) {
        return (
          <Card
            key={project.title.replace(/\s+/g, '-').toLowerCase()}
            info={project}
          />
        )
      } else { return false }
    })
    return (
      <div>
        {projects}
      </div>
    )
  }
}
