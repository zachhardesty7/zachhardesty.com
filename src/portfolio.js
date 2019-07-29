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
	images: PropTypes.objectOf(PropTypes.shape({
		width: PropTypes.number.isRequired,
		height: PropTypes.number.isRequired,
		src: PropTypes.string.isRequired,
		srcSet: PropTypes.string.isRequired,
		base64: PropTypes.string,
		tracedSVG: PropTypes.string,
		srcWebp: PropTypes.string,
		srcSetWebp: PropTypes.string,
		media: PropTypes.string,
	})),
	items: PropTypes.arrayOf(PropTypes.shape({
		id: PropTypes.string,
		title: PropTypes.string,
		description: PropTypes.string,
		image: PropTypes.string,
		link: PropTypes.string,
		github: PropTypes.string,
		skills: PropTypes.arrayOf(PropTypes.string),
	})),
}

export default React.memo(Portfolio)
