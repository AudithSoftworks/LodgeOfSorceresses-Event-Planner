import '../sass/style.scss';
import(
    /* webpackPreload: true */
    /* webpackChunkName: "react-notifications-component-theme-css" */
    'react-notifications-component/dist/theme.css');
import(
    /* webpackPreload: true */
    /* webpackChunkName: "animate-css" */
    'animate.css/animate.css');

import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router } from "react-router-dom";
import Application from './Components/Application';

const routing = (
    <Router>
        <Application/>
    </Router>
);

ReactDOM.render(routing, document.getElementById('root'));
