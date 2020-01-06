import { faUsersMedical } from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from "prop-types";
import React, { Fragment, PureComponent } from "react";
import { connect } from "react-redux";
import { Link, Redirect } from "react-router-dom";
import deleteTeamAction from "../actions/delete-team";
import { infosAction } from "../actions/notifications";
import Notification from "../Components/Notification";
import List from "../Components/Teams/List";
import { authorizeAdmin, deleteTeam, renderActionList } from "../helpers";
import { characters, teams, user } from "../vendor/data";

class Teams extends PureComponent {
    constructor(props) {
        super(props);
        this.handleDelete = deleteTeam.bind(this);
    }

    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    }

    renderNoTeamsCreateOneNotification = authorizedAsAdmin => {
        const { dispatch, teams, notifications } = this.props;
        if (!teams.length && notifications.find(n => n.key === "no-teams-create-one") === undefined) {
            const messages = authorizedAsAdmin ? [
                <Fragment key="f-1">Create a new team, by clicking</Fragment>,
                <FontAwesomeIcon icon={faUsersMedical} key="icon" />,
                <Fragment key="f-2">icon on top right corner.</Fragment>,
            ] : [
                <Fragment key="f-1">No teams found.</Fragment>,
            ];
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
        const { me, location, teams } = this.props;
        if (!teams) {
            return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
        }

        const authorizedAsAdmin = authorizeAdmin(this.props);
        const actionList = {
            create: authorizedAsAdmin ? (
                <Link to="/teams/create" className="ne-corner" title="Create a Team">
                    <FontAwesomeIcon icon={faUsersMedical} />
                </Link>
            ) : null,
        };
        this.renderNoTeamsCreateOneNotification(authorizedAsAdmin);

        return [
            <section className="col-md-24 p-0 mb-4 table-responsive" key="teamList">
                <h2 className="form-title col-md-24" title="Teams">
                    Teams
                </h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <List teams={teams} me={me} onDeleteHandler={this.handleDelete} authorizedAsAdmin={authorizedAsAdmin} />
            </section>,
            <Notification key="notifications" />,
        ];
    };
}

Teams.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    groups: PropTypes.object,
    myCharacters: characters,
    teams,
    notifications: PropTypes.array,

    deleteTeamAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    me: state.getIn(["me"]),
    groups: state.getIn(["groups"]),
    myCharacters: state.getIn(["myCharacters"]),
    teams: state.getIn(["teams"]),
    notifications: state.getIn(["notifications"]),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    deleteTeamAction: characterId => dispatch(deleteTeamAction(characterId)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Teams);
