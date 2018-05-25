import React, { Component } from 'react'
import { Image, Transformation } from 'cloudinary-react'

export default class Card extends Component {
  render () {
    return (
      <div className='card'>
        {this.props.info.image &&
          <div className='thumbnail'>
            <div className='overlay' />
            <a href={this.props.info.link} target='blank_'>
              <Image alt='thumbnail' cloudName='zachhardesty' publicId={this.props.info.image} format='jpg'>
                <Transformation crop='fill' gravity='north' width='750' height='750' />
              </Image>
            </a>
          </div>
        }
        <div className='card-stacked'>
          <div className='content'>
            <h5 className='title'>
              <a href={this.props.info.link} target='blank_'>
                {this.props.info.title}
              </a>
            </h5>
            <p>{this.props.info.description}</p>
            <div className='skills'>
              <ul>{this.props.info.skills.map(skill => <li key={skill} className='skill'>{skill}</li>)}</ul>
            </div>
          </div>
          <div className='actions'>
            {this.props.info.link &&
              <a href={this.props.info.link} target='blank_'>View Project</a>
            }
            {this.props.info.github &&
              <a href={this.props.info.github} target='blank_'>View Source Code</a>
            }
          </div>
        </div>
      </div>
    )
  }
}
