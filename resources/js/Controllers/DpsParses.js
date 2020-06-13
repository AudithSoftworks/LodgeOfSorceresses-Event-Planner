import { faThList, faUserPlus } from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from "prop-types";
import React, { Fragment, PureComponent } from "react";
import { connect } from "react-redux";
import { Link, Redirect } from "react-router-dom";
import deleteMyDpsParseAction from "../actions/delete-my-dps-parse";
import { infosAction } from "../actions/notifications";
import List from "../Components/DpsParses/List";
import Notification from "../Components/Notification";
import { renderActionList } from "../helpers";
import { characters } from "../vendor/data";

class DpsParses extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            character: undefined,
        };
    }

    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel("Request cancelled.");
    }

    componentDidMount = () => {
        const { history, myCharacters } = this.props;
        const { character } = this.state;
        if (myCharacters && character === undefined) {
            const characterDetected = this.getCharacter();
            if (characterDetected === null) {
                return history.push("/@me/characters");
            }
            this.setState({ character: characterDetected });
            this.renderNotificationForNoDpsParses(characterDetected);
        }
    };

    getCharacter = () => {
        const { match, myCharacters } = this.props;
        if (myCharacters) {
            const characterId = match.params.id;

            return myCharacters.find(item => item.id === parseInt(characterId)) || null;
        }

        return null;
    };

    handleDelete = event => {
        event.preventDefault();
        if (confirm("Are you sure you want to delete this parse?")) {
            const currentTarget = event.currentTarget;
            const { match, deleteMyDpsParseAction } = this.props;
            const characterId = parseInt(match.params.id);
            const parseId = parseInt(currentTarget.getAttribute("data-id"));

            return deleteMyDpsParseAction(characterId, parseId);
        }
    };

    renderNotificationForNoDpsParses = character => {
        const { dispatch, notifications } = this.props;
        const dpsParses = character.dps_parses_pending;
        if (dpsParses && !dpsParses.length && notifications.find(n => n.key === "no-dps-parses-create-one") === undefined) {
            const message = [
                <Fragment key="f-1">Create a new parse, by clicking</Fragment>,
                <FontAwesomeIcon icon={faUserPlus} key="icon" />,
                <Fragment key="f-2">icon on top right corner.</Fragment>,
            ].reduce((prev, curr) => [prev, " ", curr]);
            dispatch(
                infosAction(
                    message,
                    {
                        container: "bottom-center",
                        animationIn: ["animated", "bounceInDown"],
                        animationOut: ["animated", "bounceOutDown"],
                        dismiss: { duration: 30000 },
                    },
                    "no-dps-parses-create-one"
                )
            );
        }
    };

    render = () => {
        const { myCharacters } = this.props;
        if (!myCharacters) {
            return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
        }

        const { character } = this.state;
        if (character === undefined) {
            return null;
        }

        const actionList = {
            return: (
                <Link to={"/@me/characters"} title="Back to My Characters">
                    <FontAwesomeIcon icon={faThList} />
                </Link>
            ),
            create: (
                <Link to={"/@me/characters/" + this.props.match.params.id + "/parses/create"} title="Submit a Parse">
                    <FontAwesomeIcon icon={faUserPlus} />
                </Link>
            ),
        };
        const dpsParses = character.dps_parses_pending;

        return [
            <section className="col-md-24 p-0 mb-4" key="dpsParsesList">
                <h2 className="form-title col-md-24 pr-5">
                    Parses for <i>{character.name}</i> Pending Approval
                </h2>
                <ul className="ne-corner">{renderActionList(actionList)}</ul>
                <List dpsParses={dpsParses} onDeleteHandler={this.handleDelete} />
            </section>,
            <Notification key="notifications" />,
        ];
    };
}

DpsParses.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    myCharacters: characters,
    notifications: PropTypes.array,

    deleteMyDpsParseAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    myCharacters: state.getIn(["myCharacters"]),
    notifications: state.getIn(["notifications"]),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    deleteMyDpsParseAction: (characterId, parseId) => dispatch(deleteMyDpsParseAction(characterId, parseId)),
});

export default connect(mapStateToProps, mapDispatchToProps)(DpsParses);
