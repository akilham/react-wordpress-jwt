import React from 'react';
import { Route, withRouter, Redirect } from 'react-router';

import { Spinner } from './helpers.jsx';



/**
 * Login page component
 */
 export default class Login extends React.Component {
 	constructor(props) {
 		super(props);

 		this.state = {
 			redirectToReferrer: false,
 			error: false,
 			errorMessage: '',
 		};
 	}

 	processLogin(event) {
 		this.setState( {
 			error: false,
 			loggingIn: true
 		});

 		this.props.processLogin(this.username.value, this.password.value)
 		.then( (response) => {
			this.setState( {
				loggingIn: false
			});
		})
 		.catch( (error) => {
 			console.log('Login error', error);

 			if (error.data.status === 403) {
 				this.setState( {
 					error: true,
 					errorMessage: 'Your username or password are incorrect.',
 					loggingIn: false,
 				});
 			}
 			else {
 				this.setState( {
 					error: true,
 					errorMessage: 'Sorry, an error occured.',
 					loggingIn: false,
 				});

 				throw new Error('Login error');
 			}
 		});

 		event.preventDefault();
 	}

 	checkToken() {
 		this.props.checkToken();
 	}

 	logOut() {
 		this.props.logOut();
 	}

 	render() {
 		console.log('Login render');
 		const { from } = this.props.location.state || { from: { pathname: '/private-page' } }

		if (this.props.state.isAuthenticated && !this.props.state.isCheckingAuth) {
			return (
				<Redirect to={from} />
			);
		}


		var errorBox, spinner;

		if (this.state.error) {
			errorBox = (
				<div className="text error">{ this.state.errorMessage }</div>
			);
		}

		if (this.state.loggingIn) {
			spinner = (
				<Spinner />
			);
		}

		return (
			<div className="route login">
				<div className="login-form">
					<h3>React / WordPress / JWT Demo</h3>
					<h6>By Andrew Kilham</h6>

					<p>This is a demo showing how you could use React on the front-end, WordPress on the back-end and JWT tokens for authentication</p>

					<p>See <a href="https://github.com/akilham/react-wordpress-jwt" target="_blank">GitHub repo</a> for more info.</p>

					<div className="message">
						{errorBox}
						{spinner}
					</div>

					<form onSubmit={this.processLogin.bind(this)}>
						<label>Username</label> <input type="text" ref={(input) => this.username = input} /> <br />
						<label>Password</label> <input type="password" ref={(input) => this.password = input} /> <br />
						<button className="btn">Login</button>
					</form>
				</div>
			</div>
		);
	}
}