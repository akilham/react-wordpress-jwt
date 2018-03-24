import React from 'react';

import { PostList } from './helpers.jsx';


/**
 * The component that renders the public page
 */
export default class PublicPage extends React.Component {
	constructor(props) {
		super(props);
	}

	render() {
		return (
			<div>
				<h2>Publicly Accessible Page</h2>

				<p>This page <strong>does not</strong> require you be logged in.</p>

				<h3>Post list</h3>

				{(!this.props.state.postsLoaded ? <h4>Loading...</h4> : <PostList posts={this.props.state.posts} />)}
			</div>
		)
	}
}