import(/* webpackPrefetch: true, webpackChunkName: "users-scss" */ "../../sass/_users.scss");

import { faChevronCircleLeft, faUser, faUserSlash } from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import moment from "moment";
import PropTypes from "prop-types";
import React, { Fragment, PureComponent } from "react";
import { connect } from "react-redux";
import { Link, Redirect } from "react-router-dom";
import { errorsAction, infosAction } from "../actions/notifications";
import List from "../Components/Characters/List";
import * as Attendance from "../Components/Events/Attendance";
import Loading from "../Components/Loading";
import Notification from "../Components/Notification";
import { filter, renderActionList } from "../helpers";
import { getAllUsers, getAttendances, getUser } from "../vendor/api";
import axios from "../vendor/axios";
import { user } from "../vendor/data";

class Users extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            filters: {
                members: true,
                soulshriven: true,
            },
            allUsers: null,
            user: null,
            attendances: [],
        };
        this.filter = filter.bind(this);
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel("Request cancelled.");
    };

    componentDidMount = () => {
        this.fetchData();
    };

    componentDidUpdate = (prevProps) => {
        const { match } = this.props;
        if (match.params.id && match.params.id !== prevProps.match.params.id) {
            this.fetchData();
        }
    };

    fetchData = () => {
        const { me, match } = this.props;
        if (me) {
            const { allUsers } = this.state;
            this.cancelTokenSource = axios.CancelToken.source();
            if (match.params.id) {
                getUser(this.cancelTokenSource, match.params.id)
                    .then(user => {
                        getAttendances(this.cancelTokenSource, match.params.id)
                            .then(attendances => {
                                this.cancelTokenSource = null;
                                const attendancesArray = Array.from(attendances.result, id => attendances.entities["attendance"][id]);
                                this.setState({
                                    user: user.entities.user[match.params.id],
                                    attendances: attendancesArray,
                                });
                            })
                            .catch(error => {
                                throw error;
                            });
                    })
                    .catch(error => {
                        if (!axios.isCancel(error)) {
                            const message = error.response.data.message || error.response.statusText || error.message;
                            this.props.dispatch(errorsAction(message));
                        }
                    });
            } else if (!allUsers) {
                getAllUsers(this.cancelTokenSource)
                    .then(allUsers => {
                        this.cancelTokenSource = null;
                        this.setState({ allUsers, user: null });
                    })
                    .catch(error => {
                        if (!axios.isCancel(error)) {
                            const message = error.response.data.message || error.response.statusText || error.message;
                            this.props.dispatch(errorsAction(message));
                        }
                    });
            }
        }
    };

    renderItem = user => {
        const { me, history } = this.props;
        if (!user.isMember && !user.isSoulshriven) {
            return <Redirect to={{ pathname: "/users" }} />;
        }

        const actionList = {
            return: (
                <Link to={"/users"} title="Back to Roster">
                    <FontAwesomeIcon icon={faChevronCircleLeft} />
                </Link>
            ),
        };
        const { attendances } = this.state;
        const startDate = attendances.length ? moment(attendances[0]["created_at"]) : undefined;
        const endDate = attendances.length ? moment(attendances[attendances.length - 1]["created_at"]) : undefined;

        const characterListRendered = user.characters.length
            ? [
                <h3 className="col-md-24 mt-5" key="heading">Their Characters</h3>,
                <List characters={user.characters} me={me} className="pl-2 pr-2 col-md-24" key="character-list" />
            ] : [];
        const attendancesRendered = attendances.length
            ? [
                <h3 className="col-md-24 mt-5" key="heading">Their Attendances</h3>,
                <Attendance.ListView start={startDate} end={endDate} events={attendances} key="attendances" />
            ] : [];

        return [
            <section className="col-md-24 p-0 mb-4 user-profile" key="user-profile">
                <h2 className="form-title col-md-24 pr-5" title="Welcome!">
                    {"@" + user.name}
                </h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <h3 className="col-md-24 mt-2">Account Summary</h3>
                <dl className={user.isMember ? "members" : "soulshriven"}>
                    <dt>Account Type</dt>
                    <dd>{user.isMember ? "Member" : "Soulshriven"}</dd>
                </dl>
                <dl className={user.linkedAccountsParsed.ips ? "info" : "danger"}>
                    <dt>Forum Account Linked</dt>
                    <dd>{user.linkedAccountsParsed.ips ? "Yes" : "No"}</dd>
                </dl>
                <dl className={user.characters.length > 0 ? "info" : "danger"}>
                    <dt># of characters</dt>
                    <dd>{user.characters.length}</dd>
                </dl>
                <dl className={user.clearanceLevel ? user.clearanceLevel.slug : "danger"}>
                    <dt>Overall Rank</dt>
                    <dd>{user.clearanceLevel ? user.clearanceLevel.rank.title : "None"}</dd>
                </dl>
                {[...characterListRendered]}
                {[...attendancesRendered]}
            </section>,
        ];
    };

    renderListItem = user => {
        if (!user.isMember && !user.isSoulshriven) {
            return null;
        }
        if (user.isMember && !this.state.filters.members) return null;
        if (user.isSoulshriven && !this.state.filters.soulshriven) return null;
        let rowBgColor = user.isMember ? "members" : "soulshriven";

        return (
            <li className={rowBgColor + " mb-1 mr-1"} key={"user-" + user.id} data-id={user.id}>
                <Link to={"/users/" + user.id} title="User Sheet">
                    {"@" + user.name}
                </Link>
            </li>
        );
    };

    renderList = allUsers => {
        let charactersRendered = allUsers.result.map(userId => {
            const user = allUsers.entities["user"][userId];

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
                <button type="button" onClick={event => this.filter(event, "members")} className={"ne-corner " + (filters.members || "inactive")} title="Filter Actual Members">
                    <FontAwesomeIcon icon={faUser} />
                </button>
            ),
            soulshriven: (
                <button type="button" onClick={event => this.filter(event, "soulshriven")} className={"ne-corner " + (filters.soulshriven || "inactive")} title="Filter Soulshriven">
                    <FontAwesomeIcon icon={faUserSlash} />
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
        if (allUsers && !allUsers.result.length && notifications.find(n => n.key === "no-users-found") === undefined) {
            const message = [<Fragment key="f-1">No Users Found!</Fragment>].reduce((acc, curr) => [acc, " ", curr]);
            dispatch(
                infosAction(
                    message,
                    {
                        container: "bottom-center",
                        animationIn: ["animated", "bounceInDown"],
                        animationOut: ["animated", "bounceOutDown"],
                        dismiss: { duration: 30000 },
                    },
                    "no-users-found"
                )
            );
        }
    };

    render = () => {
        const { me, location, match } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
        }

        const { allUsers, user } = this.state;
        if (!allUsers && !user) {
            return [<Loading message="Fetching Roster information..." key="loading" />, <Notification key="notifications" />];
        }

        if (match.params.id && user) {
            return [...this.renderItem(user), <Notification key="notifications" />];
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
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(["me"]),
    notifications: state.getIn(["notifications"]),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(mapStateToProps, mapDispatchToProps)(Users);
