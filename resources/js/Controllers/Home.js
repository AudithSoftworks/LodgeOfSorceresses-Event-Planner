import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "dashboard-jumbotron-scss" */
    '../../sass/global/_dashboard_jumbotron.scss'
);

import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import Notification from '../Components/Notification';
import DiscordOauthAccount from '../Components/Users/DiscordOauthAccount';
import IpsOauthAccount from '../Components/Users/IpsOauthAccount';
import Name from '../Components/Users/Name';
import { user } from '../vendor/data';

class Home extends PureComponent {
    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        const accountStatusOptions = [<DiscordOauthAccount key='discord-oauth-status' />];
        if (me.isMember) {
            accountStatusOptions.push(<IpsOauthAccount key='ips-oauth-status' />);
        }
        accountStatusOptions.push(<Name key='ign-status' />);

        return [
            <section className="col-md-24 p-0 mb-4" key="dashboard">
                <h2 className="form-title col-md-24" title="Account Status">
                    Account Status
                </h2>
                <p className='col-md-24'>Please fix the problems below in the order they are listed, as one problem might be a blocker for another (the ones listed after it).</p>
                {[...accountStatusOptions]}
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
