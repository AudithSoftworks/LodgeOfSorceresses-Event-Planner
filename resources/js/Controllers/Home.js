import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "dashboard-jumbotron-scss" */
    '../../sass/global/_dashboard_jumbotron.scss'
);

import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import putUserAction from "../actions/put-user";
import Notification from '../Components/Notification';
import { authorizeUser } from "../helpers";
import { user } from '../vendor/data';

class Home extends PureComponent {
    constructor(props) {
        super(props);
        this.authorizeUser = authorizeUser.bind(this);
    }

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    handleSubmit = event => {
        event.preventDefault();
        const { putUserAction } = this.props;
        const data = new FormData(event.target);

        return putUserAction(data);
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        const accountStatusOptions = [];
        if (!me.linkedAccountsParsed || !me.linkedAccountsParsed.discord) {
            accountStatusOptions.push(
                <article key='discord-oauth' className='jumbotron danger ml-2 mr-2' data-cy='account-status-element'>
                    <h3>Your Discord Account:</h3>
                    <p>Not Linked</p>
                    <small>You won't be able to use Planner, until Discord is linked to it. <a href='/oauth/to/discord'>Click here</a> to fix this problem.</small>
                </article>
            );
        } else if (me.linkedAccountsParsed && me.linkedAccountsParsed.discord && !this.authorizeUser()) {
            accountStatusOptions.push(
                <article key='pre-check-failed' className='jumbotron danger ml-2 mr-2' data-cy='account-status-element'>
                    <small>Pre-check failed! Make sure you have <i>Soulshriven</i> or <i>Member</i> tag on Lodge Discord server.</small>
                    <small>Please check #guests-read-me channel on Discord to see your options.</small>
                    <small>Please contact guild leader on Discord if you need a help.</small>
                    <small>You won't be able to use Guild Planner until this is addressed!</small>
                </article>
            );
        } else if (me.isMember && !me.linkedAccountsParsed.ips) {
            accountStatusOptions.push(
                <article key='forum-oauth' className='jumbotron danger ml-2 mr-2' data-cy='account-status-element'>
                    <h3>Your Lodge Forum Account:</h3>
                    <p>Not Linked</p>
                    <small>If you are an actual member of Lodge in-game, you need to register at Lodge Forum as well (by logging in via Discord).</small>
                    <small>We are temporarily using the Calendar there (until Planner Calendar is operational).</small>
                    <small>If you don't have a Forum account created, <a href='https://lodgeofsorceresses.com' target='_blank'>click here to create one</a> (link opens in a new tab).</small>
                    <small>Once your Forum account is ready, <a href='/oauth/to/ips'>click here</a> to link it to Planner.</small>
                </article>
            );
        } else if (!me.name || !me.name.length) {
            accountStatusOptions.push(
                <form key='eso-id-form' className="jumbotron danger ml-2 mr-2" onSubmit={this.handleSubmit} data-cy='account-status-element'>
                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                    <h3>Your ESO ID:</h3>
                    <input type='text' name='name' required placeholder='e.g. Glevissig (Gelmir)' />
                    <input type='submit' value='Save' className='btn btn-info' />
                    <small>Enter your ESO ID exactly as it is (omit @ sign). Feel free to add an easy-to-pronounce nickname for yourself (to be called with) in parentheses.</small>
                </form>
            );
        }

        if (!accountStatusOptions.length) {
            accountStatusOptions.push(
                <article key='eso-id' className='jumbotron success ml-2 mr-2' data-cy='account-status-element'>
                    <h3>Your ESO ID:</h3>
                    <p>{'@' + me.name}</p>
                    <small className='half-transparent'>To update your ESO ID, please contact the guild leader on Discord.</small>
                </article>
            );
        }

        return [
            <section className="col-md-24 p-0 mb-4" key="dashboard">
                <h2 className="form-title col-md-24" title="Account Status">
                    Account Status
                </h2>
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

    putUserAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    me: state.getIn(['me']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    putUserAction: data => dispatch(putUserAction(data)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Home);
