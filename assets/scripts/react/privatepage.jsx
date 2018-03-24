import React from 'react';

import { PostList } from './helpers.jsx';


/**
 * The component that renders the private page
 */
export default class PrivatePage extends React.Component {
	constructor(props) {
		super(props);
	}

	render() {
		if (this.props.state.isAuthenticated) {
			return (
				<div>
					<h2>Private Page</h2>

					<p>This page <strong>does require</strong> you to be logged in. If visit this page when not logged in as a valid user you will get an error.</p>

					<h3>Post list</h3>

					{(!this.props.state.postsLoaded ? <h4>Loading...</h4> : <PostList posts={this.props.state.posts} />)}
				</div>
			);
		}
		else {
			return (
				<div>
					<h3>Sorry, you are not authorised to view this page</h3>
				</div>
			);
		}
	}
}



