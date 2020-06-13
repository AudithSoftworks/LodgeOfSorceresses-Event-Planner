import { faUsersMedical } from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from "prop-types";
import React, { Fragment, PureComponent } from "react";
import { connect } from "react-redux";
import { Link, Redirect } from "react-router-dom";
import deleteTeamAction from "../actions/delete-team";
import { infosAction } from "../actions/notifications";
import Notification from "../Components/Notification";
import Item from "../Components/Teams/Item";
import List from "../Components/Teams/List";
import { authorizeTeamManager, deleteTeam, renderActionList } from "../helpers";
import { teams, user } from "../vendor/data";

class Teams extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            team: null,
        };
        this.handleDeleteTeam = deleteTeam.bind(this);
    }

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel("Request cancelled.");
    };

    componentDidMount = () => {
        this.renderNoTeamsCreateOneNotification();
        const { me, history, match, teams } = this.props;
        const { team } = this.state;
        if (me && match.params.id && teams && !team) {
            const selectedTeam = teams.find(item => item.id === parseInt(match.params.id));
            if (!selectedTeam) {
                history.push("/teams");
            }
            this.setState({ team: selectedTeam });
        }
    };

    renderNoTeamsCreateOneNotification = () => {
        const { dispatch, me, teams, notifications } = this.props;
        if (teams && !teams.length && notifications.find(n => n.key === "no-teams-create-one") === undefined) {
            const messages =
                me && me.isAdmin
                    ? [
                        <Fragment key="f-1">Create a new team, by clicking</Fragment>,
                        <FontAwesomeIcon icon={faUsersMedical} key="icon" />, <Fragment key="f-2">icon on top right corner.</Fragment>
                    ]
                    : [<Fragment key="f-1">No teams found.</Fragment>];
            dispatch(
                infosAction(
                    messages.reduce((acc, curr) => [acc, " ", curr]),
                    {
                        container: "bottom-center",
                        animationIn: ["animated", "bounceInDown"],
                        animationOut: ["animated", "bounceOutDown"],
                        dismiss: { duration: 30000 },
                        width: 250,
                    },
                    "no-teams-create-one"
                )
            );
        }
    };

    render = () => {
        const { location, match, me, teams } = this.props;
        if (!teams) {
            return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
        }

        const { team } = this.state;
        if (match.params.id && team) {
            const authorizedTeamManager = authorizeTeamManager({ me, team });
            return [
                <Item key="team-item"
                      authorizedTeamManager={authorizedTeamManager}
                      me={me}
                      team={team}
                      teams={teams}
                      deleteTeamHandler={authorizedTeamManager ? this.handleDeleteTeam : null} />,
                <Notification key="notifications" />,
            ];
        }

        const actionList = {
            create:
                me && me.isAdmin ? (
                    <Link to="/teams/create" className="ne-corner" title="Create a Team">
                        <FontAwesomeIcon icon={faUsersMedical} />
                    </Link>
                ) : null,
        };

        return [
            <section className="col-md-24 p-0 mb-4 table-responsive" key="teamList">
                <h2 className="form-title col-md-24" title="Teams">
                    Teams
                </h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <List deleteTeamHandler={this.handleDeleteTeam} me={me} teams={teams} />
            </section>,
            <Notification key="notifications" />,
        ];
    };
}

Teams.propTypes = {
    axiosCancelTokenSource: PropTypes.object,
    history: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    match: PropTypes.object.isRequired,
    me: user,
    notifications: PropTypes.array,
    teams,

    deleteTeamAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    me: state.getIn(["me"]),
    notifications: state.getIn(["notifications"]),
    teams: state.getIn(["teams"]),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    deleteTeamAction: teamId => dispatch(deleteTeamAction(teamId)),
});

export default connect(mapStateToProps, mapDispatchToProps)(Teams);
