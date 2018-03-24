import React from 'react';
import { Route, withRouter, Redirect } from 'react-router';
import deepmerge from 'deepmerge';



/**
 * Allows us to pass props in to Route components from React Router
 * Source: https://github.com/ReactTraining/react-router/issues/4105#issuecomment-289195202
 */
export const PropsRoute = ({ component, ...rest }) => {
	return (
		<Route {...rest} render={routeProps => {
			return renderMergedProps(component, routeProps, rest);
		}}/>
	);
}

export const renderMergedProps = (component, ...rest) => {
	const finalProps = Object.assign({}, ...rest);

	return (
		React.createElement(component, finalProps)
	);
}




/**
 * A Higher Order Component wrapper, primarily used for routing
 *
 * At the moment it's just an alias for calling withRouter(requireAuthentication(Component))
 * But you could add whatever you want here
 */
export function wrapper(Component, options) {
	return withRouter(requireAuthentication(Component));
}

/**
 * A Higher Order Component (HOC) that handles authentication
 */
export function requireAuthentication(Component) {

	class AuthenticatedComponent extends React.Component {
		constructor(props) {
			super(props);
		}

		render() {
			if (this.props.state.isAuthenticated) {
				if (this.props.state.hasLoadedData) {
					console.log('auth HOC: User is authenticated and data is loaded');

					return (
						<Component {...this.props} />
					);
				}
				else {
					console.log('auth HOC: User is authenticated but data is still being loaded');

					return (
						<Spinner />
					);
				}
			}
			else if (!this.props.state.isAuthenticated && this.props.state.isCheckingAuth) {
				console.log('auth HOC: User auth is currently being checked');

				return (
					<Spinner />
				);
			}
			else {
				console.log('auth HOC: User is *NOT* authenticated');

				return (
					<Component {...this.props} />
				);
			}
		}
	}

	return AuthenticatedComponent;
}



/**
 * A basic Spinner component for when we load data
 */
export function Spinner() {
	return (
		<div className="spinner loading">
			<div className="rect1"></div>
			<div className="rect2"></div>
			<div className="rect3"></div>
			<div className="rect4"></div>
			<div className="rect5"></div>
		</div>
	);
}



/**
 * Displays a list of posts from WordPress
 *
 * Used in PublicPage and PrivatePage
 */
export class PostList extends React.Component {
	constructor(props) {
		super(props);
	}

	render() {
		const listItems = this.props.posts.map((post) => {
			return (
				<PostItem post={post} key={post.id} />
			);
		} );
		return (
			<div className="posts">
				<div className="posts-list">
					{listItems}
				</div>
			</div>
		);
	}
}

/**
 * Displays the title of a post from WordPress
 *
 * Called by PostList
 */
function PostItem(props) {
	return (
		<div className='post'>
			<h5>{ props.post.title.rendered }</h5>
		</div>
	);
}



/**
 * A simple wrapper class for fetch() so that we can set some defaults
 *
 * @param  {string} 	path 			the local URL to work with
 * @param  {object} 	params 			optional object containing parameters
 * @return {Promise} 					a Promise that will resolve once the request has completed
 */
export const wpApi = (path, params) => {

	// adds the base WP API url to our path
	const url = siteData.site_url + '/wp-json' + path;

	var headers = {
		'Content-Type': 'application/json'
	}

	// skip adding the Authorization header if requested
	if (!params.noAuth) {
		// add the JWT token for authentication if it exists
		var jwtToken = sessionStorage.getItem('jwtToken');

		if (jwtToken !== undefined && jwtToken !== '' && jwtToken !== null) {
			headers['Authorization'] = 'Bearer ' + jwtToken;
		}
	}

	// adds headers to tell the server that we're working with JSON
	const p2 = deepmerge(params, {
		headers: headers
	});

	return fetch(url, p2);
}



/**
 * Helper function to check the validity of a JWT token with WordPress
 *
 * @param  {string} 	token 			the JWT token to check
 * @return {Promise} 					a Promise that will resolve once the request has completed
 */
wpApi.checkToken = (token) => {
 	return wpApi('/jwt-auth/v1/token/validate', {
 		method: 'POST',
 		noAuth: true,
 		headers: { 'Authorization': 'Bearer ' + token },
 	})
 	.then( (response) => {
 		return response.json();
 	});
}

/**
 * Helper function to handle the WordPress login
 *
 * @param  {string} 	username
 * @param  {string} 	password
 * @return {Promise} 					a Promise that will resolve once the request has completed
 */
wpApi.doLogin = (username, password) => {
 	return wpApi('/jwt-auth/v1/token', {
 		method: 'POST',
 		noAuth: true,
 		body: JSON.stringify( {
 			username: username,
 			password: password
 		} )
 	})
 	.then( (response) => {
 		return response.json();
 	});
}



/**
 * Checks if the browser supports the fetch API, which we need to talk to the server
 * @return {Boolean}
 */
export function isBrowserSupported() {
	return (fetch !== undefined);
}





