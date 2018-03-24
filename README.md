
# React / WordPress / JWT Demo
### Simple demo showing how to use React on the front-end, WordPress on the back-end, WP REST API to communication between the two and JSON Web Tokens for authentication
I couldn't find a good guide on how to integrate WordPress authentication with React, so I thought I would share this demo that I made to save others the time of figuring everything out yourself!

I've used a WordPress plugin to handle the JWT parts on the WordPress end (link below).

This repo is a WordPress theme, which saves having to set up two separate websites (front-end and back-end) for testing. It should be simple to pull out the React parts and put it in to a standalone website, and then set up a remote WordPress installation as the back-end.

**As this repo is a WordPress theme, most of the files are used for the theme. If you just want the Javascript code, it's in assets/scripts/.**

## Pre-requisites
- an installation of WordPress (needs to support the WordPress REST API)
- The "JWT Authentication for REST API" plugin for WordPress - https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/.

## Installation
As this is a WordPress theme, clone it in to your themes directory. Then just run `bower install` then `npm install`

This will grab all of your dependencies and will install gulp. You can get your browser to auto-refresh by running `gulp watch`.

You'll need to use gulp or a similar tool to transpose the code - it's set up using JSX, with gulp running browserify and babelify to get it ready for use.

## Notes
Make sure you use SSL when talking to the server in production! The username and password are not encrypted or 
hashed before sending to the server. SSL is the only thing protecting the data at the moment. It would probably be 
a good idea to encrypt/hash the data before sending it anyway to further prevent MitM attacks, but NEVER solely 
rely on any sort of client-side security when working with Javascript.

 This code works for me but it is just a demo SO YMMV. If you have any issues or questions please open an issue and I'll do my best to help. 

Don't forget to set up appropriate permissions and authorisation on any endpoints in the REST API that you want to be private.

I've used the Session Storage API to store the JWT token on the client, you can easily switch this out for whatever you like e.g. Local Storage API, cookies.

The WordPress theme is built using the fantastic Sage framework (https://github.com/roots/sage/tree/8.5.0) by Roots.