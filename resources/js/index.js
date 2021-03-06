import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import '../sass/style.scss';
import configureStore, { history } from './configureStore';
import Application from './Controllers/Application';

import(/* webpackPreload: true, webpackChunkName: "react-notifications-component-theme-css" */ 'react-notifications-component/dist/theme.css');
import(/* webpackPreload: true, webpackChunkName: "animate-css" */ 'animate.css/animate.css');

const store = configureStore();
ReactDOM.render(
    <Provider store={store}>
        <Application history={history} />
    </Provider>,
    document.getElementById('root'),
);
