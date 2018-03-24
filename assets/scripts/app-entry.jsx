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