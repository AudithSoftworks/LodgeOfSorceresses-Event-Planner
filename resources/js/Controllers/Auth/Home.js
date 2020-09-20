import(/* webpackPrefetch: true, webpackChunkName: "dashboard-scss" */ '../../../sass/_dashboard.scss');

import { DateTime } from 'luxon';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import { errorsAction } from '../../actions/notifications';
import * as Attendance from '../../Components/Events/Attendance';
import Notification from '../../Components/Notification';
import { authorizeUser } from '../../helpers';
import { getAttendances } from '../../vendor/api';
import axios from '../../vendor/axios';
import { characters, user } from '../../vendor/data';

class Home extends PureComponent {
    constructor(props) {
        super(props);
        this.authorizeUser = authorizeUser.bind(this);
        this.state = {
            attendances: null,
        };
    }

    componentDidMount = () => {
        const { dispatch, me } = this.props;
        if (me) {
            this.cancelTokenSource = axios.CancelToken.source();
            getAttendances(this.cancelTokenSource, me.id)
                .then(attendances => {
                    this.cancelTokenSource = null;
                    if (attendances) {
                        const attendancesArray = Array.from(attendances.body.result, id => attendances.body.entities['attendance'][id]);
                        this.setState({
                            attendances: attendancesArray,
                        });
                    }
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = (error.response && (error.response.data.message || error.response.statusText)) || error.message;
                        dispatch(errorsAction(message));
                    }
                });
        }
    };

    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    }

    render = () => {
        const { me, location, myCharacters } = this.props;
        if (!me || !myCharacters) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname }}} />;
        } else if (!this.authorizeUser(true)) {
            return <Redirect to="/home" />;
        }
        const { attendances } = this.state;
        let startDate = undefined;
        let endDate = undefined;
        if (attendances !== null && attendances instanceof Array) {
            startDate = attendances.length ? DateTime.fromISO(attendances[attendances.length - 1]['created_at']) : null;
            endDate = attendances.length ? DateTime.fromISO(attendances[0]['created_at']) : null;
        }

        return [
            <section className="col-md-13 col-lg-17 p-0 mb-4 dashboard" key="dashboard">
                <h2 className="form-title col-md-24 pr-5" title="Welcome!">
                    Welcome, {me.name || 'Soulless One'}!
                </h2>
                <h3 className="col-md-24 mt-2">Account Summary</h3>
                <dl className={me.isMember ? 'members' : 'soulshriven'}>
                    <dt>Account Type</dt>
                    <dd>
                        {me.isMember ?
                            [
                                <Fragment key="item-1">Member</Fragment>,
                                <small key="item-2">
                                    [<Link to="/onboarding/soulshriven">switch</Link>]
                                </small>,
                            ] :
                            [
                                <Fragment key="item-1">Soulshriven</Fragment>,
                                <small key="item-2">
                                    [<Link to="/onboarding/members">switch</Link>]
                                </small>,
                            ]}
                    </dd>
                </dl>
                <dl className={me.linkedAccountsParsed.ips ? 'info' : 'danger'}>
                    <dt>Forum Account Linked</dt>
                    <dd>
                        {me.linkedAccountsParsed.ips ?
                            [
                                <Fragment key="item-1">Yes</Fragment>,
                                <small key="item-2">
                                    [<a href="/oauth/to/ips">refresh it</a>]
                                </small>,
                            ] :
                            [
                                <Fragment key="item-1">No</Fragment>,
                                <small key="item-2">
                                    [<a href="/oauth/to/ips">link now</a>]
                                </small>,
                            ]}
                    </dd>
                </dl>
                <dl className={me.characters.length > 0 ? 'info' : 'danger'}>
                    <dt># of characters</dt>
                    <dd>
                        {me.characters.length}{' '}
                        <small>
                            [<Link to="/@me/characters">manage</Link>]
                        </small>
                    </dd>
                </dl>
                <dl className={me.clearanceLevel ? me.clearanceLevel.slug : 'danger'}>
                    <dt>Overall Rank</dt>
                    <dd>
                        {me.clearanceLevel ?
                            me.clearanceLevel.rank.title :
                            [
                                <Fragment key="item-1">None</Fragment>,
                                <small key="item-2">
                                    [<Link to="/@me/characters">get going</Link>]
                                </small>,
                            ]}
                    </dd>
                </dl>
                <Attendance.ListView start={startDate} end={endDate} events={attendances} heading='My Attendances' key="attendances" />
                <article className='col-md-24 mt-5'>
                    <h3>Important Readings</h3>
                    <ul>
                        <li>
                            <a href='https://lodgeofsorceresses.com/topic/423-guild-introduction-lodge-of-sorceresses/' target='_blank' rel='noreferrer'>Guild Introduction</a>
                        </li>
                        <li>
                            <a href='https://lodgeofsorceresses.com/topic/423-guild-introduction-lodge-of-sorceresses/?do=findComment&comment=12160' target='_blank' rel='noreferrer'>
                                Guild Requirements (for Members)
                            </a>
                        </li>
                        <li>
                            <a href='https://lodgeofsorceresses.com/topic/5741-pve-content-clearance-guide/' target='_blank' rel='noreferrer'>Tier-based Content Clearance Model</a>
                        </li>
                        <li>
                            <a href='https://lodgeofsorceresses.com/topic/5506-endgame-progression-guidelines-for-cores-upd-2020-02-18/' target='_blank' rel='noreferrer'>
                                Endgame Attendance Guidelines for Raid Cores
                            </a>
                        </li>
                        <li>
                            <a href='https://lodgeofsorceresses.com/topic/423-guild-introduction-lodge-of-sorceresses/?do=findComment&comment=10741' target='_blank' rel='noreferrer'>
                                What is Open Initiative (aka Soulshriven project)?
                            </a>
                        </li>
                        <li><a href='https://lodgeofsorceresses.com/topic/5751-open-events-organization-guidelines/' target='_blank' rel='noreferrer'>Open Events Organization Guidelines</a></li>
                    </ul>
                </article>
            </section>,
            <aside
                key="member-onboarding"
                data-heading="Here you can ..."
                data-text={'* Link forum account to Guild Planner\u000A* Switch your membership mode\u000A* Track your Attendance'}
                className="banner col-md-11 col-lg-7 d-none d-sm-inline-block"
            />,
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
    myCharacters: characters,
    notifications: PropTypes.array,

    dispatch: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(['axiosCancelTokenSource']),
    me: state.getIn(['me']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(mapStateToProps, mapDispatchToProps)(Home);
