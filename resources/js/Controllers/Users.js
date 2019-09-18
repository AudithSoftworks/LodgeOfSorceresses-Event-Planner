import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "users-scss" */
    '../../sass/_users.scss'
    );

import { faUser, faUserCrown } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import { errorsAction, infosAction } from '../actions/notifications';
import Loading from '../Components/Loading';
import Notification from '../Components/Notification';
import { authorizeUser, filter } from '../helpers';
import { getAllUsers } from '../vendor/api';
import axios from '../vendor/axios';
import { user } from '../vendor/data';

class Users extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            filters: {
                members: true,
                soulshriven: false,
            },
            allUsers: null,
        };
        this.filter = filter.bind(this);
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    componentDidMount = () => {
        const { me } = this.props;
        if (me) {
            this.cancelTokenSource = axios.CancelToken.source();
            getAllUsers(this.cancelTokenSource)
                .then(allUsers => {
                    this.cancelTokenSource = null;
                    this.setState({ allUsers });
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = error.response.data.message || error.response.statusText || error.message;
                        this.props.dispatch(errorsAction(message));
                    }
                });
        }
    };

    renderListItem = user => {
        if (!user.isMember && !user.isSoulshriven) {
            return null;
        }
        if (user.isMember && !this.state.filters.members) return null;
        if (user.isSoulshriven && !this.state.filters.soulshriven) return null;
        let rowBgColor = user.isMember ? 'members' : 'soulshriven';

        return (
            <li className={rowBgColor + ' mb-1 mr-1'} key={'user-' + user.id} data-id={user.id}>
                <Link to={'/users/' + user.id} title="User Sheet" onClick={event => {event.preventDefault(); alert('Coming Soon!')}}>{'@' + user.name}</Link>
            </li>
        );
    };

    renderList = allUsers => {
        let charactersRendered = allUsers.result.map(userId => {
            const user = allUsers.entities['user'][userId];

            return this.renderListItem(user);
        });
        if (charactersRendered.length) {
            charactersRendered = [
                <ul key="roster" className="roster d-flex flex-row flex-wrap pl-2 pr-2 col-md-24">
                    {charactersRendered}
                </ul>,
            ];
        }

        const { filters } = this.state;
        const filterList = {
            members: (
                <button type="button" onClick={event => this.filter(event, 'members')} className={'ne-corner ' + (filters.members || 'inactive')} title="Filter Actual Members">
                    <FontAwesomeIcon icon={faUserCrown} />
                </button>
            ),
            soulshriven: (
                <button type="button" onClick={event => this.filter(event, 'soulshriven')} className={'ne-corner ' + (filters.soulshriven || 'inactive')} title="Filter Soulshriven">
                    <FontAwesomeIcon icon={faUser} />
                </button>
            ),
        };
        const actionListRendered = [];
        for (const [filterType, link] of Object.entries(filterList)) {
            actionListRendered.push(<li key={filterType}>{link}</li>);
        }

        return [
            <section className="col-md-24 p-0 mb-4" key="user-list">
                <h2 className="form-title col-md-24">Roster</h2>
                <ul className="ne-corner">{actionListRendered}</ul>
                {charactersRendered}
            </section>,
        ];
    };

    renderNoUsersFoundNotification = allUsers => {
        const { dispatch, notifications } = this.props;
        if (allUsers && !allUsers.result.length && notifications.find(n => n.key === 'no-users-found') === undefined) {
            const message = [<Fragment key="f-1">No Users Found!</Fragment>].reduce((acc, curr) => [acc, ' ', curr]);
            dispatch(
                infosAction(
                    message,
                    {
                        container: 'bottom-center',
                        animationIn: ['animated', 'bounceInDown'],
                        animationOut: ['animated', 'bounceOutDown'],
                        dismiss: { duration: 30000 },
                    },
                    'no-users-found'
                )
            );
        }
    };

    render = () => {
        const { me, groups, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        if (me && groups && !authorizeUser(this.props, true)) {
            return <Redirect to='/' />;
        }

        const { allUsers } = this.state;
        if (!allUsers) {
            return [<Loading message="Fetching Roster information..." key="loading" />, <Notification key="notifications" />];
        }
        this.renderNoUsersFoundNotification(allUsers);

        return [...this.renderList(allUsers), <Notification key="notifications" />];
    };
}

Users.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    groups: PropTypes.object,
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Users);
