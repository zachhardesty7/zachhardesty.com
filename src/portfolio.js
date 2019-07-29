import React from 'react'
import PropTypes from 'prop-types'
import Card from './card'

const Portfolio = ({ items = [], images }) => (
	items.map(item => (
		<Card
			key={item.title.replace(/\s+/g, '-').toLowerCase()}
			title={item.title}
			description={item.description}
			link={item.link}
			github={item.github}
			skills={item.skills}
			image={images[item.image]}
		/>
	))
)

Portfolio.propTypes = {
	items: PropTypes.node,
	images: PropTypes.node,
}

export default React.memo(Portfolio)
