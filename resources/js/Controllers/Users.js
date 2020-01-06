import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "users-scss" */
    "../../sass/_users.scss"
);

import { faChevronCircleLeft, faUser, faUserSlash } from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from "prop-types";
import React, { Fragment, PureComponent } from "react";
import { connect } from "react-redux";
import { Link, Redirect } from "react-router-dom";
import { errorsAction, infosAction } from "../actions/notifications";
import List from "../Components/Characters/List";
import Loading from "../Components/Loading";
import Notification from "../Components/Notification";
import { filter, renderActionList } from "../helpers";
import { getAllUsers, getUser } from "../vendor/api";
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
            user: null
        };
        this.filter = filter.bind(this);
    }

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Request cancelled.');
    };

    componentDidMount = () => {
        const { me, match } = this.props;
        if (me) {
            const { allUsers } = this.state;
            this.cancelTokenSource = axios.CancelToken.source();
            if (match.params.id) {
                getUser(this.cancelTokenSource, match.params.id)
                    .then(user => {
                        this.cancelTokenSource = null;
                        this.setState({ user: user.entities.user[match.params.id] });
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
        const { me } = this.props;
        if (!user.isMember && !user.isSoulshriven) {
            return null;
        }

        const actionList = {
            return: (
                <Link to={"/users"} title="Back to Roster">
                    <FontAwesomeIcon icon={faChevronCircleLeft} />
                </Link>
            ),
        };

        let rankFormatted = user.isSoulshriven ? 'None' : 'Initiate';
        if (user.clearanceLevel.rank) {
            rankFormatted = user.clearanceLevel.rank.title;
        }
        if (user.isSoulshriven) {
            rankFormatted += ' (Soulshriven)';
        }

        return [
            <section className="col-md-24 p-0 mb-4 d-flex flex-wrap" key="character">
                <h2 className="form-title col-md-24">{"@" + user.name}</h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <dl className="col-lg-24">
                    <dt>Rank</dt>
                    <dd>{rankFormatted}</dd>
                </dl>
                <List characters={user.characters} me={me} />
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

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Users);
