import React, { Component } from 'react'

export default class Icons extends Component {
  render () {
    return (
      <div className='icons'>
        <div className='icon'>
          <a href='mailto:hello@zachhardesty.com' target='blank_'>
            <i className='fa fa-envelope fa-lg fa-fw' aria-hidden='true' aria-label='Email' title='Email' style={{ color: this.props.color }} />
            {this.props.text &&
              <span className='icon-text' style={{ color: this.props.color }}>Email</span>
            }
          </a>
        </div>
        <div className='icon'>
          <a href='https://github.com/zachhardesty7' target='blank_'>
            <i className='fa fa-github fa-lg fa-fw' aria-hidden='true' aria-label='Github' title='Github' style={{ color: this.props.color }} />
            {this.props.text &&
              <span className='icon-text' style={{ color: this.props.color }}>Github</span>
            }
          </a>
        </div>
        <div className='icon'>
          <a href='https://www.linkedin.com/in/zachhardesty7/' target='blank_'>
            <i className='fa fa-linkedin fa-lg fa-fw' aria-hidden='true' aria-label='LinkedIn' title='LinkedIn' style={{ color: this.props.color }} />
            {this.props.text &&
              <span className='icon-text' style={{ color: this.props.color }}>LinkedIn</span>
            }
          </a>
        </div>
        <div className='icon'>
          <a href='https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing' target='blank_'>
            <i className='fa fa-briefcase fa-lg fa-fw' aria-hidden='true' aria-label='Resume' title='Resume' style={{ transform: 'translateY(7.5%)', color: this.props.color }} />
            {this.props.text &&
              <span className='icon-text' style={{ color: this.props.color }}>Resume</span>
            }
          </a>
        </div>
      </div>
    )
  }
}
