/**
 * React / WordPress / JWT token authentication
 *
 * Couldn't find any end-to-end examples of WordPress authentication with React so I built my own and I'm sharing it
 * JWT WordPress plugin
 * Need to use SSL
 * This is designed to be set up on same installation but can be done remotely
 * Need to consider CORS
 * Probably not coded well but it works and (I think) it's secure
 * Don't forget to set up permissions on WordPress' end so that people can't do unauthorised actions!!!
 * Uses sessionStorage - could use localStorage or cookie storage, whatever suits your requirements
 * Plug my portfolio / linkedin / something
 */



/**
 * About
 *
 * I couldn't find a good guide on how to integrate WordPress authentication with React, so I thought I would 
 * share this demo that I made to save others the time of figuring everything out yourself!
 *
 * I've used a WordPress plugin to handle the JWT parts on the WordPress end.
 * 
 * It can work either with a local WordPress installation or a remote one.
 *
 *
 * 
 * Pre-requisites
 *
 * - an installation of WordPress (needs to support the WordPress REST API)
 * - The "JWT Authentication for REST API" plugin for WordPress - https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/
 *
 * 
 *
 * Caution
 *
 * Make sure you use SSL when talking to the server in production! The username and password are not encrypted or 
 * hashed before sending to the server. SSL is the only thing protecting the data at the moment. It would probably be 
 * a good idea to encrypt/hash the data before sending it anyway to further prevent MitM attacks, but NEVER solely 
 * rely on any sort of client-side security when working with Javascript.
 *
 * This code works for me but it is just a demo SO YMMV. If you have any issues or questions please open an issue 
 * and I'll do my best to help.
 *
 * Don't forget to set up appropriate permissions and authorisation on any endpoints in the REST API that you want to 
 * be private.
 *
 * 
 */


import React from 'react';
import ReactDOM from 'react-dom';

import { isBrowserSupported } from './react/helpers.jsx';

import { App } from './react/app.jsx';


/**
 * Older versions of iOS don't support the fetch API, so this attempts to check for that support
 */
 try {
 	if (!isBrowserSupported()) {
 		throw "Sorry, your browser isn't supported";
 	}


 	ReactDOM.render(<App />, document.getElementById("root"));
 }
 catch (error) {
 	console.error(error);

 	document.body.innerHTML = '<h1>Sorry, your <strike>potato</strike> browser isn\'t supported because it doesn\'t support the fetch API</h1>';
 }