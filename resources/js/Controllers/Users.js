import(/* webpackPrefetch: true, webpackChunkName: "users-scss" */ '../../sass/_users.scss');

import { faChevronCircleLeft } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { DateTime } from 'luxon';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import { errorsAction, infosAction, warningsAction } from '../actions/notifications';
import List from '../Components/Characters/List';
import * as Attendance from '../Components/Events/Attendance';
import Loading from '../Components/Loading';
import Notification from '../Components/Notification';
import { filter, renderActionList } from '../helpers';
import { getAllUsers, getAttendances, getUser } from '../vendor/api';
import axios from '../vendor/axios';
import { user } from '../vendor/data';

class Users extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            allUsers: null,
            user: null,
            attendances: null, // [empty array = no attendances found; null = attendances to be loaded]
            firstAttendanceDate: null,
        };
        this.filter = filter.bind(this);
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Request cancelled.');
    };

    componentDidMount = () => {
        this.fetchData();
    };

    componentDidUpdate = prevProps => {
        const { match } = this.props;
        if (match.params.id && match.params.id !== prevProps.match.params.id) {
            this.setState({
                user: null,
                attendances: null,
                firstAttendanceDate: null,
            });
            this.fetchData();
        }
    };

    fetchData = () => {
        const { dispatch, history, me, match } = this.props;
        if (me) {
            const { allUsers } = this.state;
            this.cancelTokenSource = axios.CancelToken.source();
            let promiseRet = undefined;
            if (match.params.id) {
                promiseRet = getUser(this.cancelTokenSource, match.params.id)
                    .then(user => {
                        if (!user.result) {
                            dispatch(errorsAction('User not found.'));

                            return history.goBack();
                        }
                        this.setState({
                            user: user.entities.user[match.params.id],
                        });
                        getAttendances(this.cancelTokenSource, match.params.id)
                            .then(attendances => {
                                this.cancelTokenSource = null;
                                if (attendances) {
                                    const attendancesArray = Array.from(attendances.body.result, id => attendances.body.entities['attendance'][id]);
                                    const firstAttendanceDateHeader = attendances.headers['x-first-attendance-date'];
                                    if (attendancesArray.length === 0) {
                                        const message = [];
                                        if (firstAttendanceDateHeader) {
                                            message.push(
                                                <Fragment key="f-1">No attendance records were found for last 3 weeks. </Fragment>,
                                                <Fragment key="f-2">Feel free to load older records a week at a time.</Fragment>,
                                            );
                                        } else {
                                            message.push(
                                                <Fragment key="f-1">User has no attendance records.</Fragment>,
                                            );
                                        }
                                        dispatch(
                                            infosAction(
                                                message,
                                                {
                                                    container: 'bottom-center',
                                                    animationIn: ['animated', 'bounceInDown'],
                                                    animationOut: ['animated', 'bounceOutDown'],
                                                    dismiss: { duration: 30000 },
                                                    width: 350,
                                                },
                                                'no-attendances-at-first',
                                            ),
                                        );
                                    }
                                    this.setState({
                                        attendances: attendancesArray,
                                        firstAttendanceDate: firstAttendanceDateHeader,
                                    });
                                }
                            })
                            .catch(error => {
                                throw error;
                            });
                    });
            } else if (!allUsers) {
                promiseRet = getAllUsers(this.cancelTokenSource)
                    .then(allUsers => {
                        if (!allUsers.result.length) {
                            dispatch(
                                infosAction(
                                    'No Users Found!',
                                    {
                                        container: 'bottom-center',
                                        animationIn: ['animated', 'bounceInDown'],
                                        animationOut: ['animated', 'bounceOutDown'],
                                        dismiss: { duration: 30000 },
                                    },
                                    'no-users-found',
                                ),
                            );
                        }
                        this.cancelTokenSource = null;
                        this.setState({ allUsers, user: null });
                    });
            }
            if (promiseRet !== undefined && promiseRet instanceof Promise) {
                promiseRet.catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = error.response.data.message || error.response.statusText || error.message;
                        dispatch(errorsAction(message));
                    }
                });
            }
        }
    };

    loadMore = event => {
        const { dispatch, match } = this.props;
        const currentTarget = event.currentTarget;
        currentTarget.setAttribute('disabled', true);
        this.cancelTokenSource = axios.CancelToken.source();
        const dataBeforeAttrValue = currentTarget.getAttribute('data-before');
        getAttendances(this.cancelTokenSource, match.params.id, { b: dataBeforeAttrValue })
            .then(attendances => {
                this.cancelTokenSource = null;
                if (attendances) {
                    const attendancesArray = Array.from(attendances.body.result, id => attendances.body.entities['attendance'][id]);
                    if (attendancesArray.length === 0) {
                        dispatch(warningsAction('Reached the end of the attendance list.'));
                        currentTarget.remove();

                        return;
                    }
                    currentTarget.removeAttribute('disabled');
                    this.setState({
                        attendances: [...this.state.attendances, ...attendancesArray],
                    });
                }
            })
            .catch(error => {
                const message = (error.response && (error.response.data.message || error.response.statusText)) || error.message;
                dispatch(errorsAction(message));
            });
    };

    renderItem = user => {
        const { history, me } = this.props;
        if (!user.isMember && !user.isSoulshriven) {
            return history.push('/users');
        }

        const actionList = {
            return: (
                <Link to={'/users'} title="Back to Roster">
                    <FontAwesomeIcon icon={faChevronCircleLeft} />
                </Link>
            ),
        };
        const { attendances, firstAttendanceDate } = this.state;
        let loadMoreAttendancesButton = null;
        let startDate = undefined;
        let endDate = undefined;
        if (attendances !== null && attendances instanceof Array) {
            startDate = attendances.length ? DateTime.fromISO(attendances[attendances.length - 1]['created_at']) : null;
            endDate = attendances.length ? DateTime.fromISO(attendances[0]['created_at']) : null;
            loadMoreAttendancesButton = firstAttendanceDate
                ? <button key='load-more-button'
                    className='btn btn-primary btn-sm ml-auto mr-auto'
                    data-before={
                        startDate
                            ? startDate.toISO()
                            : DateTime.local().minus({ weeks: 3 }).startOf('week').toISO()
                    }
                    onClick={event => this.loadMore(event)}>load older records</button>
                : <button key='load-more-button'
                    className='btn btn-primary btn-sm ml-auto mr-auto'
                    disabled={true}>nothing to load</button>;
        }

        const characterListRendered = user.characters.length
            ? [
                <h3 className="col-md-24 mt-5" key="heading">Their Characters</h3>,
                <List characters={user.characters} me={me} className="pl-2 pr-2 col-md-24" key="character-list" />,
            ] : [];

        return [
            <section className="col-md-24 p-0 mb-4 user-profile" key="user-profile">
                <h2 className="form-title col-md-24 pr-5" title="Welcome!">
                    {'@' + user.name}
                </h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <h3 className="col-md-24 mt-2">Account Summary</h3>
                <dl className={user.isMember ? 'members' : 'soulshriven'}>
                    <dt>Account Type</dt>
                    <dd>{user.isMember ? 'Member' : 'Soulshriven'}</dd>
                </dl>
                <dl className={user.linkedAccountsParsed.ips ? 'info' : 'danger'}>
                    <dt>Forum Account Linked</dt>
                    <dd>{user.linkedAccountsParsed.ips ? 'Yes' : 'No'}</dd>
                </dl>
                <dl className={user.characters.length > 0 ? 'info' : 'danger'}>
                    <dt># of characters</dt>
                    <dd>{user.characters.length}</dd>
                </dl>
                <dl className={user.clearanceLevel ? user.clearanceLevel.slug : 'danger'}>
                    <dt>Overall Rank</dt>
                    <dd>{user.clearanceLevel ? user.clearanceLevel.rank.title : 'None'}</dd>
                </dl>
                {[...characterListRendered]}
                <Attendance.ListView start={startDate} end={endDate} events={attendances} key="attendances" />
                {loadMoreAttendancesButton}
            </section>,
        ];
    };

    renderListItem = user => {
        if (!user.isMember && !user.isSoulshriven) {
            return null;
        }
        const rowBgColor = user.clearanceLevel ? user.clearanceLevel.slug : 'no-clearance';

        return (
            <li className={rowBgColor + ' mb-1 mr-1'} key={'user-' + user.id} data-id={user.id}>
                <Link to={'/users/' + user.id} title="User Sheet">
                    {'@' + user.name}
                </Link>
            </li>
        );
    };

    renderList = (allUsers, mode = 'isMember') => {
        if (allUsers === null) {
            return null;
        }
        let charactersRendered = allUsers.result
            .map(userId => {
                const user = allUsers.entities['user'][userId];
                if (user[mode] === false) {
                    return null;
                }

                return this.renderListItem(user);
            })
            .filter(item => item !== null);
        const numberOfCharacters = charactersRendered.length;
        if (numberOfCharacters) {
            charactersRendered = [
                <ul key="roster" className="roster d-flex flex-row flex-wrap pl-2 pr-2 col-md-24">
                    {charactersRendered}
                </ul>,
            ];
        }

        return [
            <section className="col-md-24 p-0 mb-4" key={'user-list-' + mode}>
                <h3 className="form-title col-md-24">{mode === 'isMember' ? 'Members' : 'Soulshriven'} ({numberOfCharacters})</h3>
                {charactersRendered}
            </section>,
        ];
    };

    render = () => {
        const { me, location, match } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        const { allUsers, user } = this.state;
        if (match.params.id) {
            if (!user) {
                return [<Loading message="Fetching user information..." key="loading" />, <Notification key="notifications" />];
            }
            const userRendered = this.renderItem(user) || [];

            return [...userRendered, <Notification key="notifications" />];
        } else if (!allUsers) {
            return [<Loading message="Fetching roster information..." key="loading" />, <Notification key="notifications" />];
        }

        const memberListRendered = this.renderList(allUsers, 'isMember');
        const soulshrivenListRendered = this.renderList(allUsers, 'isSoulshriven');

        return [
            <h2 className="form-title col-md-24" key='heading'>Roster</h2>,
            ...memberListRendered,
            ...soulshrivenListRendered,
            <Notification key="notifications" />,
        ];
    };
}

Users.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    notifications: PropTypes.array,

    dispatch: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(mapStateToProps, mapDispatchToProps)(Users);
