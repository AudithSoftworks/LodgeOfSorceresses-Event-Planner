// noinspection ES6CheckImport
import { ConnectedRouter } from 'connected-react-router/immutable';
import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Footer from './Layout/Footer';
import Header from './Layout/Header';
import Main from './Layout/Main';

class Application extends Component {
    render = () => {
        const { history } = this.props;
        return (
            <ConnectedRouter history={history}>
                <Header />
                <Main />
                <Footer />
            </ConnectedRouter>
        );
    };
}

Application.propTypes = {
    history: PropTypes.object,
};

Application.contextTypes = {
    store: PropTypes.object,
};

export default Application;
