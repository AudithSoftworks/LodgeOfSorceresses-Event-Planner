import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "dashboard-jumbotron-scss" */
    '../../sass/global/_dashboard_jumbotron.scss'
    );

import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import Notification from '../Components/Notification';
import DiscordOauthAccount from "../Components/Users/DiscordOauthAccount";
import Name from "../Components/Users/Name";
import { user } from '../vendor/data';

library.add(faDiscord);

class Home extends PureComponent {
    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        return [
            <section className="col-md-24 p-0 mb-4" key="dashboard">
                <h2 className="form-title col-md-24" title="Account Status">
                    Account Status
                </h2>
                <DiscordOauthAccount />
                <Name />
            </section>,
            <Notification key="notifications" />,
        ];
    };
}

Home.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    notifications: state.getIn(['notifications']),
});

export default connect(mapStateToProps)(Home);
