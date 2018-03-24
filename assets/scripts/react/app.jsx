import React from 'react';
import ReactDOM from 'react-dom';

import ReactRouter, { withRouter } from 'react-router';
import { HashRouter, Route, Link, Switch, Redirect, NavLink } from 'react-router-dom';

import { PropsRoute, wrapper, wpApi } from './helpers.jsx';
import Login from './login.jsx';
import PrivatePage from './privatepage.jsx';
import PublicPage from './publicpage.jsx';




/**
 * Our core App component
 */
export class App extends React.Component {
 	constructor(props) {
 		super(props);

		this.state = {
			isAuthenticated: false,
			isCheckingAuth: true,
			hasLoadedData: false,
			jwtToken: null,
			displayName: '',
			niceName: '',
			email: '',

			postsLoaded: false,
			posts: [],
		}
	}

	componentDidMount() {
		// do an initial check of the token validity
		this.isAuthed()
		.then( json => {
			return this.__doDataSetupAfterAuthCheck();
		})
		.catch( e => {
			return this.__doDataSetupAfterAuthCheck();
		});
	}


	/**
	 * Sends the username and password off to the server to get validated
	 * 
	 * @param  {string} username
	 * @param  {string} password
	 */
	processLogin(username, password) {
		return wpApi.doLogin(username, password)
		.then( (json) => {
			if (json.hasOwnProperty('token')) {
				this.__storeToken(json);

				return json;
			}
			else {
				return Promise.reject(json);
			}
		})
		.then( (data) => {
			return this.__doDataSetupAfterAuthCheck();
		});
	}


	__storeToken(data) {
		sessionStorage.setItem('jwtToken', data.token);

		var userData = {
			displayName: data.user_display_name,
			niceName: data.user_nicename,
			email: data.user_email
		};

		sessionStorage.setItem('userData', JSON.stringify(userData));

		this.__setAuthedFromSession(data.token);
	}


	/**
	 * Log the user out, which deletes the session data and JWT token
	 */
	 logOut() {
	 	sessionStorage.removeItem('jwtToken');
	 	sessionStorage.removeItem('userData');

	 	this.setState( {
	 		jwtToken: '',
	 		isAuthenticated: false,
	 		isCheckingAuth: false,
	 		displayName: '',
	 		niceName: '',
	 		email: '',
	 	});

	 	console.log('logOut(): User data has been deleted');
	}


	/**
	 * Checks to see if a user is authenticated or not
	 *
	 * Check if there is a JWT token in session storage, and if there is, send it to the server to get validated
	 */
	isAuthed() {
		var jwtToken = sessionStorage.getItem('jwtToken');

		if (jwtToken !== null) {
			return wpApi.checkToken(jwtToken)
			.then( (json) => {
				if (json.code === 'jwt_auth_valid_token') {
					console.info('SUCCESS: user has a valid token');

					this.__setAuthedFromSession(jwtToken);

					return json;
				}
				else {
					console.error('ERROR: user is NOT authed, token found but invalid');

					this.logOut();

					return Promise.reject('ERR user is NOT authed, token found but invalid');
				}
			});
		}
		else {
			this.logOut();

			return Promise.reject('No token found.');
		}
	}


	/**
	 * Updates the app with user info from sessionStorage
	 * @param {string} jwtToken  		The JWT token for the user
	 */
	__setAuthedFromSession(jwtToken) {
		var userData = JSON.parse(sessionStorage.getItem('userData')) || {};

		this.setState( {
			jwtToken: jwtToken,
			isAuthenticated: true,
			isCheckingAuth: false,
			displayName: userData.displayName || '',
			niceName: userData.niceName || '',
			email: userData.email || '',
		});
	}



	/**
	 * This is called after the user has been authenticated
	 * Call anything you want here (e.g. more API calls to load posts)
	 * Anything you put here needs to return a promise
	 * If you only want to call something if the user passed the auth check, just add a conditional statement here
	 */
	__doDataSetupAfterAuthCheck() {
	 	return Promise.all( [ this.loadPosts() ])
	 	.then( result => {
	 		this.setState( { hasLoadedData: true } );
	 	} );
	 }




	/**
	 * Gets all of the posts stored in WordPress and stores them for later use
	 *
	 * The method used below to retrieve multiple pages is a bit hacky - it can definitely be improved on
	 */
	loadPosts() {
	 	const perPage = 40;

	 	const p = wpApi('/wp/v2/posts?orderby=title&per_page=' + perPage + '&page=1', { method: 'GET' } )
	 	.then( (response) => {
	 		const totalPages = response.headers.get('x-wp-totalpages');

	 		if (totalPages > 1) {
	 			var promises = [ response.json() ];

	 			for (var i = 2; i <= totalPages; i++) {
	 				promises.push( wpApi('/wp/v2/posts?orderby=title&per_page=' + perPage + '&page=' + i, { method: 'GET' } )
	 					.then( (response) => {
	 						return response.json();
	 					})
	 					.then( (json) => {
	 						return json;
	 					}) );
	 			}


	 			return Promise.all( promises )
	 			.then( responses => {
	 				return [].concat.apply([], responses);
	 			});

	 		}
	 		else {
	 			return response.json();
	 		}
	 	})
	 	.then( (json) => {
	 		this.setState( {
	 			postsLoaded: true,
	 			posts: json,
	 		} );

	 		return json;
	 	});

	 	return p;
	 }


	render() {
		let userInfo;

		if (this.state.isAuthenticated) {
			userInfo = (
				<div>
					Hi {this.state.displayName} &nbsp; &nbsp; &nbsp;

					<button className="btn btn-small" onClick={() => { this.logOut(); }}>Log Out</button>
				</div>
			);
		}
		else {
			userInfo = (
				<div>Not logged in</div>
			);
		}


 		return (
 			<ErrorBoundary>
 				<HashRouter>
 					<div className="app">
			 			<div className="route page">
				 			<header className="site-header">
				 				<ul className="menu">
				 					{ !this.state.isAuthenticated && (<li><NavLink to="/login">Login</NavLink></li>) }
									<li><NavLink to="/public-page">Public page</NavLink></li>
									<li><NavLink to="/private-page">Private page</NavLink></li>
				 				</ul>

					 			<div className="user-info">
					 				{ userInfo }
					 			</div>
				 			</header>
				 			<div className="wrap container" role="document">
					 			<div className="content row">
						 			<main className="main">
								 			<Switch>
								 				<PropsRoute path="/login" component={Login} state={this.state} processLogin={this.processLogin.bind(this)} />
									 			<PropsRoute path="/private-page" component={wrapper(PrivatePage)} state={this.state} />
									 			<PropsRoute path="/public-page" component={PublicPage} state={this.state} />
									 			<Redirect to="/private-page" />
								 			</Switch>
						 			</main>
					 			</div>
				 			</div>
			 			</div>
			 		</div>
		 		</HashRouter>
 			</ErrorBoundary>
 		);
	}
}





/**
 * Default error boundary so that we can get more descriptive errors
 */
export class ErrorBoundary extends React.Component {
 	constructor(props) {
 		super(props);
 		this.state = { hasError: false };
 	}

 	componentDidCatch(error, info) {
	    // Display fallback UI
	    this.setState({ hasError: true });
	}

	render() {
		if (this.state.hasError) {
	      	// You can render any custom fallback UI
	      	return (
	      		<div>
		      		<h1>Something went wrong.</h1>

		      		<h4>We've notified the code monkeys.</h4>
	      		</div>
	      	);
	    }

	    return this.props.children;
	}
}



